<?php

namespace controllers;

require_once "./controllers/HeaderController.php";
require_once "./models/UserModel.php";
require_once "./models/SettingModel.php";
require_once "./models/FilesModel.php";

use controllers\HeaderController;
use models\SettingModel;
use models\UserModel;
use models\FilesModel;

class ProfilePageController
{
    private $headerController;
    private $userModel;
    private $settingModel;
    private $filesModel;

    public function __construct($conn) {
        $this->headerController = new HeaderController($conn);
        $this->userModel = new UserModel($conn);
        $this->settingModel = new SettingModel($conn);
        $this->filesModel = new FilesModel($conn);
    }

    public function viewProfilePage()
    {
        $notification = '';

        $this->headerController->viewHeader();

        $user_id = $_SESSION['user_id'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if(isset($_FILES['profile_image'])) {
                $file = $_FILES['profile_image'];

                $allowed_types = $this->settingModel->getImageTypes();

                $max_file_size = $this->settingModel->getImageMaxSize();

                if ($file['error'] === UPLOAD_ERR_NO_FILE) {
                    $notification = "No file was uploaded.";
                } elseif ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['size'] > ($max_file_size * 1024 * 1024)) {
                    $notification = "File size exceeds the maximum limit of " . $max_file_size . ' MB.';
                } elseif (!in_array($file['type'], $allowed_types)) {
                    $notification = "File type not allowed.";
                } elseif ($file['error'] !== UPLOAD_ERR_OK) {
                    $notification = "An error occurred while uploading the file. Error Code: " . $file['error'];
                } else {
                    $image_data = file_get_contents($_FILES['profile_image']['tmp_name']);
                    $image_id = $this->filesModel->addImage($image_data);

                    $this->userModel->updateProfileImage($user_id, $image_id);

                    $notification = "Uploaded file is not a valid image or is corrupted.";
                }
            }
        }

        $profile_info = $this->userModel->findUserById($user_id);

        include 'views/profile.php';
    }
}
