<?php

use PHPUnit\Framework\TestCase;
use controllers\AuthController;
use models\UserModel;

class AuthTest extends TestCase
{
    private $mockConn;
    private $mockUserModel;
    private $authController;

    protected function setUp(): void
    {
        $this->mockConn = $this->createMock(mysqli::class);
        $this->mockUserModel = $this->createMock(UserModel::class);

        $this->authController = new AuthController($this->mockConn, $this->mockUserModel);
    }

    public function testRegisterSuccess()
    {
        $this->mockUserModel->method('findUserByUsername')->willReturn(null);
        $this->mockUserModel->method('findUserByEmail')->willReturn(null);

        $this->mockUserModel->method('registerUser')->willReturn(true);

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'password123',
            'role' => 'user',
        ];


        $this->authController->register();

        $headers = xdebug_get_headers();
        $this->assertContains('Location: index.php?action=login&success=registered', $headers);
    }

    public function testRegisterUsernameExists()
    {
        $this->mockUserModel->method('findUserByUsername')->willReturn(['id' => 1, 'username' => 'testuser']);

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'password123',
            'role' => 'user',
        ];

        $this->authController->register();

        $headers = xdebug_get_headers();
        $this->assertContains('Location: index.php?action=register&error=user_exists_by_username', $headers);
    }

    public function testLoginSuccess()
    {
        $this->mockUserModel->method('findUserByUsername')->willReturn([
            'id' => 1,
            'username' => 'testuser',
            'password' => password_hash('password123', PASSWORD_DEFAULT),
        ]);

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'username' => 'testuser',
            'password' => 'password123',
        ];

        session_start();

        $this->authController->login();

        $headers = xdebug_get_headers();
        $this->assertContains('Location: index.php?action=home', $headers);

        $this->assertEquals(1, $_SESSION['user_id']);
    }

    public function testLoginFailure()
    {
        $this->mockUserModel->method('findUserByUsername')->willReturn(null);

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'username' => 'nonexistent',
            'password' => 'password123',
        ];

        $this->authController->login();

        $headers = xdebug_get_headers();
        $this->assertContains('Location: index.php?action=login&error=user_does_not_exist', $headers);
    }

    public function testLogout()
    {
        session_start();
        $_SESSION['user_id'] = 1;

        $this->authController->logout();

        $headers = xdebug_get_headers();
        $this->assertContains('Location: index.php?action=login', $headers);

        $this->assertArrayNotHasKey('user_id', $_SESSION);
    }
}
