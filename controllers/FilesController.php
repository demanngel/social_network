<?php

namespace controllers;

require_once "./models/FilesModel.php";

use models\FilesModel;

class FilesController {
    private $filesModel;

    public function __construct($conn) {
        $this->filesModel = new FilesModel($conn);
    }

    public function displayImage($image_id) {
        echo $this->filesModel->getImageData($image_id);
    }
}
