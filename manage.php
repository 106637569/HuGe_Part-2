<?php
/*
 * manage.php - HR manager page (login required).
 * Features (all use the eoi table):
 *   1. List all EOIs
 *   2. List by job reference
 *   3. List by applicant first name, last name, or both
 *   4. Delete all EOIs for a job reference (with an "Are you sure?" step)
 *   5. Change an EOI's status (New / Current / Final)
 *   6. Choose which column the results are sorted by
 *
 * Security used here (and why):
 *   - auth.inc            : not logged in -> sent to login.php
 *   - prepared statements : user input can never become SQL
 *   - sort column         : checked against a fixed list (a ? can't be used for a column name)
 *   - status              : must be exactly New, Current or Final
 *   - delete/status       : POST forms + CSRF token (links could be triggered by other sites)
 *   - esc()               : every value printed from the database is HTML-escaped
 */
require_once 'auth.inc';        // protects this page and starts the session
require_once 'settings.php';

/* ---------------------------------------------------------------
   Fixed lists of allowed values
   --------------------------------------------------------------- */
$allowed_sorts = array(
    'EOInumber'    => 'EOI number',
    'job_ref'      => 'Job reference',
    'first_name'   => 'First name',
    'last_name'    => 'Last name',
    'dob'          => 'Date of birth',
    'state'        => 'State',
    'status'       => 'Status',
    'submitted_at' => 'Date submitted',
);
$allowed_status = array('New', 'Current', 'Final');

mysqli_report(MYSQLI_REPORT_OFF);
$conn = mysqli_connect($host, $user, $pwd, $sql_db);
if ($conn) {
    mysqli_set_charset($conn, 'utf8mb4');
}

// Message shown after an action (stored in the session so it survives the redirect).
$message = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

function flash($type, $text)
{
    $_SESSION['flash'] = array('type' => $type, 'text' => $text);
}

// Returns a valid job reference (6 letters/numbers, upper case) or '' if not valid.
function clean_job_ref($value)
{
    if (!is_string($value)) {
        return '';
    }
    $value = strtoupper(trim($value));
    return preg_match('/^[A-Z0-9]{6}$/', $value) ? $value : '';
}

$confirm_delete = '';    // set to a job ref when we need to ask "Are you sure?"
$confirm_count  = 0;

/* ===============================================================
   PART A: Actions (POST requests)
   =============================================================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = (isset($_POST['action']) && is_string($_POST['action'])) ? $_POST['action'] : '';

    if (!$conn) {
        flash('error', 'Could not connect to the database.');
    } elseif (!csrf_ok()) {
        flash('error', 'Your session expired. Please try again.');
    } elseif ($action === 'change_status') {

        $eoi_number = filter_var($_POST['eoi_number'] ?? '', FILTER_VALIDATE_INT);
        $new_status = (isset($_POST['new_status']) && is_string($_POST['new_status'])) ? $_POST['new_status'] : '';

        if ($eoi_number === false || $eoi_number < 1) {
            flash('error', 'Invalid EOI number.');
        } elseif (!in_array($new_status, $allowed_status, true)) {
            flash('error', 'Status must be New, Current or Final.');
        } else {
            $stmt = mysqli_prepare($conn, "UPDATE eoi SET status = ? WHERE EOInumber = ?");
            mysqli_stmt_bind_param($stmt, 'si', $new_status, $eoi_number);
            if (mysqli_stmt_execute($stmt)) {
                flash('success', 'EOI ' . $eoi_number . ' status set to ' . $new_status . '.');
            } else {
                flash('error', 'Could not update the status.');
            }
            mysqli_stmt_close($stmt);
        }

    } elseif ($action === 'delete_ask' || $action === 'delete_confirm') {

        $job_ref = clean_job_ref($_POST['delete_job_ref'] ?? '');

        if ($job_ref === '') {
            flash('error', 'Job reference must be exactly 6 letters or numbers.');
        } else {
            // How many records would be affected?
            $stmt = mysqli_prepare($conn, "SELECT COUNT(*) FROM eoi WHERE job_ref = ?");
            mysqli_stmt_bind_param($stmt, 's', $job_ref);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_bind_result($stmt, $count);
            mysqli_stmt_fetch($stmt);
            mysqli_stmt_close($stmt);

            if ($count == 0) {
                flash('error', 'No EOIs found for job reference ' . $job_ref . '.');
            } elseif ($action === 'delete_ask') {
                // Step 1: don't delete yet - show the "Are you sure?" box below.
                $confirm_delete = $job_ref;
                $confirm_count  = $count;
            } else {
                // Step 2: the manager pressed the confirm button.
                $stmt = mysqli_prepare($conn, "DELETE FROM eoi WHERE job_ref = ?");
                mysqli_stmt_bind_param($stmt, 's', $job_ref);
                if (mysqli_stmt_execute($stmt)) {
                    flash('success', 'Deleted ' . mysqli_stmt_affected_rows($stmt) . ' EOI(s) for job reference ' . $job_ref . '.');
                } else {
                    flash('error', 'Could not delete the records.');
                }
                mysqli_stmt_close($stmt);
            }
        }
    }

    // After an action, redirect back to the page (so refreshing doesn't repeat it).
    // The exception is the "Are you sure?" step, which needs to be shown right now.
    if ($confirm_delete === '') {
        $back = 'manage.php';
        if (isset($_POST['return_query']) && is_string($_POST['return_query'])) {
            // Keep the manager's current filters/sort. Re-encoded from parsed values, not copied raw.
            parse_str($_POST['return_query'], $old);
            $keep = array();
            foreach (array('job_ref', 'first_name', 'last_name', 'sort', 'order') as $k) {
                if (isset($old[$k]) && is_string($old[$k]) && $old[$k] !== '') {
                    $keep[$k] = $old[$k];
                }
            }
            if ($keep) {
                $back .= '?' . http_build_query($keep);
            }
        }
        header('Location: ' . $back);
        exit;
    }
}

/* ===============================================================
   PART B: Build the list (GET filters)
   =============================================================== */
