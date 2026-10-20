<?php
// Shared top part of every page
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/functions.php';

start_session();
$page_title = isset($page_title) ? $page_title : 'BM Digital Store';
$base_url  = isset($base_url) ? $base_url : '';   // '' on the main pages, '../' inside /admin
$cart_count = cart_count();
$shop_name  = 'BM Digital Store';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($page_title); ?> | <?php echo e($shop_name); ?></title>
    <link rel="stylesheet" href="<?php echo $base_url; ?>assets/style.css">
</head>
<body>

<div class="top-bar">
    <div class="container">
        <span>Free delivery on orders over RWF 100,000</span>
        <span class="top-right">Support 24/7 &nbsp;|&nbsp; <?php echo e($shop_name); ?></span>
    </div>
</div>

<header class="site-header">
    <div class="container header-inner">
        <a class="logo" href="<?php echo $base_url; ?>index.php">
            <span class="logo-mark">BM</span>
            <span class="logo-text">Digital <b>Store</b></span>
        </a>

        <nav class="nav">
            <a href="<?php echo $base_url; ?>index.php">Products</a>
            <a href="<?php echo $base_url; ?>cart.php">Cart (<?php echo $cart_count; ?>)</a>
            <?php if (is_admin()): ?>
                <a class="nav-admin" href="<?php echo $base_url; ?>admin/index.php">Admin Panel</a>
            <?php endif; ?>
            <?php if (is_logged_in()): ?>
                <a href="<?php echo $base_url; ?>orders.php">My Orders</a>
                <span class="hello">Hi, <?php echo e($_SESSION['name']); ?></span>
                <a href="<?php echo $base_url; ?>logout.php">Logout</a>
            <?php else: ?>
                <a href="<?php echo $base_url; ?>login.php">Login</a>
                <a href="<?php echo $base_url; ?>register.php">Register</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<main class="container">
    <?php show_flash(); ?>
