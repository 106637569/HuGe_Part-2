<?php
require_once 'settings.php';

$page_title = 'Search Jobs | HuGe';
$current_page = 'jobs.php';

function h($text)
{
    return htmlspecialchars((string)$text, ENT_QUOTES, 'UTF-8');
}

function print_job_list($text)
{
    $items = preg_split('/\R+/', trim((string)$text));
    foreach ($items as $item) {
        $item = trim($item);
        if ($item !== '') {
            echo '<li>' . h($item) . '</li>';
        }
    }
}

<!-- GenAI acknowledgement: page structure, wireframe and CSS drafted with the assistance of ChatGPT (Anthropic). Content, details and final edits reviewed and adapted by the group. -->

$search = '';
if (isset($_GET['search']) && !is_array($_GET['search'])) {
    $search = trim($_GET['search']);
}

if ($search === '') {
    header('Location: jobs.php');
    exit;
}

mysqli_report(MYSQLI_REPORT_OFF);
$conn = mysqli_connect($host, $user, $pwd, $sql_db);
$result = false;
$db_error = '';

if (!$conn) {
    $db_error = 'Could not connect to the database.';
} else {
    mysqli_set_charset($conn, 'utf8mb4');

    $sql = "SELECT * FROM jobs
            WHERE job_ref LIKE ?
               OR job_title LIKE ?
               OR short_description LIKE ?
               OR reporting_line LIKE ?
               OR key_responsibilities LIKE ?
               OR essential_requirements LIKE ?
               OR preferable_requirements LIKE ?
            ORDER BY job_id ASC";

    $stmt = mysqli_prepare($conn, $sql);
    

    if (!$stmt) {
        $db_error = 'The job search could not be prepared.';
    } else {
        $term = '%' . $search . '%';
        mysqli_stmt_bind_param(
            $stmt,
            'sssssss',
            $term,
            $term,
            $term,
            $term,
            $term,
            $term,
            $term
        );
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
    }
}

include 'header.inc';
?>

<main class="container">
    <section class="page-intro">
        <h1>Search Jobs</h1>

        <form class="job-search" action="search.php" method="get">
            <label for="search">Search jobs</label>
            <input type="text"
                   id="search"
                   name="search"
                   value="<?php echo h($search); ?>"
                   placeholder="Job title, reference or keyword"
                   required>
            <button type="submit">Search</button>
        </form>

        <p><a href="jobs.php">View all open positions</a></p>
    </section>

    <section class="jobs-content">
        <h2>Search results for &ldquo;<?php echo h($search); ?>&rdquo;</h2>

<?php if ($db_error !== '') { ?>
        <p><?php echo h($db_error); ?></p>
<?php } elseif (!$result || mysqli_num_rows($result) === 0) { ?>
        <p>No jobs matched your search.</p>
<?php } else { ?>

<?php while ($job = mysqli_fetch_assoc($result)) { ?>
        <article class="job-card">
            <div class="job-heading">
                <h3><?php echo h($job['job_title']); ?></h3>
                <span class="job-ref"><?php echo h($job['job_ref']); ?></span>
            </div>

            <p><strong>Salary:</strong> <?php echo h($job['salary']); ?></p>
            <p><strong>Reporting Line:</strong> <?php echo h($job['reporting_line']); ?></p>
            <p><strong>Short Description:</strong> <?php echo h($job['short_description']); ?></p>

            <section>
                <h4>Key Responsibilities</h4>
                <ul><?php print_job_list($job['key_responsibilities']); ?></ul>
            </section>

            <section>
                <h4>Essential Requirements</h4>
                <ul><?php print_job_list($job['essential_requirements']); ?></ul>
            </section>

            <section>
                <h4>Preferable Requirements</h4>
                <ul><?php print_job_list($job['preferable_requirements']); ?></ul>
            </section>

            <a class="apply-button"
               href="apply.php?jobRef=<?php echo urlencode($job['job_ref']); ?>#jobRef">
                Apply for <?php echo h($job['job_ref']); ?>
            </a>
        </article>
<?php } ?>

<?php } ?>
    </section>
</main>

<?php
if (isset($stmt) && $stmt) {
    mysqli_stmt_close($stmt);
}
if ($result) {
    mysqli_free_result($result);
}
if ($conn) {
    mysqli_close($conn);
}
include 'footer.inc';
?>
