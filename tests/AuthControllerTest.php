<?php
require_once __DIR__ . '/../vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use controllers\AuthController;
use models\UserModel;

class AuthControllerTest extends TestCase
{
    private $mockModel;
    private $mockConn;
    private $authController;

    protected function setUp(): void
    {
        $this->mockConn = $this->createMock(PDO::class);
        $this->mockModel = $this->createMock(UserModel::class);

        $this->authController = new AuthController($this->mockConn, $this->mockModel);
    }

    public function testRegisterUserExistsByUsername()
    {
        $this->mockModel->expects($this->once())
            ->method('findUserByUsername')
            ->with('existingUser')
            ->willReturn(['id' => 1]);

        $this->mockModel->expects($this->once())
            ->method('findUserByEmail');

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'username' => 'existingUser',
            'email' => 'test@example.com',
            'password' => 'password123',
            'role' => 'user',
        ];

        $this->authController->register();

        $headers = xdebug_get_headers();
        $this->assertContains('Location: index.php?action=register&error=user_exists_by_username', $headers);
    }

    public function testRegisterUserExistsByEmail()
    {
        $this->mockModel->expects($this->once())
            ->method('findUserByUsername')
            ->with('newUser')
            ->willReturn(null);

        $this->mockModel->expects($this->once())
            ->method('findUserByEmail')
            ->with('existing@example.com')
            ->willReturn(['id' => 1]);

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'username' => 'newUser',
            'email' => 'existing@example.com',
            'password' => 'password123',
            'role' => 'user',
        ];

        $this->authController->register();

        $headers = xdebug_get_headers();
        $this->assertContains('Location: index.php?action=register&error=user_exists_by_email', $headers);
    }
}