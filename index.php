<?php
require_once './db.php';
require_once './controllers/AuthController.php';
require_once './controllers/HomePageController.php';
require_once './controllers/ProfilePageController.php';
require_once './controllers/GroupsController.php';
require_once './controllers/SuggestedPostsPageController.php';
require_once './controllers/SubscribersPageController.php';


use controllers\AuthController;
use controllers\HomePageController;
use controllers\GroupsController;
use controllers\ProfilePageController;
use controllers\SuggestedPostsPageController;
use controllers\SubscribersPageController;

$conn = db_connect();

$action = $_GET['action'] ?? 'login';

$authController = new AuthController($conn);
$homepageController = new HomepageController($conn);
$groupsController = new GroupsController($conn);
$profilepageController = new ProfilepageController($conn);
$suggestedpostspageController = new SuggestedpostspageController($conn);
$subcriberspageController = new SubscribersPageController($conn);

switch ($action) {

    case 'register':
        $authController->handleRegisterRequest();
        break;

    case 'logout':
        $authController->logout();
        break;

    case 'home':
        $homepageController->viewHomePage();
        break;

    case 'groups':
        $groupsController->viewGroupsPage();
        break;

    case 'profile':
        $profilepageController->viewProfilePage();
        break;

    case 'group':
        $groupsController->showGroup($_GET['id']);
        break;

    case 'suggested_posts':
        $suggestedpostspageController->viewSuggestedPostsPage();
        break;

    case 'subscribers':
        $subcriberspageController->viewSubscribersPage();
        break;

    default:
        $authController->handleLoginRequest();
        break;
}
