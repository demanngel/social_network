<?php
require_once './db.php';
require_once './controllers/AuthController.php';
require_once './controllers/HomePageController.php';
require_once './controllers/ProfilePageController.php';
require_once './controllers/GroupsController.php';
require_once './controllers/SuggestedPostsPageController.php';
require_once './controllers/SubscribersPageController.php';
require_once './controllers/PostHistoryPageController.php';
require_once './controllers/FilesController.php';
require_once './controllers/UsersPageController.php';

use controllers\AuthController;
use controllers\HomePageController;
use controllers\GroupsController;
use controllers\ProfilePageController;
use controllers\SuggestedPostsPageController;
use controllers\SubscribersPageController;
use controllers\PostHistoryPageController;
use controllers\FilesController;
use controllers\UsersPageController;

$conn = db_connect();

$action = $_GET['action'] ?? 'login';

$authController = new AuthController($conn);
$homepageController = new HomepageController($conn);
$groupsController = new GroupsController($conn);
$profilePageController = new ProfilepageController($conn);
$suggestedPostsPageController = new SuggestedpostspageController($conn);
$subcribersPageController = new SubscribersPageController($conn);
$postHistoryPageController = new PostHistoryPageController($conn);
$filesController = new FilesController($conn);
$usersPageController = new UsersPageController($conn);

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
        $profilePageController->viewProfilePage();
        break;

    case 'group':
        $groupsController->showGroup($_GET['id']);
        break;

    case 'suggested_posts':
        $suggestedPostsPageController->viewSuggestedPostsPage();
        break;

    case 'subscribers':
        $subcribersPageController->viewSubscribersPage();
        break;

    case 'post_history':
        $postHistoryPageController->viewPostHistoryPage();
        break;

    case 'display_image':
        $filesController->displayImage($_GET['id']);
        break;

    case 'users':
        $usersPageController->viewUsersPage();
        break;

    default:
        $authController->handleLoginRequest();
        break;
}
