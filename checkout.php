<?php
// Checkout form + saving the order to the database
$page_title = 'Checkout';
require_once __DIR__ . '/includes/header.php';

$items = cart_items();

// Nothing to check out
if (count($items) == 0) {
    set_flash('Your cart is empty.');
    redirect('cart.php');
}

$errors = array();

// Pre-fill the form with the logged in user's name
$user = null;
if (is_logged_in()) {
    $user = get_row("SELECT * FROM users WHERE id = " . (int) $_SESSION['user_id']);
}

// The form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['full_name']);
    $address = trim($_POST['address']);
    $phone   = trim($_POST['phone']);

    if ($name === '')    $errors[] = 'Please enter your full name.';
    if ($address === '') $errors[] = 'Please enter your delivery address.';
    if ($phone === '')   $errors[] = 'Please enter your phone number.';

    // Save the order when everything is fine
    if (count($errors) == 0) {
        // Start a transaction so everything is saved together
        mysqli_begin_transaction($conn);

        $total = cart_total(cart_items());
        $user_id = is_logged_in() ? (int) $_SESSION['user_id'] : 'NULL';

        // 1. Insert the order
        $sql = "INSERT INTO orders (user_id, full_name, address, phone, total)
                VALUES ($user_id, '" . mysqli_real_escape_string($conn, $name) . "',
                        '" . mysqli_real_escape_string($conn, $address) . "',
                        '" . mysqli_real_escape_string($conn, $phone) . "',
                        " . (float) $total . ")";
        run_query($sql);
        $order_id = mysqli_insert_id($conn);

        // 2. Insert one line per product and take the items out of stock
        foreach (cart_items() as $item) {
            $product_id = (int) $item['id'];
            $qty        = (int) $item['qty'];
            $price      = (float) $item['price'];

            run_query("INSERT INTO order_items (order_id, product_id, quantity, price)
                       VALUES ($order_id, $product_id, $qty, $price)");

            run_query("UPDATE products SET stock = stock - $qty WHERE id = $product_id");
        }

        // 3. Save it all
        mysqli_commit($conn);

        // 4. Empty the cart and remember the order for this session
        $_SESSION['cart'] = array();
        $_SESSION['last_order_id'] = $order_id;

        set_flash('Thank you! Your order #' . $order_id . ' has been placed.');
        redirect('order.php?id=' . $order_id);
    }
}

$total = cart_total($items);
?>

<h1>Checkout</h1>

<?php if (count($errors) > 0): ?>
    <div class="error">
        <?php foreach ($errors as $err): ?>
            <p><?php echo e($err); ?></p>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<div class="checkout-grid">

    <div class="panel">
        <h3>Delivery details</h3>
        <form method="post" action="checkout.php">
            <label for="full_name">Full name</label>
            <input type="text" id="full_name" name="full_name"
                   value="<?php echo e(isset($_POST['full_name']) ? $_POST['full_name'] : ($user ? $user['name'] : '')); ?>">

            <label for="address">Delivery address</label>
            <textarea id="address" name="address" rows="3"><?php echo e(isset($_POST['address']) ? $_POST['address'] : ''); ?></textarea>

            <label for="phone">Phone number</label>
            <input type="text" id="phone" name="phone"
                   value="<?php echo e(isset($_POST['phone']) ? $_POST['phone'] : ''); ?>">

            <p><button class="btn" type="submit">Place order</button></p>
        </form>
    </div>

    <div class="panel">
        <h3>Order summary</h3>
        <table>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td><?php echo e($item['name']); ?> x <?php echo (int) $item['qty']; ?></td>
                    <td><?php echo price($item['line_total']); ?></td>
                </tr>
            <?php endforeach; ?>
            <tr class="total-row">
                <td>Total</td>
                <td><?php echo price($total); ?></td>
            </tr>
        </table>
    </div>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
