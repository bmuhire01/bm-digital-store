<?php
// Creates the database tables and the sample data the first time the app starts.
//
// It is safe to run again: when the tables are already there it does nothing,
// so restarting the server never wipes the orders that came in.
//
// Run it by hand:  php bin/setup.php

require_once __DIR__ . '/../config/db.php';

// Are the tables already there?
$check = mysqli_query($conn, "SHOW TABLES LIKE 'products'");

if (mysqli_num_rows($check) > 0) {
    echo "The database is already set up. Nothing to do.\n";
    exit(0);
}

echo "Setting up the database...\n";

// Read the SQL file
$sql_file = __DIR__ . '/../database.sql';

if (!file_exists($sql_file)) {
    die('Cannot find ' . $sql_file . "\n");
}

$sql = file_get_contents($sql_file);

// Take out the comments and the empty lines
$sql = preg_replace('/^\s*--.*$/m', '', $sql);
$sql = preg_replace('/^\s*$/m', '', $sql);

// The file also creates the database and switches to it - on the server the
// database already exists, so we skip those two lines.
$statements = array();
foreach (explode(';', $sql) as $statement) {
    $statement = trim($statement);

    if ($statement === '') {
        continue;
    }
    if (stripos($statement, 'CREATE DATABASE') === 0) {
        continue;
    }
    if (stripos($statement, 'USE ') === 0) {
        continue;
    }

    $statements[] = $statement;
}

// Run them one by one
foreach ($statements as $statement) {
    if (!mysqli_query($conn, $statement)) {
        die('Setup failed: ' . mysqli_error($conn) . "\n" . $statement . "\n");
    }
}

$count = mysqli_num_rows(mysqli_query($conn, 'SELECT id FROM products'));

echo "Done. The shop is ready with $count products.\n";
