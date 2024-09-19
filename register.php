<?php
include './db.php';

try {
    if ($conn->connect_error) {
        throw new Exception("Ошибка подключения к базе данных: " . $conn->connect_error);
    }

    $create_table_sql = "
        CREATE TABLE IF NOT EXISTS users (
            id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            email VARCHAR(100) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";

    if (!$conn->query($create_table_sql)) {
        throw new Exception("Ошибка при создании таблицы: " . $conn->error);
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

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
            header('Location: registerForm.php?error=' . urlencode(implode(', ', $errors)));
            exit();
        }

        $check_sql = "SELECT * FROM users WHERE email = ? OR username = ?";
        if ($stmt = $conn->prepare($check_sql)) {
            $stmt->bind_param('ss', $email, $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                header('Location: registerForm.php?error=user_exists');
                exit();
            } else {
                $sql = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
                if ($stmt = $conn->prepare($sql)) {
                    $stmt->bind_param('sss', $username, $email, $password);

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
    header('Location: registerForm.php?error=exception');
    exit();
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    $conn->close();
}
