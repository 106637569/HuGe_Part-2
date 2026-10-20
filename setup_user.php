
<?php
require_once 'settings.php';

$conn = mysqli_connect($host, $user, $pwd, $sql_db);

if (!$conn) {
    die("Database connection failed.");
}

$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
)";

mysqli_query($conn, $sql);

// Create a new hashed password
$username = "admin";
$password = password_hash("admin", PASSWORD_DEFAULT);

// Insert admin or reset its password if it already exists
$stmt = mysqli_prepare($conn,
    "INSERT INTO users (username, password)
     VALUES (?, ?)
     ON DUPLICATE KEY UPDATE password = VALUES(password)"
);

mysqli_stmt_bind_param($stmt, "ss", $username, $password);

if (mysqli_stmt_execute($stmt)) {
    echo "Admin account is ready! Username: admin, Password: admin";
} else {
    echo "Could not create admin account.";
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>
