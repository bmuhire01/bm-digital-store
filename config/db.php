<?php
// Database connection (procedural mysqli)
//
// It works out where the database is in this order:
//   1. config/credentials.php  (a local file, NOT in git - best for shared hosting)
//   2. environment variables   (DB_HOST, DB_USER, DB_PASS, DB_NAME)
//   3. the defaults below     (XAMPP on your own computer)

// The defaults for XAMPP
$host = 'localhost';
$user = 'root';
$pass = '';
$name = 'shopdb';

// Environment variables (many hosts let you set these in the control panel)
if (getenv('DB_HOST')) {
    $host = getenv('DB_HOST');
}
if (getenv('DB_USER')) {
    $user = getenv('DB_USER');
}
if (getenv('DB_PASS') !== false) {
    $pass = getenv('DB_PASS');
}
if (getenv('DB_NAME')) {
    $name = getenv('DB_NAME');
}

// A local file with the real credentials wins over everything
if (file_exists(__DIR__ . '/credentials.php')) {
    require __DIR__ . '/credentials.php';
}

$conn = mysqli_connect($host, $user, $pass, $name);

if (!$conn) {
    die('Database connection failed: ' . mysqli_connect_error($conn));
}
