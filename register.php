<?php
$message = '';
$messageType = '';
$username = '';

require_once 'apis/auth.php';

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];

    $result = processRegistration($username, $password, $confirmPassword);

    $message = $result['message'];
    $messageType = $result['messageType'];

    if ($result['success']) {
        header('Location: login.php?registered=1');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<?php include 'header.php'; ?>
<body>
    <div class="mini-container">
        <h3>Create an Account</h3>

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
                <div class="password-requirements">Password must be at least 6 characters long</div>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>

            <div class="form-group">
                <button type="submit" name="register">Register</button>
            </div>
        </form>

        <div class="links">
            <a href="login.php">Already have an account? Log in</a>
        </div>
    </div>
<?php include 'footer.php'; ?>
</body>
</html>