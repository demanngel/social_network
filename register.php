<?php
include './db.php';

$conn = db_connect();

try {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $role =$_POST['role'];


        if (empty($username) || !preg_match('/^[A-Za-z0-9_]{3,20}$/', $username)) {
            $errors[] = "Имя пользователя должно содержать от 3 до 20 символов и может содержать только буквы, цифры и подчеркивания.";
        }

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Введите корректный email.";
        }

        if (empty($_POST['password']) || strlen($_POST['password']) < 8) {
            $errors[] = "Пароль должен содержать не менее 8 символов.";
        }

        if (!empty($errors)) {
            header('Location: register.php?error=' . urlencode(implode(', ', $errors)));
            exit();
        }

        $check_sql = "SELECT * FROM users WHERE email = ? OR username = ?";
        if ($stmt = $conn->prepare($check_sql)) {
            $stmt->bind_param('ss', $email, $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                header('Location: register.php?error=user_exists');
                exit();
            } else {
                $sql = "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)";
                if ($stmt = $conn->prepare($sql)) {
                    $stmt->bind_param('ssss', $username, $email, $password, $role);

                    if ($stmt->execute()) {
                        header('Location: loginForm.php?success=registered');
                        exit();
                    } else {
                        throw new Exception("Ошибка при выполнении запроса: " . $stmt->error);
                    }
                } else {
                    throw new Exception("Ошибка при подготовке запроса: " . $conn->error);
                }
            }
        } else {
            throw new Exception("Ошибка при подготовке запроса: " . $conn->error);
        }
    }
} catch (Exception $e) {
    header('Location: register.php?error=exception');
    exit();
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    db_close($conn);
}

?>

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
    <form method="POST">
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
    <p>Already have an account? <a href="login.php">Login here</a>.</p>
</div>
</body>
</html>
