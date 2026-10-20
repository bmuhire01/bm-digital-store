<?php
// Add a new product
require_once __DIR__ . '/auth.php';

$page_title = 'Add Product';
$active     = 'products';
require_once __DIR__ . '/includes/header.php';

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

    // Save the picture (if one was uploaded)
    $image = '';
    if (count($errors) == 0) {
        $upload = isset($_FILES['image']) ? $_FILES['image'] : null;
        $image = save_uploaded_image($upload, '');

        if ($image === false) {
            $errors[] = 'The picture could not be saved. Use a JPG, PNG, GIF, WEBP or SVG file smaller than 2 MB.';
        }
    }

    if (count($errors) == 0) {
        // If no category was chosen, store NULL
        $cat = $category_id > 0 ? $category_id : 'NULL';

        $sql = "INSERT INTO products (category_id, name, description, price, stock, image)
                VALUES ($cat,
                        '" . mysqli_real_escape_string($conn, $name) . "',
                        '" . mysqli_real_escape_string($conn, $description) . "',
                        " . (float) $price . ",
                        " . (int) $stock . ",
                        '" . mysqli_real_escape_string($conn, $image) . "')";
        run_query($sql);

        set_flash('Product "' . $name . '" was added.');
        redirect('products.php');
    }
}
?>

<div class="toolbar">
    <a class="btn gray" href="products.php">&larr; Back to products</a>
</div>

<div class="panel">
    <div class="panel-head"><h2>New product</h2></div>
    <div class="panel-body">

        <?php if (count($errors) > 0): ?>
            <div class="alert bad">
                <?php foreach ($errors as $err): ?>
                    <p style="margin:0;"><?php echo e($err); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="post" action="add_product.php" enctype="multipart/form-data">
            <div class="form-grid">
                <div>
                    <label for="name">Name</label>
                    <input type="text" id="name" name="name" value="<?php echo e(isset($_POST['name']) ? $_POST['name'] : ''); ?>">
                </div>

                <div>
                    <label for="category_id">Category</label>
                    <select id="category_id" name="category_id">
                        <option value="0">-- none --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo (int) $cat['id']; ?>"><?php echo e($cat['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label for="price">Price (RWF)</label>
                    <input type="text" id="price" name="price" value="<?php echo e(isset($_POST['price']) ? $_POST['price'] : ''); ?>">
                    <p class="hint">For example: 150000</p>
                </div>

                <div>
                    <label for="stock">Stock</label>
                    <input type="number" id="stock" name="stock" value="<?php echo e(isset($_POST['stock']) ? $_POST['stock'] : '0'); ?>">
                    <p class="hint">How many are in the warehouse</p>
                </div>
            </div>

            <label for="image">Picture</label>
            <input type="file" id="image" name="image" accept=".jpg,.jpeg,.png,.gif,.webp,.svg">
            <p class="hint">JPG, PNG, GIF, WEBP or SVG. Max 2 MB.</p>

            <label for="description">Description</label>
            <textarea id="description" name="description" rows="4"><?php echo e(isset($_POST['description']) ? $_POST['description'] : ''); ?></textarea>

            <button class="btn" type="submit">Save product</button>
            <a class="btn gray" href="products.php">Cancel</a>
        </form>

    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
