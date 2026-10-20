<?php
// The front controller for Vercel.
//
// On XAMPP every page is its own file: /product.php, /admin/products.php ...
// On Vercel every request comes to one function, so this file looks at the
// "page" parameter and loads the right page.
//
// The vercel.json routes look like this:
//   /product.php        ->  /api/index.php?page=product
//   /admin/products.php ->  /api/index.php?page=admin/products

// The only pages we let through. Anything else gets the home page.
$allowed_pages = array(
    'index',
    'product',
    'cart',
    'checkout',
    'order',
    'orders',
    'login',
    'register',
    'logout',
    'cancel_order',
    'image',
    'admin/index',
    'admin/products',
    'admin/orders',
    'admin/customers',
    'admin/add_product',
    'admin/edit_product',
    'admin/delete_product',
    'admin/update_status',
);

$page = isset($_GET['page']) ? $_GET['page'] : 'index';

// Only the pages on the list above are allowed
if (!in_array($page, $allowed_pages, true)) {
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
