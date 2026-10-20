<?php
// Manage the orders: look at them and change the status
require_once __DIR__ . '/auth.php';

$page_title = 'Orders';
$active     = 'orders';
require_once __DIR__ . '/includes/header.php';

$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

// Only use the filter when it is one of the statuses we know
$allowed = array('pending', 'shipped', 'delivered', 'cancelled');
if ($status_filter !== '' && !in_array($status_filter, $allowed)) {
    $status_filter = '';
}

$where = '';
if ($status_filter !== '') {
    $where = " WHERE o.status = '" . mysqli_real_escape_string($conn, $status_filter) . "'";
}

$orders = get_all("SELECT o.*, u.name AS user_name, u.email AS user_email
                   FROM orders o
                   LEFT JOIN users u ON o.user_id = u.id" . $where . "
                   ORDER BY o.id DESC");

// How many orders are in each status (for the little counters)
$counts = array();
foreach ($allowed as $s) {
    $counts[$s] = (int) get_value("SELECT COUNT(*) FROM orders WHERE status = '" . $s . "'");
}
?>

<div class="toolbar">
    <div>
        <a class="btn <?php echo $status_filter === '' ? 'orange' : 'gray'; ?>" href="orders.php">All (<?php echo (int) get_value("SELECT COUNT(*) FROM orders"); ?>)</a>
        <?php foreach ($allowed as $s): ?>
            <a class="btn <?php echo $status_filter === $s ? 'orange' : 'gray'; ?>" href="orders.php?status=<?php echo $s; ?>">
                <?php echo e($s); ?> (<?php echo $counts[$s]; ?>)
            </a>
        <?php endforeach; ?>
    </div>
</div>

<div class="panel">
    <div class="table-wrap">
        <table>
            <tr>
                <th>Order</th>
                <th>Customer</th>
                <th>Delivery</th>
                <th>Date</th>
                <th>Total</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>

            <?php if (count($orders) == 0): ?>
                <tr><td colspan="7" class="empty">No orders here yet.</td></tr>
            <?php endif; ?>

            <?php foreach ($orders as $o): ?>
                <tr>
                    <td><b>#<?php echo (int) $o['id']; ?></b></td>
                    <td>
                        <b><?php echo e($o['user_name'] ? $o['user_name'] : $o['full_name']); ?></b>
                        <?php if ($o['user_email']): ?>
                            <div class="muted"><?php echo e($o['user_email']); ?></div>
                        <?php endif; ?>
                    </td>
                    <td class="muted">
                        <?php echo e($o['address']); ?><br>
                        <?php echo e($o['phone']); ?>
                    </td>
                    <td class="muted"><?php echo e(date('d M Y, H:i', strtotime($o['created_at']))); ?></td>
                    <td><b><?php echo price($o['total']); ?></b></td>
                    <td><span class="badge <?php echo e($o['status']); ?>"><?php echo e($o['status']); ?></span></td>
                    <td>
                        <form method="post" action="update_status.php" style="display:flex;gap:6px;">
                            <input type="hidden" name="order_id" value="<?php echo (int) $o['id']; ?>">
                            <select name="status" style="width:auto;margin-bottom:0;">
                                <?php foreach ($allowed as $s): ?>
                                    <option value="<?php echo $s; ?>" <?php echo $o['status'] === $s ? 'selected' : ''; ?>>
                                        <?php echo e($s); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button class="btn sm" type="submit" name="save_status">Save</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
