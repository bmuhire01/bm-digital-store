<?php
// The front controller for Vercel.
//
// On XAMPP every page is its own file: /product.php, /admin/products.php ...
// On Vercel every request comes to one function, so this file looks at the
// "page" parameter and loads the right page.
//
// The vercel.json rewrites look like this:
//   /product.php       ->  /api/index.php?page=product
//   /admin/products.php -> /api/index.php?page=admin/products

// The static files (the css and the pictures) are given out by the web server
// itself. On Vercel the CDN does that, but when we run it locally with
// "php -S" we have to say it here.
if (PHP_SAPI === 'cli-server') {
    $static_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $static_file = __DIR__ . '/..' . $static_path;

    if (is_file($static_file) && substr($static_file, -4) !== '.php') {
        return false;
    }
}

$page = isset($_GET['page']) ? $_GET['page'] : 'index';

// Only letters, numbers, _ and / are allowed - no tricks with the path
$page = preg_replace('/[^a-zA-Z0-9_\/-]/', '', $page);

// No going up a folder
if (strpos($page, '..') !== false) {
    $page = 'index';
}

$file = __DIR__ . '/../' . $page . '.php';

if (!is_file($file)) {
    $file = __DIR__ . '/../index.php';
}

// The first time the site is opened we make the tables
require_once __DIR__ . '/../bin/setup.php';

if (!database_tables_exist()) {
    setup_database();
}

require $file;
