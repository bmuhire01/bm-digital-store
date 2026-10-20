<?php
// Sends a picture that is stored in the database.
//
// On a normal server the pictures are plain files in assets/uploads.
// On Vercel the filesystem is read only, so the uploaded pictures go into
// the database and this page gives them out again.

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/functions.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    http_response_code(404);
    exit;
}

$image = get_row("SELECT filename, mime, data FROM product_images WHERE id = " . $id);

if (!$image) {
    http_response_code(404);
    exit;
}

// Only pictures are given out
$allowed = array('image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml');
$mime = in_array($image['mime'], $allowed) ? $image['mime'] : 'application/octet-stream';

header('Content-Type: ' . $mime);
header('Content-Length: ' . strlen($image['data']));
header('Cache-Control: public, max-age=31536000');
header('Content-Disposition: inline; filename="' . preg_replace('/[^A-Za-z0-9._-]/', '', $image['filename']) . '"');

echo $image['data'];
