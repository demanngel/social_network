<?php

namespace controllers;

use models\UserModel;

require_once './models/UserModel.php';

class HeaderController
{
    private $userModel;
    private $theme;

    public function __construct($conn)
    {
        $this->changeTheme();
        $this->userModel = new UserModel($conn);
    }

    public function viewHeader()
    {
        require_once './db.php';
        require_once './models/UserModel.php';

        session_start();

        $userRole = $this->getUserRole();
        $navLinks = $this->getNavLinks($userRole);
        $this->changeTheme();
        $theme = $this->getTheme();

        include './views/header.php';
    }

    private function changeTheme()
    {
        if (!isset($_SESSION['theme'])) {
            $_SESSION['theme'] = 'light';
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_theme'])) {
            $_SESSION['theme'] = $_SESSION['theme'] === 'dark' ? 'light' : 'dark';
        }

        $this->theme = $_SESSION['theme'];
    }

    public function getTheme()
    {
        return $this->theme;
    }

    public function getNavLinks($userRole)
    {
        $links = [
            'home' => 'index.php?action=home'
        ];

        if ($userRole === 'admin') {
            $links['users'] = 'index.php?action=users';
            $links['settings'] = '../settings.php';
        }

        if (in_array($userRole, ['user', 'moderator'])) {
            $links['groups'] = 'index.php?action=groups';
        }

        if ($userRole === 'user') {
            $links['profile'] = 'index.php?action=profile';
        }

        $links['logout'] = 'index.php?action=logout';

        return $links;
    }

    public function getUserRole()
    {
        if (!isset($_SESSION['user_id'])) {
            return 'guest';
        }

        $userId = intval($_SESSION['user_id']);
        $user = $this->userModel->findUserById($userId);

        if (!$user) {
            session_unset();
            session_destroy();
            header('Location: index.php?action=login');
            exit();
        }

        return $user['role'] ?? 'guest';
    }
}
