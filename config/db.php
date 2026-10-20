<?php
// Database connection (procedural mysqli)
//
// It works out where the database is in this order:
//   1. config/credentials.php  (a local file, NOT in git - best for shared hosting)
//   2. DATABASE_URL            (one URL, the way Railway and Heroku give it)
//   3. DB_HOST / DB_USER / DB_PASS / DB_NAME (environment variables)
//   4. the defaults below     (XAMPP on your own computer)

require_once __DIR__ . '/config.php';

// The defaults for XAMPP
$host = 'localhost';
$user = 'root';
$pass = '';
$name = 'shopdb';
$port = 3306;

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
if (getenv('DB_PORT')) {
    $port = (int) getenv('DB_PORT');
}

// One single URL with everything in it, like mysql://user:pass@host:port/database
$database_url = getenv('DATABASE_URL');
if (!$database_url) {
    $database_url = getenv('MYSQL_URL');
}

if ($database_url) {
    $parts = parse_url($database_url);

    if (!empty($parts['host'])) {
        $host = $parts['host'];
    }
    if (isset($parts['user'])) {
        $user = urldecode($parts['user']);
    }
    if (isset($parts['pass'])) {
        $pass = urldecode($parts['pass']);
    }
    if (isset($parts['path']) && $parts['path'] !== '/') {
        $name = ltrim($parts['path'], '/');
    }
    if (isset($parts['port'])) {
        $port = (int) $parts['port'];
    }
}

// A local file with the real credentials wins over everything
if (file_exists(__DIR__ . '/credentials.php')) {
    require __DIR__ . '/credentials.php';
}

$conn = mysqli_connect($host, $user, $pass, $name, $port);

if (!$conn) {
    die('Database connection failed: ' . mysqli_connect_error($conn));
}
