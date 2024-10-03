<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="container auth">
    <h1>Login</h1>
    <?php if (isset($_GET['message']) && $_GET['message'] == 'logged_out'): ?>
        <p class="success">Вы успешно вышли из системы.</p>
    <?php endif; ?>
    <?php if (isset($_GET['error'])): ?>
        <p><?php echo htmlspecialchars($_GET['error']); ?></p>
    <?php endif; ?>
    <form action="login.php" method="POST">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>
    </form>
    <p>Don't have an account? <a href="registerForm.php">Register here</a>.</p>
</div>
</body>
</html>
