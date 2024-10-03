<?php
include './db.php';
include 'header.php';

$error_message = isset($_GET['message']) ? htmlspecialchars($_GET['message']) : 'Неизвестная ошибка.';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Ошибка</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="container error-container">
    <h1>Произошла ошибка!</h1>
    <div class="error-message">
        <p><?php echo $error_message; ?></p>
    </div>
    <a href="homepage.php">Вернуться на главную страницу</a>
</div>
</body>
</html>