$f_job   = isset($_GET['job_ref'])    && is_string($_GET['job_ref'])    ? trim($_GET['job_ref'])    : '';
$f_first = isset($_GET['first_name']) && is_string($_GET['first_name']) ? trim($_GET['first_name']) : '';
$f_last  = isset($_GET['last_name'])  && is_string($_GET['last_name'])  ? trim($_GET['last_name'])  : '';

$sort  = (isset($_GET['sort']) && is_string($_GET['sort'])) ? $_GET['sort'] : 'EOInumber';
$order = (isset($_GET['order']) && $_GET['order'] === 'desc') ? 'DESC' : 'ASC';

// SORT COLUMN: a ? blank can't hold a column name, so we only accept names from our own list.
if (!array_key_exists($sort, $allowed_sorts)) {
    $sort = 'EOInumber';
}

$filter_error = '';
$conditions   = array();
$types        = '';
$params       = array();

if ($f_job !== '') {
    $job_clean = clean_job_ref($f_job);
    if ($job_clean === '') {
        $filter_error = 'Job reference must be exactly 6 letters or numbers.';
    } else {
        $conditions[] = 'job_ref = ?';
        $types .= 's';
        $params[] = $job_clean;
    }
}

// Name search: partial match. We escape % and _ so they count as normal characters.
if ($f_first !== '') {
    $conditions[] = 'first_name LIKE ?';
    $types .= 's';
    $params[] = '%' . addcslashes($f_first, '%_\\') . '%';
}
if ($f_last !== '') {
    $conditions[] = 'last_name LIKE ?';
    $types .= 's';
    $params[] = '%' . addcslashes($f_last, '%_\\') . '%';
}

$rows = array();
$db_error = '';

if (!$conn) {
    $db_error = 'Could not connect to the database.';
} elseif ($filter_error === '') {
    // $sort and $order are safe to put in the SQL: they came from our fixed lists.
    $sql = "SELECT EOInumber, job_ref, first_name, last_name, dob, gender, gender_description,
                   street, suburb, state, postcode, email, phone, skills, other_skills,
                   status, submitted_at
            FROM eoi";
    if ($conditions) {
        $sql .= ' WHERE ' . implode(' AND ', $conditions);
    }
    $sql .= " ORDER BY $sort $order, EOInumber ASC";

    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        // Most likely the eoi table doesn't exist yet (nobody has applied).
        $db_error = 'The eoi table could not be read. Has anyone submitted an application yet?';
    } else {
        if ($types !== '') {
            mysqli_stmt_bind_param($stmt, $types, ...$params);
        }
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
        mysqli_stmt_close($stmt);
    }
}

