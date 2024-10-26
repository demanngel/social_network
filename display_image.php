<?php
include './db.php';

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$image_id = $_GET['id'];

$sql = "SELECT image_data FROM images WHERE id = ?";

if($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $image_id);
    $stmt->execute();
    $stmt->bind_result($image_data);
    $stmt->fetch();
    $stmt->close();
}

echo $image_data;
