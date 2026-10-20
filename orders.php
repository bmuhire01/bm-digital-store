<?php
// The order history of the logged in customer
$page_title = 'My Orders';
require_once __DIR__ . '/includes/header.php';

if (!is_logged_in()) {
    set_flash('Please login to see your orders.');
    redirect('login.php');
}

$orders = get_all("SELECT * FROM orders WHERE user_id = " . (int) $_SESSION['user_id'] . " ORDER BY id DESC");
?>

<h1>My Orders</h1>

<?php if (count($orders) == 0): ?>
    <p>You have not placed any orders yet.</p>
    <p><a class="btn" href="index.php">Start shopping</a></p>
<?php else: ?>
    <table>
        <tr>
            <th>Order #</th>
            <th>Date</th>
            <th>Total</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($orders as $order): ?>
            <tr>
                <td>#<?php echo (int) $order['id']; ?></td>
                <td><?php echo e($order['created_at']); ?></td>
                <td><?php echo price($order['total']); ?></td>
                <td><?php echo e($order['status']); ?></td>
                <td>
                    <a class="btn blue" href="order.php?id=<?php echo (int) $order['id']; ?>">View</a>

                    <?php if ($order['status'] === 'pending'): ?>
                        <form method="post" action="cancel_order.php" style="display:inline;">
                            <input type="hidden" name="order_id" value="<?php echo (int) $order['id']; ?>">
                            <button class="btn red" type="submit"
                                    onclick="return confirm('Cancel order #<?php echo (int) $order['id']; ?>?');">Cancel</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
