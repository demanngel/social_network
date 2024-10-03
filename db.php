<?php
    $servername = "localhost";
    $username = "root";
    $password = "admin";
    $dbname = "social_network";

    try {
        $conn = new mysqli($servername, $username, $password,  $dbname);
        
        if ($conn->connect_error) {
            throw new Exception("Ошибка подключения: " . $conn->connect_error);
        }

        $storedProcedures = [
            'CreateUsersTable',
            'CreateGroupsTable',
            'CreateGroupMembersTable',
            /*'CreateGroupPostsTable'*/
        ];

        foreach ($storedProcedures as $procedure) {
            if ($conn->query("CALL $procedure()") === FALSE) {
                echo "Ошибка при запуске процедуры $procedure: " . $conn->error . "<br>";
            }
        }
    } catch (mysqli_sql_exception $e) {
        header('Location: loginForm.php?error=' . urlencode($e->getMessage()));
        exit();
    }
