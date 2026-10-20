<?php
// Log the user out and empty the cart
require_once __DIR__ . '/config/functions.php';

start_session();
$_SESSION = array();          // remove all session data
session_destroy();            // close the session

// Start a fresh session to show the goodbye message
start_session();
set_flash('You have been logged out.');
redirect('login.php');
