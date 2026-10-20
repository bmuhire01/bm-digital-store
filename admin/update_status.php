<?php
// Change the status of an order (pending / shipped / delivered / cancelled)
require_once __DIR__ . '/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_status'])) {
    $order_id = (int) $_POST['order_id'];
    $status   = $_POST['status'];

    // Only allow the four statuses we know
    if (in_array($status, array('pending', 'shipped', 'delivered', 'cancelled'))) {
        run_query("UPDATE orders SET status = '" . mysqli_real_escape_string($conn, $status) . "'
                   WHERE id = " . $order_id);
        set_flash('Order #' . $order_id . ' is now ' . $status . '.');
    }
}

redirect('orders.php');
