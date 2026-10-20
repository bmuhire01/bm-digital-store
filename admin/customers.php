<?php
// The customers who have an account
require_once __DIR__ . '/auth.php';

$page_title = 'Customers';
$active     = 'customers';
require_once __DIR__ . '/includes/header.php';

$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$where = '';
if ($search !== '') {
    $where = " WHERE u.name LIKE '%" . mysqli_real_escape_string($conn, $search) . "%'
               OR u.email LIKE '%" . mysqli_real_escape_string($conn, $search) . "%'";
}

// Every customer with how much they have ordered
$customers = get_all("SELECT u.*,
                             (SELECT COUNT(*) FROM orders o WHERE o.user_id = u.id) AS order_count,
                             (SELECT COALESCE(SUM(o.total), 0) FROM orders o
                              WHERE o.user_id = u.id AND o.status <> 'cancelled') AS spent
                      FROM users u" . $where . "
                      ORDER BY spent DESC");
?>

<div class="toolbar">
    <form method="get" action="customers.php">
        <input type="text" name="search" placeholder="Search by name or email..." value="<?php echo e($search); ?>">
        <button class="btn" type="submit">Search</button>
    </form>
</div>

<div class="panel">
    <div class="table-wrap">
        <table>
            <tr>
                <th>Customer</th>
                <th>Email</th>
                <th>Role</th>
                <th>Orders</th>
                <th>Total spent</th>
                <th>Joined</th>
            </tr>

            <?php if (count($customers) == 0): ?>
                <tr><td colspan="6" class="empty">No customers found.</td></tr>
            <?php endif; ?>

            <?php foreach ($customers as $c): ?>
                <tr>
                    <td>
                        <div class="product-cell">
                            <span class="avatar" style="background:#e6efff;color:var(--blue);">
                                <?php echo e(strtoupper(substr($c['name'], 0, 1))); ?>
                            </span>
                            <div>
                                <b><?php echo e($c['name']); ?></b>
                                <small>#<?php echo (int) $c['id']; ?></small>
                            </div>
                        </div>
                    </td>
                    <td class="muted"><?php echo e($c['email']); ?></td>
                    <td><span class="badge <?php echo e($c['role']); ?>"><?php echo e($c['role']); ?></span></td>
                    <td><?php echo (int) $c['order_count']; ?></td>
                    <td><b><?php echo price($c['spent']); ?></b></td>
                    <td class="muted"><?php echo e(date('d M Y', strtotime($c['created_at']))); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
