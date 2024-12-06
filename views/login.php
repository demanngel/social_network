<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="styles/styles.css">
    <link rel="stylesheet" href="styles/light-theme.css?">
</head>
<body>
<div class="container auth">
    <h1>Login</h1>
    <?php if (isset($_GET['error'])): ?>
        <p class="error">Неверное имя пользователя или пароль.</p>
    <?php endif; ?>
    <?php if (isset($_GET['success']) == 'registered'): ?>
        <p class="error">The user has been successfully registered. Log in to your account</p>
    <?php endif; ?>
    <form method="POST" action="index.php?action=login">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>
    </form>
    <p>Don't have an account? <a href="index.php?action=register">Register here</a>.</p>
</div>
</body>
</html>