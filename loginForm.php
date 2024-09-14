<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="container">
    <h1>Login</h1>
    <?php if (isset($_GET['message']) && $_GET['message'] == 'logged_out'): ?>
        <p class="success">Вы успешно вышли из системы.</p>
    <?php endif; ?>
    <?php if (isset($_GET['error'])): ?>
        <?php if ($_GET['error'] == 'invalid_password'): ?>
            <p>Неправильный пароль. Пожалуйста, попробуйте еще раз.</p>
        <?php elseif ($_GET['error'] == 'user_not_found'): ?>
            <p>Пользователь не найден. Пожалуйста, зарегистрируйтесь.</p>
        <?php elseif ($_GET['error'] == 'exception'): ?>
            <p>Произошла ошибка при входе в систему. Пожалуйста, попробуйте позже.</p>
        <?php endif; ?>
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
