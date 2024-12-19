<?php

namespace models;
class UserModel
{
    private $conn;
    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function findUserByUsername($username)
    {
        $sql = "SELECT * FROM users WHERE username = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('s', $username);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function findUserByEmail($email)
    {
        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('s', $email);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function registerUser($username, $email, $password, $role)
    {
        $sql = "INSERT INTO users (username, email, password, role, is_protected) VALUES (?, ?, ?, ?, 0)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('ssss', $username, $email, $password, $role);
        return $stmt->execute();
    }

    public function findUserById($userId)
    {
        $sql = "SELECT * FROM users WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function getRating($user_id)
    {
        $sql = "SELECT rating FROM users WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function updateRating($rating, $user_id)
    {
        $sql = "UPDATE users SET rating = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('ii', $rating, $user_id);
        return $stmt->execute();
    }

    public function updateProfileImage($user_id, $image_id)
    {
        $update_sql = "UPDATE users SET image_id = ? WHERE id = ?";
        if ($update_stmt = $this->conn->prepare($update_sql)) {
            $update_stmt->bind_param('si', $image_id, $user_id);
            $update_stmt->execute();
            $update_stmt->close();
        }
    }

    public function deleteUserById($user_id) {
        $sql = "DELETE FROM users WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $user_id);
        return $stmt->execute();
    }

    public function getUsers($search) {
        $sql = "SELECT id, username, email, role FROM users WHERE username LIKE ? AND is_protected = 0 ORDER BY role";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('s', $search);
        $stmt->execute();
        return $stmt->get_result();
    }

    public function updateUser($user_id, $username, $email) {
        $sql = "UPDATE users SET username = ?, email = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('ssi', $username, $email, $user_id);
        return $stmt->execute();
    }

}
