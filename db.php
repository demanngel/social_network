<?php
    $servername = "localhost";
    $username = "root";
    $password = "admin";
    $dbname = "social_network";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
