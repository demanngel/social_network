<?php

namespace models;

class GroupModel {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getGroups($user_id, $search, $user_role) {
        $sql = $user_role === 'user'
            ? "SELECT g.id, g.name, g.description, g.created_by, u.username AS creator, 
                      IF(gm.user_id IS NOT NULL, 1, 0) AS is_member
               FROM `groups` AS g
               JOIN users AS u ON g.created_by = u.id
               LEFT JOIN group_members AS gm ON g.id = gm.group_id AND gm.user_id = ?
               WHERE g.name LIKE ? OR g.description LIKE ?
               ORDER BY is_member DESC, g.name"
            : "SELECT g.id, g.name, g.description
               FROM `groups` AS g
               WHERE g.created_by = ? and (g.name LIKE ? OR g.description LIKE ?)";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('iss', $user_id, $search, $search);
        $stmt->execute();
        return $stmt->get_result();
    }

    public function createGroup($name, $description, $user_id) {
        $sql = "INSERT INTO `groups` (name, description, created_by) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('ssi', $name, $description, $user_id);
        return $stmt->execute();
    }

    public function deleteGroup($group_id, $user_id) {
        $sql = "DELETE FROM `groups` WHERE id = ? AND created_by = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('ii', $group_id, $user_id);
        return $stmt->execute();
    }

    public function joinGroup($group_id, $user_id) {
        $sql = "INSERT INTO group_members (group_id, user_id) VALUES (?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('ii', $group_id, $user_id);
        return $stmt->execute();
    }

    public function leaveGroup($group_id, $user_id) {
        $sql = "DELETE FROM group_members WHERE group_id = ? AND user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('ii', $group_id, $user_id);
        return $stmt->execute();
    }

    public function getGroupById($group_id, $user_id) {
        $sql = "SELECT g.id, g.name, g.description, u.username AS creator, 
                       COUNT(m.user_id) AS subscriber_count,
                       (SELECT COUNT(*) FROM group_members WHERE group_id = ? AND user_id = ?) AS is_member
                FROM `groups` AS g
                JOIN users AS u ON g.created_by = u.id
                LEFT JOIN group_members AS m ON g.id = m.group_id
                WHERE g.id = ?
                GROUP BY g.id, g.name, g.description, u.username";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('iii', $group_id, $user_id, $group_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function getGroupPosts($group_id) {
        $sql = "SELECT p.id, p.content, p.created_at, u.username AS author 
            FROM group_approved_posts AS p
            JOIN users AS u ON p.author_id = u.id
            WHERE p.group_id = ?
            ORDER BY p.created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $group_id);
        $stmt->execute();
        return $stmt->get_result();
    }

    public function addPost($group_id, $author_id, $content) {
        $sql = "INSERT INTO group_suggested_posts (group_id, author_id, content, created_at) 
            VALUES (?, ?, ?, NOW())";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('iis', $group_id, $author_id, $content);
        return $stmt->execute();
    }

    public function deletePost($post_id, $user_id, $user_role) {
        $sql = $user_role === 'admin'
            ? "DELETE FROM group_posts WHERE id = ?"
            : "DELETE FROM group_posts WHERE id = ? AND author_id = ?";
        $stmt = $this->conn->prepare($sql);
        if ($user_role === 'admin') {
            $stmt->bind_param('i', $post_id);
        } else {
            $stmt->bind_param('ii', $post_id, $user_id);
        }
        return $stmt->execute();
    }

    public function updatePost($post_id, $content, $user_id) {
        $sql = "UPDATE posts SET content = ? WHERE id = ? AND author_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('sii', $content, $post_id, $user_id);
        return $stmt->execute();
    }

    public function updateGroup($group_id, $name, $description, $user_id) {
        $sql = "UPDATE `groups` SET name = ?, description = ? WHERE id = ? AND created_by = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('ssii', $name, $description, $group_id, $user_id);
        return $stmt->execute();
    }

}
