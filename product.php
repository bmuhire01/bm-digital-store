<?php
// One product with its details, plus the "add to cart" form
$page_title = 'Product';
require_once __DIR__ . '/includes/header.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// Load the product
$product = get_row("SELECT p.*, c.name AS category_name
                    FROM products p
                    LEFT JOIN categories c ON p.category_id = c.id
                    WHERE p.id = " . $id);

if (!$product) {
    echo '<div class="error">Product not found.</div>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

// Add to cart button was pressed
if (isset($_POST['add_to_cart'])) {
    $qty = isset($_POST['qty']) ? (int) $_POST['qty'] : 1;
    if ($qty < 1) $qty = 1;

    // Do not add more than the stock we have
    if ($qty > $product['stock']) {
        $qty = (int) $product['stock'];
    }

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = array();
    }

    // The cart holds product_id => quantity
    if (isset($_SESSION['cart'][$id])) {
        $_SESSION['cart'][$id] += $qty;
    } else {
        $_SESSION['cart'][$id] = $qty;
    }

    set_flash('"' . $product['name'] . '" added to your cart.');
    redirect('cart.php');
}
?>

<p class="crumbs"><a href="index.php">Products</a> &rsaquo; <?php echo e($product['name']); ?></p>

<div class="detail">
    <img src="<?php echo e(product_image($product['image'])); ?>"
         alt="<?php echo e($product['name']); ?>">

    <div>
        <span class="tag"><?php echo e($product['category_name']); ?></span>
        <h1><?php echo e($product['name']); ?></h1>
        <p><?php echo e($product['description']); ?></p>
        <p class="detail-price"><?php echo price($product['price']); ?></p>
        <p class="muted">In stock: <?php echo (int) $product['stock']; ?></p>

        <?php if ($product['stock'] > 0): ?>
            <form method="post" action="product.php?id=<?php echo (int) $product['id']; ?>">
                <label for="qty">Quantity</label>
                <input type="number" id="qty" name="qty" value="1" min="1"
                       max="<?php echo (int) $product['stock']; ?>" style="width:100px;">
                <p><button class="btn" type="submit" name="add_to_cart">Add to Cart</button></p>
            </form>
            <p class="muted">Free delivery over RWF 100,000 &nbsp;|&nbsp; 30-day returns</p>
        <?php else: ?>
            <p class="stock-out">Out of stock</p>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
