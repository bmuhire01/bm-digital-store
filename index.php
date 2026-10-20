<?php
// Home page: shows all products, can filter by category or search
$page_title = 'Products';
require_once __DIR__ . '/includes/header.php';

$category_id = isset($_GET['category']) ? (int) $_GET['category'] : 0;
$search      = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build the WHERE part of the query depending on the filters
$where  = '';
$params = array();

if ($category_id > 0) {
    $where .= ' AND p.category_id = ' . $category_id;
}
if ($search !== '') {
    $where .= ' AND p.name LIKE "%' . mysqli_real_escape_string($conn, $search) . '%"';
}

// Load products with the name of their category
$sql = "SELECT p.*, c.name AS category_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE 1=1 " . $where . "
        ORDER BY p.id ASC";

$products = get_all($sql);

// Load all categories for the filter links
$categories = get_all("SELECT * FROM categories ORDER BY name");

// Some numbers for the banner
$product_count = get_value("SELECT COUNT(*) FROM products");
$low_stock     = (int) get_value("SELECT COUNT(*) FROM products WHERE stock <= 5");
?>

<div class="hero">
    <h1>BM Digital Store</h1>
    <p>Technology, gadgets and accessories for everyday life. Browse the range, add what you
       like to the cart and we deliver it to your door.</p>
    <a class="btn" href="#products">Shop the products</a>

    <div class="hero-stats">
        <div><b><?php echo (int) $product_count; ?></b>Products in stock</div>
        <div><b><?php echo count($categories); ?></b>Categories</div>
        <div><b><?php echo $low_stock; ?></b>Low in stock</div>
    </div>
</div>

<div class="page-head" id="products">
    <h1>Our Products</h1>
    <form method="get" action="index.php" style="display:flex;gap:8px;">
        <input type="text" name="search" placeholder="Search products..." value="<?php echo e($search); ?>">
        <button class="btn" type="submit">Search</button>
    </form>
</div>

<p class="category-bar">
    <a class="<?php echo $category_id == 0 ? 'active' : ''; ?>" href="index.php">All</a>
    <?php foreach ($categories as $cat): ?>
        <a class="<?php echo $category_id == (int) $cat['id'] ? 'active' : ''; ?>"
           href="index.php?category=<?php echo (int) $cat['id']; ?>"><?php echo e($cat['name']); ?></a>
    <?php endforeach; ?>
</p>

<?php if (count($products) == 0): ?>
    <p>No products found.</p>
<?php else: ?>
    <div class="products">
        <?php foreach ($products as $p): ?>
            <div class="product-card">
                <a href="product.php?id=<?php echo (int) $p['id']; ?>">
                    <img src="<?php echo e(product_image($p['image'])); ?>"
                         alt="<?php echo e($p['name']); ?>">
                    <h3><?php echo e($p['name']); ?></h3>
                </a>
                <div class="cat"><?php echo e($p['category_name']); ?></div>
                <div class="price"><?php echo price($p['price']); ?></div>
                <?php if ($p['stock'] > 0): ?>
                    <div class="muted">In stock: <?php echo (int) $p['stock']; ?></div>
                <?php else: ?>
                    <div class="stock-out">Out of stock</div>
                <?php endif; ?>
                <p><a class="btn" href="product.php?id=<?php echo (int) $p['id']; ?>">View</a></p>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
