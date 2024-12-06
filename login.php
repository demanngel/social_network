<?php
session_start();
include './db.php';

$conn = db_connect();

try {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $username = $_POST['username'];
        $password = $_POST['password'];

        $sql = "SELECT * FROM users WHERE username = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param('s', $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();

                if (password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['id'];

                    header('Location: homepage.php');
                    exit();
                } else {
                    throw new Exception("Неверный пароль.");
                }
            } else {
                throw new Exception("Пользователь не найден.");
            }

            $stmt->close();
        } else {
            throw new Exception("Ошибка при подготовке запроса: " . $conn->error);
        }
    }
} catch (Exception $e) {
    header('Location: login.php?error=' . urlencode($e->getMessage()));
    exit();
} finally {
    db_close($conn);
}
?>

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
    <?php if (isset($_GET['message']) && $_GET['message'] == 'logged_out'): ?>
        <p class="success">Вы успешно вышли из системы.</p>
    <?php endif; ?>
    <?php if (isset($_GET['error'])): ?>
        <p><?php echo htmlspecialchars($_GET['error']); ?></p>
    <?php endif; ?>
    <form method="POST">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>
    </form>
    <p>Don't have an account? <a href="register.php">Register here</a>.</p>
</div>
</body>
</html>