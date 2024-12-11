<?php

namespace controllers;

use models\UserModel;

require_once __DIR__ . '/../vendor/autoload.php';

class AuthController
{
    private $userModel;

    public function __construct($conn, $userModel = null)
    {
        $this->userModel = $userModel ?: new UserModel($conn);
    }

    public function handleLoginRequest()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->login();
        } else {
            $this->viewLoginForm();
        }
    }

    public function handleRegisterRequest()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->register();
        } else {
            $this->viewRegisterForm();
        }
    }

    public function viewLoginForm()
    {
        include './views/login.php';
    }
    public function login()
    {
        session_start();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'];
            $password = $_POST['password'];
            $user = $this->userModel->findUserByUsername($username);

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                header('Location: index.php?action=home');
            } else {
                header('Location: index.php?action=login&error=user_does_not_exist');
            }
        }
    }

    public function viewRegisterForm()
    {
        include './views/register.php';
    }

    public function register()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'];
            $email = $_POST['email'];
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $role = $_POST['role'];

            $userByUsername = $this->userModel->findUserByUsername($username);
            $userByEmail = $this->userModel->findUserByEmail($email);

            if ($userByUsername) {
                header('Location: index.php?action=register&error=user_exists_by_username');
            } else if ($userByEmail) {
                header('Location: index.php?action=register&error=user_exists_by_email');
            } else if ($this->userModel->registerUser($username, $email, $password, $role)) {
                header('Location: index.php?action=login&success=registered');
            } else {
                header('Location: index.php?action=register&error=exception');
            }
        }
    }

    public function logout()
    {
        session_start();
        session_unset();
        session_destroy();
        header('Location: index.php?action=login');
    }
}
