<?php
// The settings of BM Digital Store.
//
// This file is in git, so the shop behaves the same everywhere.
// The environment variables (the way Vercel and most hosting panels give
// them to us) always win over what is written here.

$config = array(
    // 'files' keeps the product pictures as files on the server,
    // 'db' puts them in the database (needed when the server has no
    // writable folder, like Vercel)
    'image_driver' => 'files',

    // 'files' keeps the sessions in the normal PHP session files,
    // 'db' keeps them in the database (needed on a serverless host where
    // every request can land on a different machine)
    'session_driver' => 'files',
);

// Read one setting.
//
// 1. The environment variable wins  (SESSION_DRIVER, IMAGE_DRIVER, ...)
// 2. Then the array above
// 3. Then the default we pass in
function app_setting($name, $default = '')
{
    $env = getenv(strtoupper($name));

    if ($env !== false) {
        return $env;
    }

    global $config;

    if (isset($config[$name]) && $config[$name] !== '') {
        return $config[$name];
    }

    return $default;
}
