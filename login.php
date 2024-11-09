<?php
session_start();
include './db.php';

try {
    if ($conn->connect_error) {
        throw new Exception("Ошибка подключения к базе данных: " . $conn->connect_error);
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

                echo $password . " " . $user['password'];

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
    header('Location: loginForm.php?error=' . urlencode($e->getMessage()));
    exit();
} finally {
    $conn->close();
}
