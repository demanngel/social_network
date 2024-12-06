<?php

namespace controllers;

use models\UserModel;
use controllers\HeaderController;

require_once './models/UserModel.php';
require_once './controllers/HeaderController.php';

class HomePageController
{
    private $userModel;
    private $HeaderController;

    public function __construct($conn)
    {
        $this->userModel = new UserModel($conn);
        $this->HeaderController = new HeaderController($conn);
    }

    public function viewHomePage() {
        $this->HeaderController->viewHeader();
        include './views/home.php';
    }

}
