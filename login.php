<?php

$message = '';
$messageType = '';
$username = '';

if (isset($_GET['registered']) && $_GET['registered'] == 1) {
    $message = "Registration successful!";
    $messageType = "success";
}

session_start();

if (isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

require_once 'apis/auth.php';

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $result = processLogin($username, $password);

    if (!$result['success']) {
        $message = $result['message'];
        $messageType = $result['messageType'];
    } else {
        $_SESSION['user'] = $result['user'];

        header('Location: index.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<?php include 'header.php'; ?>
<body>
    <div class="mini-container">
        <h3>Login to Your Account</h3>

        <?php if (!empty($message)): ?>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    <?php if ($messageType === 'success'): ?>
                        notyf.success('<?php echo addslashes($message); ?>');
                    <?php else: ?>
                        notyf.error('<?php echo addslashes($message); ?>');
                    <?php endif; ?>
                });
            </script>
        <?php endif; ?>

        <form method="post" action="">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>

            <div class="form-group">
                <button type="submit" name="login">Login</button>
            </div>
        </form>

        <div class="links">
            <a href="register.php">Don't have an account? Register</a>
        </div>
    </div>
<?php include 'footer.php'; ?>
</body>
</html>