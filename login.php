<?php
session_start();
include './db.php';

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
                    $_SESSION['username'] = $user['username'];

                    header('Location: posts.php');
                    exit();
                } else {
                    header('Location: loginForm.php?error=invalid_password');
                    exit();
                }
            } else {
                header('Location: loginForm.php?error=user_not_found');
                exit();
            }

            $stmt->close();
        } else {
            throw new Exception("Ошибка при подготовке запроса: " . $conn->error);
        }
    }
} catch (Exception $e) {
    header('Location: loginForm.php?error=exception');
    exit();
} finally {
    $conn->close();
}