// The current filters as a query string, used to return to the same view after an action.
$return_query = http_build_query(array_filter(array(
    'job_ref' => $f_job, 'first_name' => $f_first, 'last_name' => $f_last,
    'sort' => $sort, 'order' => strtolower($order),
), function ($v) { return $v !== ''; }));

if ($conn) {
    mysqli_close($conn);
}

/* ===============================================================
   PART C: Show the page
   =============================================================== */
$page_title   = 'HuGe - Manage EOIs';
$current_page = 'manage.php';
include 'header.inc';
?>

    <main>
        <div class="container manage-page">

            <div class="manage-top">
                <h1>Manage Expressions of Interest</h1>
                <form method="post" action="logout.php" class="inline-form">
                    <?php echo csrf_field(); ?>
                    <span>Logged in as <strong><?php echo esc($_SESSION['username'] ?? ''); ?></strong></span>
                    <button type="submit" class="manage-button manage-button-grey">Log out</button>
                </form>
            </div>

<?php if ($message) { ?>
            <p class="manage-message manage-<?php echo $message['type'] === 'success' ? 'success' : 'error'; ?>" role="status"><?php echo esc($message['text']); ?></p>
<?php } ?>
<?php if ($db_error !== '') { ?>
            <p class="manage-message manage-error" role="alert"><?php echo esc($db_error); ?></p>
<?php } ?>

<?php if ($confirm_delete !== '') { ?>
            <section class="manage-box manage-confirm" aria-labelledby="confirm-heading">
                <h2 id="confirm-heading">Are you sure?</h2>
                <p>This will permanently delete <strong><?php echo (int)$confirm_count; ?></strong> EOI(s) for job reference <strong><?php echo esc($confirm_delete); ?></strong>. This cannot be undone.</p>
                <form method="post" action="manage.php" class="inline-form">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="action" value="delete_confirm">
                    <input type="hidden" name="delete_job_ref" value="<?php echo esc($confirm_delete); ?>">
                    <input type="hidden" name="return_query" value="<?php echo esc($return_query); ?>">
                    <button type="submit" class="manage-button manage-button-red">Yes, delete them</button>
                    <a class="manage-button manage-button-grey" href="manage.php<?php echo $return_query !== '' ? '?' . esc($return_query) : ''; ?>">Cancel</a>
                </form>
            </section>
<?php } ?>

            <!-- Search / filter / sort (GET: it only reads data, so it is fine in the URL) -->
            <section class="manage-box" aria-labelledby="filter-heading">
                <h2 id="filter-heading">Find EOIs</h2>
                <form method="get" action="manage.php" novalidate>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="job_ref">Job reference</label>
                            <input type="text" id="job_ref" name="job_ref" maxlength="6" placeholder="e.g. HGF101" value="<?php echo esc($f_job); ?>">
                        </div>
                        <div class="form-group">
                            <label for="first_name">First name</label>
                            <input type="text" id="first_name" name="first_name" maxlength="20" value="<?php echo esc($f_first); ?>">
                        </div>
                        <div class="form-group">
                            <label for="last_name">Last name</label>
                            <input type="text" id="last_name" name="last_name" maxlength="20" value="<?php echo esc($f_last); ?>">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="sort">Sort by</label>
                            <select id="sort" name="sort">
