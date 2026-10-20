<?php
// Shows one finished order
$page_title = 'Order details';
require_once __DIR__ . '/includes/header.php';

$order_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// Load the order
$order = get_row("SELECT * FROM orders WHERE id = " . $order_id);

if (!$order) {
    echo '<div class="error">Order not found.</div>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

// Who is allowed to see this order?
$may_see = false;

if (is_admin()) {
    $may_see = true;                                        // admins see every order
} elseif (is_logged_in()) {
    $may_see = ($order['user_id'] == $_SESSION['user_id']);  // customers see their own
} elseif ($order['user_id'] === null) {
    // A guest order: only the session that placed it may look at it
    $may_see = isset($_SESSION['last_order_id']) && $_SESSION['last_order_id'] == $order['id'];
}

if (!$may_see) {
    echo '<div class="error">You cannot view this order.</div>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

// Load the products inside this order
$lines = get_all("SELECT oi.*, p.name AS product_name
                  FROM order_items oi
                  LEFT JOIN products p ON oi.product_id = p.id
                  WHERE oi.order_id = " . $order_id);
?>

<h1>Order #<?php echo (int) $order['id']; ?></h1>

<div class="panel">
    <p>
        <strong>Placed on:</strong> <?php echo e($order['created_at']); ?> &nbsp;|&nbsp;
        <strong>Status:</strong> <?php echo e($order['status']); ?>
    </p>
    <p>
        <strong>Name:</strong> <?php echo e($order['full_name']); ?><br>
        <strong>Address:</strong> <?php echo e($order['address']); ?><br>
        <strong>Phone:</strong> <?php echo e($order['phone']); ?>
    </p>

    <table>
        <tr>
            <th>Product</th>
            <th>Quantity</th>
            <th>Price</th>
            <th>Subtotal</th>
        </tr>
        <?php foreach ($lines as $line): ?>
            <tr>
                <td><?php echo e($line['product_name']); ?></td>
                <td><?php echo (int) $line['quantity']; ?></td>
                <td><?php echo price($line['price']); ?></td>
                <td><?php echo price($line['price'] * $line['quantity']); ?></td>
            </tr>
        <?php endforeach; ?>
        <tr class="total-row">
            <td colspan="3">Total</td>
            <td><?php echo price($order['total']); ?></td>
        </tr>
    </table>

    <p><a class="btn" href="index.php">Continue shopping</a></p>

    <?php if ($order['status'] === 'pending' && is_logged_in() && $order['user_id'] == $_SESSION['user_id']): ?>
        <form method="post" action="cancel_order.php">
            <input type="hidden" name="order_id" value="<?php echo (int) $order['id']; ?>">
            <button class="btn red" type="submit"
                    onclick="return confirm('Cancel this order?');">Cancel order</button>
        </form>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
