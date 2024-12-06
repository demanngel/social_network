<?php
include './db.php';

$conn = db_connect();

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

db_close($conn);