<?php foreach ($allowed_sorts as $col => $label) { ?>
                                <option value="<?php echo esc($col); ?>"<?php echo $col === $sort ? ' selected' : ''; ?>><?php echo esc($label); ?></option>
<?php } ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="order">Order</label>
                            <select id="order" name="order">
                                <option value="asc"<?php echo $order === 'ASC' ? ' selected' : ''; ?>>Ascending (A-Z, oldest first)</option>
                                <option value="desc"<?php echo $order === 'DESC' ? ' selected' : ''; ?>>Descending (Z-A, newest first)</option>
                            </select>
                        </div>
                    </div>
                    <p class="manage-hint">Leave all three search boxes empty to list every EOI. Name boxes match part of a name.</p>
                    <button type="submit" class="manage-button">Show results</button>
                    <a class="manage-button manage-button-grey" href="manage.php">Show all / reset</a>
                </form>
            </section>

            <!-- Delete by job reference -->
            <section class="manage-box" aria-labelledby="delete-heading">
                <h2 id="delete-heading">Delete all EOIs for a job</h2>
                <form method="post" action="manage.php" class="inline-form">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="action" value="delete_ask">
                    <input type="hidden" name="return_query" value="<?php echo esc($return_query); ?>">
                    <label for="delete_job_ref">Job reference</label>
                    <input type="text" id="delete_job_ref" name="delete_job_ref" maxlength="6" placeholder="e.g. HGF101" class="short-input">
                    <button type="submit" class="manage-button manage-button-red">Delete...</button>
                </form>
            </section>

            <!-- Results -->
            <section aria-labelledby="results-heading">
                <h2 id="results-heading">Results (<?php echo count($rows); ?>)</h2>
<?php if ($filter_error !== '') { ?>
                <p class="manage-message manage-error" role="alert"><?php echo esc($filter_error); ?></p>
<?php } elseif (count($rows) === 0 && $db_error === '') { ?>
                <p>No EOIs match your search.</p>
<?php } elseif (count($rows) > 0) { ?>
                <div class="table-wrap">
                    <table class="data-table manage-table">
                        <caption>Expressions of interest sorted by <?php echo esc($allowed_sorts[$sort]); ?> (<?php echo $order === 'ASC' ? 'ascending' : 'descending'; ?>)</caption>
                        <thead>
                            <tr>
                                <th scope="col">EOI #</th>
                                <th scope="col">Job ref</th>
                                <th scope="col">Name</th>
                                <th scope="col">Date of birth</th>
                                <th scope="col">Gender</th>
                                <th scope="col">Address</th>
                                <th scope="col">Email</th>
                                <th scope="col">Phone</th>
                                <th scope="col">Skills</th>
                                <th scope="col">Other skills</th>
                                <th scope="col">Submitted</th>
                                <th scope="col">Status</th>
                            </tr>
                        </thead>
                        <tbody>
<?php foreach ($rows as $r) { ?>
                            <tr>
                                <td><?php echo (int)$r['EOInumber']; ?></td>
                                <td><?php echo esc($r['job_ref']); ?></td>
                                <td><?php echo esc($r['first_name'] . ' ' . $r['last_name']); ?></td>
                                <td><?php echo esc($r['dob']); ?></td>
                                <td><?php echo esc($r['gender'] === 'other' && $r['gender_description'] !== '' ? 'Other: ' . $r['gender_description'] : $r['gender']); ?></td>
                                <td><?php echo esc($r['street'] . ', ' . $r['suburb'] . ' ' . $r['state'] . ' ' . $r['postcode']); ?></td>
                                <td><?php echo esc($r['email']); ?></td>
                                <td><?php echo esc($r['phone']); ?></td>
                                <td><?php echo esc(str_replace(',', ', ', $r['skills'])); ?></td>
                                <td><?php echo esc($r['other_skills']); ?></td>
                                <td><?php echo esc($r['submitted_at']); ?></td>
                                <td>
                                    <form method="post" action="manage.php" class="status-form">
                                        <?php echo csrf_field(); ?>
                                        <input type="hidden" name="action" value="change_status">
                                        <input type="hidden" name="eoi_number" value="<?php echo (int)$r['EOInumber']; ?>">
                                        <input type="hidden" name="return_query" value="<?php echo esc($return_query); ?>">
                                        <label class="visually-hidden" for="status-<?php echo (int)$r['EOInumber']; ?>">Status for EOI <?php echo (int)$r['EOInumber']; ?></label>
                                        <select id="status-<?php echo (int)$r['EOInumber']; ?>" name="new_status">
<?php foreach ($allowed_status as $s) { ?>
                                            <option value="<?php echo esc($s); ?>"<?php echo $s === $r['status'] ? ' selected' : ''; ?>><?php echo esc($s); ?></option>
<?php } ?>
                                        </select>
                                        <button type="submit" class="manage-button manage-button-small">Save</button>
                                    </form>
                                </td>
                            </tr>
<?php } ?>
                        </tbody>
                    </table>
                </div>
<?php } ?>
            </section>

        </div>
    </main>

<?php include 'footer.inc'; ?>
