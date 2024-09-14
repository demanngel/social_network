<?php
include './db.php';

try {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

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
