<?php
/*
 * login.php - login form for the HR manager.
 *   1. Shows a username/password form (POSTs back to this same page)
 *   2. Looks the user up with a prepared statement
 *   3. Checks the password with password_verify()
 *   4. On success: new session id, mark as logged in, go to manage.php
 *   5. On failure: ONE generic message (never says which part was wrong)
 */
require_once 'settings.php';
require_once 'session.inc';

// Already logged in? Go straight to the manager page.
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header('Location: manage.php');
    exit;
}

$error    = '';
$username = '';
$max_tries    = 5;      // wrong attempts allowed...
$lock_seconds = 60;      // ...before a short lock-out

mysqli_report(MYSQLI_REPORT_OFF);
$conn = mysqli_connect($host, $user, $pwd, $sql_db);

if (!$conn) {
    $error = 'The login system is unavailable right now. Please try again later.';
} else {
    mysqli_set_charset($conn, 'utf8mb4');
    // Create the users table if it has not been created yet.
    // Add the default account only when it does not already exist; do not reset passwords.
    $create_users = "CREATE TABLE IF NOT EXISTS users (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL
    )";
    if (mysqli_query($conn, $create_users)) {
        $check = mysqli_prepare($conn, "SELECT id FROM users WHERE username = ? LIMIT 1");
        if ($check) {
            $default_username = 'admin';
            mysqli_stmt_bind_param($check, 's', $default_username);
            mysqli_stmt_execute($check);
            mysqli_stmt_store_result($check);
            $admin_exists = mysqli_stmt_num_rows($check) > 0;
            mysqli_stmt_close($check);
            if (!$admin_exists) {
                $insert = mysqli_prepare($conn, "INSERT INTO users (username, password) VALUES (?, ?)");
                if ($insert) {
                    $default_password = password_hash('admin', PASSWORD_DEFAULT);
                    mysqli_stmt_bind_param($insert, 'ss', $default_username, $default_password);
                    mysqli_stmt_execute($insert);
                    mysqli_stmt_close($insert);
                }
            }
        }
    }
}

if ($conn && $_SERVER['REQUEST_METHOD'] === 'POST') {

    $locked = isset($_SESSION['lock_until']) && time() < $_SESSION['lock_until'];

    if (!csrf_ok()) {
        $error = 'Your session expired. Please try again.';
    } elseif ($locked) {
        $error = 'Too many failed attempts. Please wait a minute and try again.';
    } elseif (!isset($_POST['username'], $_POST['password']) || is_array($_POST['username']) || is_array($_POST['password'])) {
        $error = 'Invalid username or password.';
    } else {
        $username = trim($_POST['username']);
        $password = $_POST['password'];     // do NOT trim/alter passwords

        // PREPARED STATEMENT: the ? is filled in separately, so the username can
        // never be treated as SQL (stops SQL injection on the login form).
        $stmt = mysqli_prepare($conn, "SELECT id, password FROM users WHERE username = ?");
        if (!$stmt) {
            $found = false;
            $stored_hash = '';
            $error = 'The login system is unavailable right now. Please try again later.';
        } else {
            mysqli_stmt_bind_param($stmt, 's', $username);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_bind_result($stmt, $user_id, $stored_hash);
            $found = mysqli_stmt_fetch($stmt);
            mysqli_stmt_close($stmt);
        }

        // If the username doesn't exist we still run password_verify() on a dummy hash,
        // so a wrong username and a wrong password take the same time to answer.
        if (!$found) {
            $stored_hash = '$2y$10$jZ1DwQ.saoe/OlO5xNbEleBUNVoe1l/Encz6k50to7rfLGRBlX9Ge';
        }

        if ($error === '' && $found && password_verify($password, $stored_hash)) {
            session_regenerate_id(true);    // new session id: stops "session fixation"
            $_SESSION['logged_in']     = true;
            $_SESSION['user_id']       = $user_id;
            $_SESSION['username']      = $username;
            $_SESSION['last_activity'] = time();
            $_SESSION['login_tries']   = 0;
            unset($_SESSION['lock_until']);
            header('Location: manage.php');
            exit;
        }

        // Wrong details: count it, and lock for a minute after too many.
        if ($error === '') {
        $_SESSION['login_tries'] = ($_SESSION['login_tries'] ?? 0) + 1;
        if ($_SESSION['login_tries'] >= $max_tries) {
            $_SESSION['lock_until']  = time() + $lock_seconds;
            $_SESSION['login_tries'] = 0;
        }
        $error = 'Invalid username or password.';   // generic on purpose
        }
    }
}

if ($conn) {
    mysqli_close($conn);
}
if ($error !== '') {
    http_response_code(401);
}

$page_title   = 'HuGe - Manager Login';
$current_page = 'login.php';
include 'header.inc';
?>

    <main>
        <section class="form-container login-box">
            <h1>Manager login</h1>
            <p>This area is for HuGe HR managers only.</p>

<?php if ($error !== '') { ?>
            <p class="manage-message manage-error" role="alert"><?php echo esc($error); ?></p>
<?php } ?>

            <form method="post" action="login.php" novalidate>
                <?php echo csrf_field(); ?>

                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" maxlength="50"
                           value="<?php echo esc($username); ?>" autocomplete="username" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" maxlength="100"
                           autocomplete="current-password" required>
                </div>

                <button type="submit" class="apply-button">Log in</button>
            </form>
        </section>
    </main>

<?php include 'footer.inc'; ?>
