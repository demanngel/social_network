<?php

namespace controllers;

require_once "./models/UserModel.php";
require_once "./controllers/HeaderController.php";

use models\UserModel;
use controllers\HeaderController;

class UsersPageController {
    private $userModel;
    private $headerController;

    public function __construct($conn) {
        $this->userModel = new UserModel($conn);
        $this->headerController = new HeaderController($conn);
    }

    public function viewUsersPage() {
        $this->headerController->viewHeader();

        $search_term = $_GET['search'] ?? '';
        $search = '%' . $search_term . '%';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['delete_user'])) {
                $this->userModel->deleteUserById($_POST['user_id']);
            }
        }

        $users = $this->userModel->getUsers($search);

        include 'views/users.php';
    }
}
