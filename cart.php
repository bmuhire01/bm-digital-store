<?php
// Shows the cart, lets the visitor change quantities or remove items
$page_title = 'Your Cart';
require_once __DIR__ . '/includes/header.php';

// Update the quantity of one product
if (isset($_POST['update_qty'])) {
    $product_id = (int) $_POST['product_id'];
    $qty        = (int) $_POST['qty'];

    if ($qty < 1) {
        $qty = 1;
    }

    // Do not allow more than the available stock
    $stock = get_value("SELECT stock FROM products WHERE id = " . $product_id);
    if ($stock !== null && $qty > (int) $stock) {
        $qty = (int) $stock;
        set_flash('Only ' . $qty . ' item(s) left in stock.');
    }

    $_SESSION['cart'][$product_id] = $qty;
    redirect('cart.php');
}

// Remove one product from the cart
if (isset($_POST['remove_item'])) {
    $product_id = (int) $_POST['product_id'];
    unset($_SESSION['cart'][$product_id]);
    set_flash('Item removed from your cart.');
    redirect('cart.php');
}

// Empty the whole cart
if (isset($_POST['clear_cart'])) {
    $_SESSION['cart'] = array();
    set_flash('Your cart is now empty.');
    redirect('cart.php');
}

$items = cart_items();
$total = cart_total($items);
?>

<h1>Your Cart</h1>

<?php if (count($items) == 0): ?>
    <p>Your cart is empty.</p>
    <p><a class="btn" href="index.php">Continue shopping</a></p>
<?php else: ?>

    <div class="cart-box">
        <table>
            <tr>
                <th>Product</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Subtotal</th>
                <th></th>
            </tr>

            <?php foreach ($items as $item): ?>
                <tr>
                    <td><?php echo e($item['name']); ?></td>
                    <td><?php echo price($item['price']); ?></td>
                    <td>
                        <form method="post" action="cart.php">
                            <input type="hidden" name="product_id" value="<?php echo (int) $item['id']; ?>">
                            <input type="number" class="qty-input" name="qty"
                                   value="<?php echo (int) $item['qty']; ?>" min="1"
                                   max="<?php echo (int) $item['stock']; ?>">
                            <button class="btn blue" type="submit" name="update_qty">Update</button>
                        </form>
                    </td>
                    <td><?php echo price($item['line_total']); ?></td>
                    <td>
                        <form method="post" action="cart.php">
                            <input type="hidden" name="product_id" value="<?php echo (int) $item['id']; ?>">
                            <button class="btn red" type="submit" name="remove_item">Remove</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>

            <tr class="total-row">
                <td colspan="3">Total</td>
                <td><?php echo price($total); ?></td>
                <td></td>
            </tr>
        </table>

        <p>
            <a class="btn" href="checkout.php">Checkout</a>
            <a class="btn gray" href="index.php">Continue shopping</a>
        </p>

        <form method="post" action="cart.php">
            <button class="btn red" type="submit" name="clear_cart">Empty cart</button>
        </form>
    </div>

<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
