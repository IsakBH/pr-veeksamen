<?php
// Database configuration - update these values to match your MySQL setup
define('DB_HOST', 'localhost');
define('DB_USER', 'isak');        // Your MySQL username
define('DB_PASS', 'some_pass');            // Your MySQL password
define('DB_NAME', 'prøveeksamen');

function getDB() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        die("Database connection failed: " . $conn->connect_error);
    }
    $conn->set_charset("utf8mb4");
    return $conn;
}
$mysqli = getDB();
?>
