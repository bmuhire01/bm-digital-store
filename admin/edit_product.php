<?php
// Change an existing product
require_once __DIR__ . '/auth.php';

$page_title = 'Edit Product';
$active     = 'products';
require_once __DIR__ . '/includes/header.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// Load the product we want to change
$product = get_row("SELECT * FROM products WHERE id = " . $id);

if (!$product) {
    set_flash('Product not found.');
    redirect('products.php');
}

$categories = get_all("SELECT * FROM categories ORDER BY name");
$errors = array();

// The form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = trim($_POST['name']);
    $category_id = (int) $_POST['category_id'];
    $price       = (float) $_POST['price'];
    $stock       = (int) $_POST['stock'];
    $description = trim($_POST['description']);

    if ($name === '') $errors[] = 'Please enter a product name.';
    if ($price <= 0) $errors[] = 'Please enter a price bigger than zero.';
    if ($stock < 0)  $errors[] = 'Stock cannot be negative.';

    // Keep the old picture unless a new one is uploaded
    $image = $product['image'];

    if (count($errors) == 0) {
        $upload = isset($_FILES['image']) ? $_FILES['image'] : null;
        $new_image = save_uploaded_image($upload, $product['image']);

        if ($new_image === false) {
            $errors[] = 'The picture could not be saved. Use a JPG, PNG, GIF, WEBP or SVG file smaller than 2 MB.';
        } else {
            $image = $new_image;
        }
    }

    if (count($errors) == 0) {
        $cat = $category_id > 0 ? $category_id : 'NULL';

        $sql = "UPDATE products SET
                    category_id = $cat,
                    name = '" . mysqli_real_escape_string($conn, $name) . "',
                    description = '" . mysqli_real_escape_string($conn, $description) . "',
                    price = " . (float) $price . ",
                    stock = " . (int) $stock . ",
                    image = '" . mysqli_real_escape_string($conn, $image) . "'
                WHERE id = $id";
        run_query($sql);

        // The old picture is not needed any more (unless another product uses it)
        if ($image !== $product['image']) {
            delete_image_if_unused($product['image']);
        }

        set_flash('Product "' . $name . '" was updated.');
        redirect('products.php');
    }
}
?>

<div class="toolbar">
    <a class="btn gray" href="products.php">&larr; Back to products</a>
</div>

<div class="panel">
    <div class="panel-head"><h2>Edit: <?php echo e($product['name']); ?></h2></div>
    <div class="panel-body">

        <?php if (count($errors) > 0): ?>
            <div class="alert bad">
                <?php foreach ($errors as $err): ?>
                    <p style="margin:0;"><?php echo e($err); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <p style="margin-top:0;">Current picture:</p>
        <img class="preview" src="../<?php echo e(product_image($product['image'])); ?>" alt="">

        <form method="post" action="edit_product.php?id=<?php echo (int) $id; ?>" enctype="multipart/form-data">
            <div class="form-grid">
                <div>
                    <label for="name">Name</label>
                    <input type="text" id="name" name="name" value="<?php echo e($product['name']); ?>">
                </div>

                <div>
                    <label for="category_id">Category</label>
                    <select id="category_id" name="category_id">
                        <option value="0">-- none --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo (int) $cat['id']; ?>"
                                <?php echo (int) $cat['id'] === (int) $product['category_id'] ? 'selected' : ''; ?>>
                                <?php echo e($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label for="price">Price (RWF)</label>
                    <input type="text" id="price" name="price" value="<?php echo e($product['price']); ?>">
                    <p class="hint">For example: 150000</p>
                </div>

                <div>
                    <label for="stock">Stock</label>
                    <input type="number" id="stock" name="stock" value="<?php echo (int) $product['stock']; ?>">
                    <p class="hint">How many are in the warehouse</p>
                </div>
            </div>

            <label for="image">New picture (leave empty to keep the current one)</label>
            <input type="file" id="image" name="image" accept=".jpg,.jpeg,.png,.gif,.webp,.svg">
            <p class="hint">JPG, PNG, GIF, WEBP or SVG. Max 2 MB.</p>

            <label for="description">Description</label>
            <textarea id="description" name="description" rows="4"><?php echo e($product['description']); ?></textarea>

            <button class="btn" type="submit">Save changes</button>
            <a class="btn gray" href="products.php">Cancel</a>
        </form>

    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
