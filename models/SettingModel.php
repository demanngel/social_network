<?php

namespace models;

class SettingModel {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getSettings() {
        $sql = "SELECT * FROM settings";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function getImageTypes() {
        $sql = "SELECT mime_type FROM allowed_image_types WHERE enabled = 1";
        $result = $this->conn->query($sql);

        $allowed_types = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $allowed_types[] = $row['mime_type'];
            }
        }

        return $allowed_types;
    }

    public function getImageMaxSize() {
        $sql = "SELECT value FROM settings WHERE name = 'max_image_size'";
        $result = $this->conn->query($sql);

        $max_file_size = 2;
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $max_file_size = (int)$row['value'];
        }

        return $max_file_size;
    }

}
