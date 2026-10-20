<?php
// Log a user in
$page_title = 'Login';
require_once __DIR__ . '/includes/header.php';

$errors = array();

if (is_logged_in()) {
    redirect('index.php');
}

// The form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $pass  = $_POST['password'];

    if ($email === '' || $pass === '') {
        $errors[] = 'Please fill in both email and password.';
    } else {
        // Find the user by email
        $user = get_row("SELECT * FROM users WHERE email = '" . mysqli_real_escape_string($conn, $email) . "'");

        // Check the password with the stored hash
        if ($user && password_verify($pass, $user['password'])) {
            $_SESSION['user_id'] = (int) $user['id'];
            $_SESSION['name']    = $user['name'];
            $_SESSION['role']    = $user['role'];

            set_flash('Welcome back, ' . $user['name'] . '!');
            redirect('index.php');
        } else {
            $errors[] = 'Wrong email or password.';
        }
    }
}
?>

<h1>Login</h1>

<div class="form-box">
    <?php if (count($errors) > 0): ?>
        <div class="error">
            <?php foreach ($errors as $err): ?>
                <p><?php echo e($err); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="post" action="login.php">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="<?php echo e(isset($_POST['email']) ? $_POST['email'] : ''); ?>">

        <label for="password">Password</label>
        <input type="password" id="password" name="password">

        <p><button class="btn" type="submit">Login</button></p>
    </form>

    <p class="muted">No account yet? <a href="register.php">Register here</a>.</p>
    <p class="muted">Demo admin: admin@shop.com / admin123</p>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
