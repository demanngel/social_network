<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_SESSION['theme'])) {
        $_SESSION['theme'] = $_SESSION['theme'] === 'dark' ? 'light' : 'dark';
    } else {
        $_SESSION['theme'] = 'dark';
    }

    echo json_encode(['theme' => $_SESSION['theme']]);
}
