<?php

namespace models;

class FilesModel {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function addImage($image_data) {
        $sql = "INSERT INTO images (image_data) VALUES (?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('b', $null);
        $stmt->send_long_data(0, $image_data);
        $stmt->execute();
        return $stmt->insert_id;
    }

    public function getImageData($image_id) {
        $sql = "SELECT image_data FROM images WHERE id = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $image_id);
        $stmt->execute();
        $stmt->bind_result($image_data);
        $stmt->fetch();
        return $image_data;

    }

}
