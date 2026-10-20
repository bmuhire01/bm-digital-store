<?php
// Delete a product (with a confirmation page)
require_once __DIR__ . '/auth.php';

$page_title = 'Delete Product';
$active     = 'products';
require_once __DIR__ . '/includes/header.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$product = get_row("SELECT * FROM products WHERE id = " . $id);

if (!$product) {
    set_flash('Product not found.');
    redirect('products.php');
}

// The user pressed "Yes, delete it"
if (isset($_POST['confirm_delete'])) {
    run_query("DELETE FROM products WHERE id = " . $id);

    // Now no product points at this picture any more, so it can go
    delete_image_if_unused($product['image']);

    set_flash('Product "' . $product['name'] . '" was deleted.');
    redirect('products.php');
}
?>

<div class="toolbar">
    <a class="btn gray" href="products.php">&larr; Back to products</a>
</div>

<div class="panel">
    <div class="panel-head"><h2>Delete product</h2></div>
    <div class="panel-body">

        <div class="alert bad">
            <p style="margin:0;">Are you sure you want to delete <b><?php echo e($product['name']); ?></b>?</p>
        </div>

        <div class="product-cell" style="margin-bottom:18px;">
            <img src="../<?php echo e(product_image($product['image'])); ?>" alt="">
            <div>
                <b><?php echo e($product['name']); ?></b>
                <small><?php echo price($product['price']); ?> &nbsp;|&nbsp; <?php echo (int) $product['stock']; ?> in stock</small>
            </div>
        </div>

        <form method="post" action="delete_product.php?id=<?php echo (int) $id; ?>">
            <button class="btn red" type="submit" name="confirm_delete">Yes, delete it</button>
            <a class="btn gray" href="products.php">Cancel</a>
        </form>

    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
