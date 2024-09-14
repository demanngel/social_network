<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="container">
    <h1>Register</h1>
    <?php if (isset($_GET['error'])): ?>
        <?php if ($_GET['error'] == 'user_exists'): ?>
            <p>Пользователь с таким именем или email уже существует.</p>
        <?php elseif ($_GET['error'] == 'exception'): ?>
            <p>Произошла ошибка при регистрации.</p>
        <?php endif; ?>
    <?php elseif (isset($_GET['success']) && $_GET['success'] == 'registered'): ?>
        <p>Регистрация успешна! Теперь вы можете войти в систему.</p>
    <?php endif; ?>
    <form action="register.php" method="POST">
        <input type="text" name="username" placeholder="Username" required minlength="3" maxlength="20" pattern="[A-Za-z0-9_]+" title="Имя пользователя может содержать только буквы, цифры и подчеркивания.">
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required minlength="8" pattern=".{8,}" title="Пароль должен содержать не менее 8 символов.">
        <button type="submit">Register</button>
    </form>
    <p>Already have an account? <a href="loginForm.php">Login here</a>.</p>
</div>
</body>
</html>
