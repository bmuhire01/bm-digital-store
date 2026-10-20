<?php
// Create a new customer account
$page_title = 'Register';
require_once __DIR__ . '/includes/header.php';

$errors = array();

// Already logged in? Go to the shop.
if (is_logged_in()) {
    redirect('index.php');
}

// The form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['name']);
    $email = trim($_POST['email']);
    $pass  = $_POST['password'];

    // Check that everything is filled in
    if ($name === '')  $errors[] = 'Please enter your name.';
    if ($email === '') $errors[] = 'Please enter your email.';
    if ($pass === '')  $errors[] = 'Please enter a password.';

    // Email must look like an email and must not be used yet
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'That email address is not valid.';
    }
    if ($email !== '') {
        $exists = get_value("SELECT id FROM users WHERE email = '" . mysqli_real_escape_string($conn, $email) . "'");
        if ($exists) {
            $errors[] = 'An account with this email already exists.';
        }
    }
    if ($pass !== '' && strlen($pass) < 6) {
        $errors[] = 'Password must be at least 6 characters long.';
    }

    // Save the new user
    if (count($errors) == 0) {
        $hash = password_hash($pass, PASSWORD_DEFAULT);
        $safe_email = mysqli_real_escape_string($conn, $email);
        $safe_name  = mysqli_real_escape_string($conn, $name);

        run_query("INSERT INTO users (name, email, password, role)
                   VALUES ('$safe_name', '$safe_email', '$hash', 'customer')");

        // Log the new user straight in
        $_SESSION['user_id'] = mysqli_insert_id($conn);
        $_SESSION['name']    = $name;
        $_SESSION['role']    = 'customer';

        set_flash('Welcome to the shop, ' . $name . '!');
        redirect('index.php');
    }
}
?>

<h1>Create an account</h1>

<div class="form-box">
    <?php if (count($errors) > 0): ?>
        <div class="error">
            <?php foreach ($errors as $err): ?>
                <p><?php echo e($err); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="post" action="register.php">
        <label for="name">Name</label>
        <input type="text" id="name" name="name" value="<?php echo e(isset($_POST['name']) ? $_POST['name'] : ''); ?>">

        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="<?php echo e(isset($_POST['email']) ? $_POST['email'] : ''); ?>">

        <label for="password">Password</label>
        <input type="password" id="password" name="password">

        <p><button class="btn" type="submit">Register</button></p>
    </form>

    <p class="muted">Already have an account? <a href="login.php">Login here</a>.</p>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
