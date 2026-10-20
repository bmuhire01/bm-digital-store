<?php
// Creates the database tables and the sample data the first time the app starts.
//
// It is safe to run again: when the tables are already there it does nothing,
// so restarting the server never wipes the orders that came in.
//
// Run it by hand:  php bin/setup.php

require_once __DIR__ . '/../config/db.php';

// Are the tables already there?
function database_tables_exist()
{
    global $conn;

    $check = mysqli_query($conn, "SHOW TABLES LIKE 'products'");

    return mysqli_num_rows($check) > 0;
}

// Make the tables and fill them with the sample data
function setup_database()
{
    global $conn;

    if (database_tables_exist()) {
        return true;
    }

    $sql_file = __DIR__ . '/../database.sql';

    if (!file_exists($sql_file)) {
        return false;
    }

    $sql = file_get_contents($sql_file);

    // Take out the comments and the empty lines
    $sql = preg_replace('/^\s*--.*$/m', '', $sql);
    $sql = preg_replace('/^\s*$/m', '', $sql);

    $statements = array();
    foreach (explode(';', $sql) as $statement) {
        $statement = trim($statement);

        if ($statement === '') {
            continue;
        }

        // The file also creates the database and switches to it - on the server
        // the database already exists, so we skip those two lines.
        if (stripos($statement, 'CREATE DATABASE') === 0) {
            continue;
        }
        if (stripos($statement, 'USE ') === 0) {
            continue;
        }

        $statements[] = $statement;
    }

    foreach ($statements as $statement) {
        if (!mysqli_query($conn, $statement)) {
            return false;
        }
    }

    return true;
}

// When this file is started from the command line we run the setup right away
if (isset($_SERVER['SCRIPT_FILENAME']) && basename($_SERVER['SCRIPT_FILENAME']) === 'setup.php') {
    if (database_tables_exist()) {
        echo "The database is already set up. Nothing to do.\n";
    } else {
        echo "Setting up the database...\n";
        if (setup_database()) {
            $count = mysqli_num_rows(mysqli_query($conn, 'SELECT id FROM products'));
            echo "Done. The shop is ready with $count products.\n";
        } else {
            echo "Setup failed: " . mysqli_error($conn) . "\n";
        }
    }
}
