<?php
require_once 'session.inc';

// End an old manager session after 30 minutes without activity.
$idle_limit = 1800;
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    if (isset($_SESSION['last_activity']) && time() - $_SESSION['last_activity'] > $idle_limit) {
        $_SESSION = array();
        session_destroy();
        header('Location: login.php');
        exit;
    }
    $_SESSION['last_activity'] = time();
} else {
    header('Location: login.php');
    exit;
}
