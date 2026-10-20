<?php
// Manage the products of the shop
require_once __DIR__ . '/auth.php';

$page_title = 'Products';
$active     = 'products';
require_once __DIR__ . '/includes/header.php';

$search      = isset($_GET['search']) ? trim($_GET['search']) : '';
$category_id = isset($_GET['category']) ? (int) $_GET['category'] : 0;

// Build the query depending on the filters
$where  = '';
if ($category_id > 0) {
    $where .= ' AND p.category_id = ' . $category_id;
}
if ($search !== '') {
    $where .= ' AND p.name LIKE "%' . mysqli_real_escape_string($conn, $search) . '%"';
}

$products = get_all("SELECT p.*, c.name AS category_name
                     FROM products p
                     LEFT JOIN categories c ON p.category_id = c.id
                     WHERE 1=1 " . $where . "
                     ORDER BY p.id ASC");

$categories = get_all("SELECT * FROM categories ORDER BY name");
?>

<div class="toolbar">
    <form method="get" action="products.php">
        <input type="text" name="search" placeholder="Search products..." value="<?php echo e($search); ?>">
        <select name="category" style="width:auto;margin-bottom:0;">
            <option value="0">All categories</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?php echo (int) $cat['id']; ?>" <?php echo $category_id === (int) $cat['id'] ? 'selected' : ''; ?>>
                    <?php echo e($cat['name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button class="btn" type="submit">Filter</button>
    </form>

    <a class="btn" href="add_product.php">+ Add product</a>
</div>

<div class="panel">
    <div class="table-wrap">
        <table>
            <tr>
                <th>Product</th>
                <th>Category</th>
                <th>Price</th>
                <th>Stock</th>
                <th>Actions</th>
            </tr>

            <?php if (count($products) == 0): ?>
                <tr><td colspan="5" class="empty">No products found.</td></tr>
            <?php endif; ?>

            <?php foreach ($products as $p): ?>
                <tr>
                    <td>
                        <div class="product-cell">
                            <img src="../<?php echo e(product_image($p['image'])); ?>" alt="">
                            <div>
                                <b><?php echo e($p['name']); ?></b>
                                <small>#<?php echo (int) $p['id']; ?></small>
                            </div>
                        </div>
                    </td>
                    <td class="muted"><?php echo e($p['category_name']); ?></td>
                    <td><b><?php echo price($p['price']); ?></b></td>
                    <td>
                        <?php if ($p['stock'] > 5): ?>
                            <span class="badge delivered"><?php echo (int) $p['stock']; ?></span>
                        <?php elseif ($p['stock'] > 0): ?>
                            <span class="badge pending"><?php echo (int) $p['stock']; ?> low</span>
                        <?php else: ?>
                            <span class="badge cancelled">out</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a class="btn sm blue" href="edit_product.php?id=<?php echo (int) $p['id']; ?>">Edit</a>
                        <a class="btn sm red" href="delete_product.php?id=<?php echo (int) $p['id']; ?>">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
