<?php
// The admin dashboard: numbers, a small chart and the latest activity
require_once __DIR__ . '/auth.php';

$page_title = 'Dashboard';
$active     = 'dashboard';
require_once __DIR__ . '/includes/header.php';

// How much money came in (cancelled orders do not count)
$revenue = get_value("SELECT COALESCE(SUM(total), 0) FROM orders WHERE status <> 'cancelled'");

// How many orders, and how many are still waiting
$order_count   = get_value("SELECT COUNT(*) FROM orders");
$pending_count = get_value("SELECT COUNT(*) FROM orders WHERE status = 'pending'");
$product_count = get_value("SELECT COUNT(*) FROM products");
$low_stock     = get_value("SELECT COUNT(*) FROM products WHERE stock <= 5");
$customer_count = get_value("SELECT COUNT(*) FROM users WHERE role = 'customer'");

// How many orders came in today
$today_orders = get_value("SELECT COUNT(*) FROM orders WHERE DATE(created_at) = CURDATE()");

// The last 7 days, for the little bar chart
$chart = array();
for ($i = 6; $i >= 0; $i--) {
    $day = date('Y-m-d', strtotime("-$i days"));
    $count = get_value("SELECT COUNT(*) FROM orders WHERE DATE(created_at) = '" . $day . "'");
    $chart[] = array('day' => date('D', strtotime($day)), 'count' => (int) $count);
}
$chart_max = 1;
foreach ($chart as $bar) {
    if ($bar['count'] > $chart_max) {
        $chart_max = $bar['count'];
    }
}

// The 5 newest orders
$recent_orders = get_all("SELECT o.*, u.name AS user_name
                          FROM orders o
                          LEFT JOIN users u ON o.user_id = u.id
                          ORDER BY o.id DESC LIMIT 5");

// The products that sold the most
$top_products = get_all("SELECT p.name, p.price, p.image, SUM(oi.quantity) AS sold
                         FROM order_items oi
                         JOIN products p ON oi.product_id = p.id
                         GROUP BY oi.product_id
                         ORDER BY sold DESC
                         LIMIT 5");

// The products that are almost sold out
$low_products = get_all("SELECT id, name, stock FROM products
                         WHERE stock <= 5 ORDER BY stock ASC LIMIT 5");
?>

<!-- The numbers -->
<div class="stats">
    <div class="stat">
        <div class="stat-icon orange">&#8599;</div>
        <div>
            <small>Total revenue</small>
            <b><?php echo price($revenue); ?></b>
        </div>
    </div>

    <div class="stat">
        <div class="stat-icon blue">&#9636;</div>
        <div>
            <small>Orders</small>
            <b><?php echo (int) $order_count; ?></b>
            <span class="trend"><?php echo (int) $today_orders; ?> today</span>
        </div>
    </div>

    <div class="stat">
        <div class="stat-icon amber">&#9888;</div>
        <div>
            <small>Pending orders</small>
            <b><?php echo (int) $pending_count; ?></b>
        </div>
    </div>

    <div class="stat">
        <div class="stat-icon green">&#9635;</div>
        <div>
            <small>Products</small>
            <b><?php echo (int) $product_count; ?></b>
            <span class="trend <?php echo $low_stock > 0 ? 'down' : ''; ?>">
                <?php echo (int) $low_stock; ?> low in stock
            </span>
        </div>
    </div>
</div>

<div class="grid-2">

    <!-- Orders of the last week -->
    <div class="panel">
        <div class="panel-head">
            <h2>Orders this week</h2>
            <a href="orders.php">See all orders</a>
        </div>
        <div class="panel-body">
            <?php if ($order_count == 0): ?>
                <div class="empty">No orders yet.</div>
            <?php else: ?>
                <div class="chart">
                    <?php foreach ($chart as $bar): ?>
                        <div class="chart-col">
                            <div class="chart-bar" style="height: <?php echo (int) round($bar['count'] / $chart_max * 100); ?>%;">
                                <span><?php echo $bar['count']; ?></span>
                            </div>
                            <label><?php echo e($bar['day']); ?></label>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Best sellers -->
    <div class="panel">
        <div class="panel-head">
            <h2>Best sellers</h2>
            <a href="products.php">All products</a>
        </div>
        <div class="panel-body">
            <?php if (count($top_products) == 0): ?>
                <div class="empty">Nothing sold yet.</div>
            <?php else: ?>
                <?php foreach ($top_products as $p): ?>
                    <div class="product-cell" style="margin-bottom:10px;">
                        <img src="../<?php echo e(product_image($p['image'])); ?>" alt="">
                        <div style="flex:1;">
                            <b><?php echo e($p['name']); ?></b>
                            <small><?php echo (int) $p['sold']; ?> sold</small>
                        </div>
                        <b><?php echo price($p['price']); ?></b>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="grid-2">

    <!-- The newest orders -->
    <div class="panel">
        <div class="panel-head">
            <h2>Latest orders</h2>
            <a href="orders.php">Manage orders</a>
        </div>
        <div class="table-wrap">
            <table>
                <tr>
                    <th>Order</th>
                    <th>Customer</th>
                    <th>Date</th>
                    <th>Total</th>
                    <th>Status</th>
                </tr>
                <?php if (count($recent_orders) == 0): ?>
                    <tr><td colspan="5" class="empty">No orders yet.</td></tr>
                <?php endif; ?>
                <?php foreach ($recent_orders as $o): ?>
                    <tr>
                        <td><a href="orders.php" style="color:var(--brand);text-decoration:none;">#<?php echo (int) $o['id']; ?></a></td>
                        <td><?php echo e($o['user_name'] ? $o['user_name'] : $o['full_name']); ?></td>
                        <td class="muted"><?php echo e(date('d M Y, H:i', strtotime($o['created_at']))); ?></td>
                        <td><b><?php echo price($o['total']); ?></b></td>
                        <td><span class="badge <?php echo e($o['status']); ?>"><?php echo e($o['status']); ?></span></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>

    <!-- Stock warnings -->
    <div class="panel">
        <div class="panel-head">
            <h2>Running low</h2>
            <a href="products.php">Manage stock</a>
        </div>
        <div class="panel-body">
            <?php if (count($low_products) == 0): ?>
                <div class="empty">Everything is well stocked.</div>
            <?php else: ?>
                <?php foreach ($low_products as $p): ?>
                    <div class="product-cell" style="margin-bottom:10px;">
                        <div style="flex:1;">
                            <b><?php echo e($p['name']); ?></b>
                            <small><?php echo (int) $p['stock']; ?> left in stock</small>
                        </div>
                        <span class="badge <?php echo $p['stock'] == 0 ? 'cancelled' : 'pending'; ?>">
                            <?php echo (int) $p['stock'] == 0 ? 'out' : 'low'; ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<p class="muted">
    <?php echo (int) $customer_count; ?> registered customers &nbsp;|&nbsp;
    <?php echo (int) $product_count; ?> products in the catalogue
</p>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
