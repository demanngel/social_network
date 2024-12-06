<?php
$DB_CONFIG = require 'config.php';

function db_connect() {
    global $DB_CONFIG;

    $conn = new mysqli(
        $DB_CONFIG['host'],
        $DB_CONFIG['user'],
        $DB_CONFIG['password'],
        $DB_CONFIG['dbname']
    );

    if ($conn->connect_error) {
        die('Ошибка подключения: ' . $conn->connect_error);
    }

    $conn->set_charset('utf8mb4');

    return $conn;
}

function db_close($conn) {
    if ($conn) {
        $conn->close();
    }
}
