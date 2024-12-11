<?php
require_once __DIR__ . '/../vendor/autoload.php';

use models\UserModel;
use PHPUnit\Framework\TestCase;

class UserModelTest extends TestCase
{
    private $mockConnection;
    private $userModel;

    protected function setUp(): void
    {
        $this->mockConnection = $this->createMock(mysqli::class);
        $this->userModel = new UserModel($this->mockConnection);
    }

    public function testFindUserByUsername()
    {
        $mockStmt = $this->createMock(mysqli_stmt::class);
        $mockResult = $this->createMock(mysqli_result::class);

        $mockStmt->expects($this->once())->method('bind_param')->with('s', 'testuser');
        $mockStmt->expects($this->once())->method('execute');
        $mockStmt->expects($this->once())->method('get_result')->willReturn($mockResult);

        $mockResult->expects($this->once())->method('fetch_assoc')->willReturn([
            'username' => 'testuser',
            'email' => 'test@example.com',
        ]);

        $this->mockConnection->expects($this->once())->method('prepare')->with('SELECT * FROM users WHERE username = ?')->willReturn($mockStmt);

        $result = $this->userModel->findUserByUsername('testuser');
        $this->assertEquals('testuser', $result['username']);
    }

    public function testRegisterUser()
    {
        $mockStmt = $this->createMock(mysqli_stmt::class);

        $mockStmt->expects($this->once())->method('bind_param')
            ->with('ssss', 'testuser', 'test@example.com', 'hashed_password', 'user');
        $mockStmt->expects($this->once())->method('execute')->willReturn(true);

        $this->mockConnection->expects($this->once())->method('prepare')
            ->with('INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)')
            ->willReturn($mockStmt);

        $result = $this->userModel->registerUser('testuser', 'test@example.com', 'hashed_password', 'user');
        $this->assertTrue($result);


    }
}

