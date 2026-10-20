<?php
// The admin panel layout: dark sidebar on the left, content on the right
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/functions.php';

start_session();

$page_title = isset($page_title) ? $page_title : 'Admin';
$active     = isset($active) ? $active : '';   // which menu item is highlighted
$admin_name = isset($_SESSION['name']) ? $_SESSION['name'] : 'Admin';
$first_letter = strtoupper(substr($admin_name, 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($page_title); ?> - Admin Panel | BM Digital Store</title>
    <link rel="stylesheet" href="../assets/admin.css">
</head>
<body>

<div class="admin-layout">

    <aside class="sidebar">
        <div class="side-brand">
            <span class="logo-mark">BM</span>
            <div>
                <strong>BM Digital Store</strong>
                <small>Admin Panel</small>
            </div>
        </div>

        <nav class="side-nav">
            <div class="side-title">Menu</div>
            <a class="<?php echo $active === 'dashboard' ? 'active' : ''; ?>" href="index.php">Dashboard</a>
            <a class="<?php echo $active === 'products' ? 'active' : ''; ?>" href="products.php">Products</a>
            <a class="<?php echo $active === 'orders' ? 'active' : ''; ?>" href="orders.php">Orders</a>
            <a class="<?php echo $active === 'customers' ? 'active' : ''; ?>" href="customers.php">Customers</a>

            <div class="side-title">Store</div>
            <a href="../index.php">View Store</a>
            <a href="../logout.php">Logout</a>
        </nav>

        <div class="side-user">
            <span class="avatar"><?php echo e($first_letter); ?></span>
            <div>
                <strong><?php echo e($admin_name); ?></strong>
                <small>Administrator</small>
            </div>
        </div>
    </aside>

    <div class="admin-main">
        <header class="admin-top">
            <h1><?php echo e($page_title); ?></h1>
            <div class="top-chip">
                <span>Signed in as</span>
                <b><?php echo e($admin_name); ?></b>
            </div>
        </header>

        <div class="admin-content">
            <?php show_flash(); ?>
