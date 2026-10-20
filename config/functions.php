<?php
// Small helper functions used across the shop

// The database connection is needed by these functions
require_once __DIR__ . '/db.php';

// Escape text before putting it into a SQL query (prevents SQL injection)
function e($text)
{
    global $conn;
    return htmlspecialchars(mysqli_real_escape_string($conn, $text));
}

// Run a SQL query and stop with a readable error if it fails
function run_query($sql)
{
    global $conn;
    $result = mysqli_query($conn, $sql);
    if (!$result) {
        die('SQL error: ' . mysqli_error($conn) . ' | Query: ' . $sql);
    }
    return $result;
}

// Fetch all rows as an array
function get_all($sql)
{
    $rows = array();
    $result = run_query($sql);
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    return $rows;
}

// Fetch only the first row
function get_row($sql)
{
    $result = run_query($sql);
    return mysqli_fetch_assoc($result);
}

// Get one single value from the first row
function get_value($sql)
{
    $result = run_query($sql);
    $row = mysqli_fetch_row($result);
    return $row ? $row[0] : null;
}

// Format a price in Rwandan francs, e.g. 1250000 -> RWF 1,250,000
function price($amount)
{
    $amount = (float) $amount;

    // Show the cents only when there really are cents
    if (floor($amount) == $amount) {
        return 'RWF ' . number_format($amount, 0);
    }

    return 'RWF ' . number_format($amount, 2);
}

// Start a session if it is not running yet
function start_session()
{
    if (session_status() === PHP_SESSION_NONE) {
        // On a serverless host the sessions have to live in the database
        if (sessions_in_database()) {
            require_once __DIR__ . '/session_db.php';
            use_database_sessions();
        }

        session_start();
    }
}

// Do the sessions have to be kept in the database?
// Yes on Vercel and other serverless hosts, no on a normal server.
function sessions_in_database()
{
    if (app_setting('session_driver') === 'db') {
        return true;
    }

    // Vercel tells us it is running us
    if (getenv('VERCEL')) {
        return true;
    }

    return false;
}

// Is the current visitor logged in?
function is_logged_in()
{
    start_session();
    return isset($_SESSION['user_id']);
}

// Is the current visitor an admin?
function is_admin()
{
    start_session();
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Send the visitor to another page and stop the script
function redirect($page)
{
    header('Location: ' . $page);
    exit;
}

// Show a simple message on the next page
function set_flash($message)
{
    start_session();
    $_SESSION['flash'] = $message;
}

// Print the flash message once and forget it
function show_flash()
{
    start_session();
    if (isset($_SESSION['flash'])) {
        echo '<div class="flash">' . e($_SESSION['flash']) . '</div>';
        unset($_SESSION['flash']);
    }
}

// Build the cart lines with the product details from the database
function cart_items()
{
    $items = array();

    if (empty($_SESSION['cart'])) {
        return $items;
    }

    foreach ($_SESSION['cart'] as $product_id => $qty) {
        $p = get_row("SELECT * FROM products WHERE id = " . (int) $product_id);
        if (!$p) {
            continue; // the product no longer exists
        }
        $p['qty']        = (int) $qty;
        $p['line_total'] = $p['price'] * $p['qty'];
        $items[] = $p;
    }

    return $items;
}

// Sum of all cart lines
function cart_total($items)
{
    $total = 0;
    foreach ($items as $item) {
        $total += $item['line_total'];
    }
    return $total;
}

// How many items are in the cart (for the menu)
function cart_count()
{
    start_session();
    return isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;
}

// Where the product pictures are stored
define('IMAGE_FOLDER', __DIR__ . '/../assets/uploads/');

// Have the pictures to be kept in the database?
// Yes on Vercel and other serverless hosts (the filesystem there is read only),
// no on a normal server where a plain file works fine.
function images_in_database()
{
    if (app_setting('image_driver') === 'db') {
        return true;
    }

    if (getenv('VERCEL')) {
        return true;
    }

    return false;
}

// The picture of a product, with a fallback when the file is missing
function product_image($filename)
{
    $filename = (string) $filename;

    // A picture that lives in the database
    if (strpos($filename, 'db:') === 0) {
        return 'image.php?id=' . (int) substr($filename, 3);
    }

    if ($filename !== '' && file_exists(IMAGE_FOLDER . $filename)) {
        return 'assets/uploads/' . $filename;
    }

    return 'assets/uploads/placeholder.svg';
}

// Save an uploaded picture and return its new file name (or null on error)
function save_uploaded_image($file, $old_name = '')
{
    // Nothing was uploaded?
    if (!isset($file) || !is_array($file) || !isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return $old_name;
    }

    // Something went wrong while uploading
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }

    // Too big? (2 MB)
    if ($file['size'] > 2 * 1024 * 1024) {
        return false;
    }

    // Only these picture types are allowed
    $allowed = array('jpg', 'jpeg', 'png', 'gif', 'webp', 'svg');
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($ext, $allowed)) {
        return false;
    }

    // Build a name that nobody else is using
    $new_name = 'product_' . time() . '_' . rand(1000, 9999) . '.' . $ext;

    // On a serverless host the picture has to go into the database
    if (images_in_database()) {
        return save_image_in_database($file, $new_name);
    }

    // Move the file from the temporary folder into assets/uploads
    if (!move_uploaded_file($file['tmp_name'], IMAGE_FOLDER . $new_name)) {
        return false;
    }

    return $new_name;
}

// Put an uploaded picture into the database and return "db:<id>"
function save_image_in_database($file, $new_name)
{
    global $conn;

    $bytes = file_get_contents($file['tmp_name']);

    if ($bytes === false) {
        return false;
    }

    $mime = mime_content_type($file['tmp_name']);
    if (!$mime) {
        $mime = 'application/octet-stream';
    }

    $safe_name  = mysqli_real_escape_string($conn, $new_name);
    $safe_mime  = mysqli_real_escape_string($conn, $mime);
    $safe_bytes = mysqli_real_escape_string($conn, $bytes);

    mysqli_query($conn, "INSERT INTO product_images (filename, mime, data, size)
                          VALUES ('$safe_name', '$safe_mime', '$safe_bytes', " . (int) $file['size'] . ")");

    if (mysqli_error($conn)) {
        return false;
    }

    return 'db:' . mysqli_insert_id($conn);
}

// Delete a picture file (but never the placeholder)
function delete_image_file($filename)
{
    global $conn;

    if ($filename === '' || $filename === 'placeholder.svg') {
        return;
    }

    // A picture in the database
    if (strpos($filename, 'db:') === 0) {
        $id = (int) substr($filename, 3);
        mysqli_query($conn, "DELETE FROM product_images WHERE id = " . $id);
        return;
    }

    $path = IMAGE_FOLDER . $filename;

    if (file_exists($path)) {
        unlink($path);
    }
}

// Delete a picture only when no other product still uses it
// (only uploaded pictures are removed, the ready-made ones stay)
function delete_image_if_unused($filename)
{
    global $conn;

    if ($filename === '') {
        return;
    }

    // A picture in the database is only used by one product
    if (strpos($filename, 'db:') === 0) {
        delete_image_file($filename);
        return;
    }

    if (strpos($filename, 'product_') !== 0) {
        return;
    }

    $still_used = get_value("SELECT id FROM products WHERE image = '" . mysqli_real_escape_string($conn, $filename) . "' LIMIT 1");

    if (!$still_used) {
        delete_image_file($filename);
    }
}



