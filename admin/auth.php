<?php
// Every admin page starts here: check the user is an admin
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/functions.php';

start_session();

// Only admins may continue
if (!is_admin()) {
    set_flash('Please login as an admin.');
    redirect('../login.php');
}
