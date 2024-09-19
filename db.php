<?php
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "social_network";

    try {
        $conn = new mysqli($servername, $username, $password,  $dbname);
        
        if ($conn->connect_error) {
            throw new Exception("Ошибка подключения: " . $conn->connect_error);
        }
    } catch (mysqli_sql_exception $e) {
        header('Location: loginForm.php?error=' . urlencode($e->getMessage()));
        exit();
    }
