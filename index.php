<?php
session_start();

$isLoggedIn = isset($_SESSION['user']);
$userData = $isLoggedIn ? $_SESSION['user'] : null;

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<?php include 'header.php'; ?>
<body>
    <?php include 'web_header.php'; ?>
    <div class="container">
        <div class="welcome-box">
            <?php if ($isLoggedIn): ?>
                <h1>Welcome, <?php echo htmlspecialchars($userData['username']); ?></h1>
                <p>You are logged in to your Task Manager account.</p>
                <a href="add_task.php" class="btn btn-primary">Add New Task</a>
            <?php else: ?>
                <h1>Welcome to Task Manager</h1>
                <p>Manage your tasks efficiently with our simple task management system.</p>
                <a href="login.php" class="btn btn-primary">Login</a>
                <a href="register.php" class="btn btn-primary">Register</a>
            <?php endif; ?>
        </div>
        
        <?php if ($isLoggedIn): ?>
            <?php include 'database.php'; ?>
        <?php endif; ?>
    </div>
    
    <script>
        setTimeout(()=>{
            console.log("Herlo")
        },2000)
    </script>
<?php include 'footer.php'; ?>
</body>
</html>