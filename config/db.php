<?php
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax'
    ]);

    session_start();
}

$host = 'localhost';
$user = 'root';
$password = '';
$database = 'scenthub_db';

$conn = mysqli_connect($host, $user, $password, $database);

if (!$conn) {
    die('Database connection failed: ' . mysqli_connect_error());
}

mysqli_set_charset($conn, 'utf8mb4');
?>