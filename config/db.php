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

// In PHP 8.1 and up mysqli throws an exception when it cannot connect,
// so we catch it here and show a friendly page below.
$conn    = false;
$problem = '';

try {
    $conn = mysqli_connect($host, $user, $pass, $name, $port);

    if (!$conn) {
        $problem = mysqli_connect_error();
    }
} catch (mysqli_sql_exception $e) {
    $problem = $e->getMessage();
}

if (!$conn) {
    // Remember it in the log of the server
    error_log('BM Digital Store - database connection failed: ' . $problem);

    if (!headers_sent()) {
        header('HTTP/1.1 503 Service Unavailable');
        header('Content-Type: text/html; charset=UTF-8');
    }

    // A friendly page instead of a stack trace
    echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
    echo '<title>BM Digital Store</title>';
    echo '<style>body{font-family:Arial,Helvetica,sans-serif;background:#f4f5f7;color:#222;margin:0;';
    echo 'display:flex;align-items:center;justify-content:center;height:100vh}';
    echo '.box{background:#fff;padding:35px 40px;border-radius:10px;border:1px solid #e5e7eb;max-width:520px}';
    echo 'h1{margin:0 0 12px;font-size:22px}span{color:#f97316}p{line-height:1.6;color:#555}';
    echo 'code{background:#f4f5f7;padding:2px 6px;border-radius:4px;font-size:13px}</style></head><body>';
    echo '<div class="box"><h1>BM <span>Digital Store</span></h1>';
    echo '<p>The shop cannot reach its database right now. It is probably not set up yet, ';
    echo 'or the database is asleep for a moment.</p>';
    echo '<p>Please try again in a moment.</p>';
    echo '<p style="font-size:12px;color:#888">The server said:<br><code>' . htmlspecialchars($problem) . '</code></p>';
    echo '</div></body></html>';

    exit;
}
