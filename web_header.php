    <header>
        <div class="logo">Task Manager</div>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <?php if ($isLoggedIn): ?>
                    <li><a href="add_task.php">Add New Task</a></li>
                    <li><a href="index.php?logout=1">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="register.php">Register</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>
