<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="light-theme.css?">
</head>
<body>
<div class="container auth">
    <h1>Register</h1>
    <?php if (isset($_GET['error'])): ?>
        <?php if ($_GET['error'] == 'user_exists'): ?>
            <p>Пользователь с таким именем или email уже существует.</p>
        <?php elseif ($_GET['error'] == 'exception'): ?>
            <p>Произошла ошибка при регистрации.</p>
        <?php else: ?>
            <p><?php echo htmlspecialchars($_GET['error']); ?></p>
        <?php endif; ?>
    <?php elseif (isset($_GET['success']) && $_GET['success'] == 'registered'): ?>
        <p>Регистрация успешна! Теперь вы можете войти в систему.</p>
    <?php endif; ?>
    <form action="register.php" method="POST">
        <input type="text" name="username" placeholder="Username" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <div class="radio-group">
            <label for="role" class="form-label">Role:</label>
            <label class="radio-label">
                <input type="radio" name="role" value="user" checked> User
            </label>
            <label class="radio-label">
                <input type="radio" name="role" value="moderator"> Moderator
            </label>
        </div>
        <button type="submit">Register</button>
    </form>
    <p>Already have an account? <a href="loginForm.php">Login here</a>.</p>
</div>
</body>
</html>
