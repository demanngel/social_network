<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <link rel="stylesheet" href="styles/styles.css">
    <link rel="stylesheet" href="styles/light-theme.css?">
</head>
<body>
<div class="container auth">
    <h1>Register</h1>
    <?php if (isset($_GET['error'])): ?>
        <?php if ($_GET['error'] == 'user_exists_by_username'): ?>
            <p>Пользователь с таким именем уже существует.</p>
        <?php elseif ($_GET['error'] == 'user_exists_by_email'): ?>
            <p>Пользователь с таким email уже существует.</p>
        <?php elseif ($_GET['error'] == 'exception'): ?>
            <p>Произошла ошибка при регистрации.</p>
        <?php endif; ?>
    <?php endif; ?>
    <form method="POST" action="index.php?action=register"">
        <input
                type="text"
                name="username"
                placeholder="Username"
                required
                pattern="^[A-Za-z0-9_]{3,20}$"
                title="Имя пользователя должно содержать от 3 до 20 символов и может содержать только буквы, цифры и подчеркивания."
        >
        <input
                type="email"
                name="email"
                placeholder="Email"
                required
                title="Введите корректный email."
        >
        <input
                type="password"
                name="password"
                placeholder="Password"
                required
                minlength="8"
                title="Пароль должен содержать не менее 8 символов."
        >
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
    <p>Already have an account? <a href="index.php?action=login">Login here</a>.</p>
</div>
</body>
</html>