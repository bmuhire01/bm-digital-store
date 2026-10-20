<?php
// The customer cancels an order that has not been sent yet
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/functions.php';

start_session();

if (!is_logged_in()) {
    set_flash('Please login first.');
    redirect('login.php');
}

$order_id = isset($_POST['order_id']) ? (int) $_POST['order_id'] : 0;

// Load the order, but only if it belongs to this customer
$order = get_row("SELECT * FROM orders
                  WHERE id = " . $order_id . "
                  AND user_id = " . (int) $_SESSION['user_id']);

if (!$order) {
    set_flash('Order not found.');
    redirect('orders.php');
}

// Only orders that are still pending can be cancelled
if ($order['status'] !== 'pending') {
    set_flash('This order cannot be cancelled any more.');
    redirect('orders.php');
}

// Put the items back into the stock
mysqli_begin_transaction($conn);

$lines = get_all("SELECT product_id, quantity FROM order_items WHERE order_id = " . $order_id);

foreach ($lines as $line) {
    if ($line['product_id'] !== null) {
        run_query("UPDATE products SET stock = stock + " . (int) $line['quantity'] .
                  " WHERE id = " . (int) $line['product_id']);
    }
}

run_query("UPDATE orders SET status = 'cancelled' WHERE id = " . $order_id);

mysqli_commit($conn);

set_flash('Order #' . $order_id . ' was cancelled and the items are back in stock.');
redirect('orders.php');
