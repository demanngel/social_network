<?php
session_start();
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
                    $_SESSION['username'] = $user['username'];

                    header('Location: posts.php');
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
    header('Location: loginForm.php?error=' . urlencode($e->getMessage()));
    exit();
} finally {
    $conn->close();
}
