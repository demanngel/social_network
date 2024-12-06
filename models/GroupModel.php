<?php
namespace models;
class GroupModel {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getUserGroups($user_id, $search) {
        $sql = "SELECT g.id, g.name, g.description, g.created_by, u.username AS creator, 
                       IF(gm.user_id IS NOT NULL, 1, 0) AS is_member
                FROM `groups` AS g
                JOIN users AS u ON g.created_by = u.id
                LEFT JOIN group_members AS gm ON g.id = gm.group_id AND gm.user_id = ?
                WHERE g.name LIKE ? OR g.description LIKE ?
                ORDER BY is_member DESC, g.name";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('iss', $user_id, $search, $search);
        $stmt->execute();
        return $stmt->get_result();
    }

    public function getModeratorGroups($user_id) {
        $sql = "SELECT g.id, g.name, g.description 
                FROM `groups` AS g 
                WHERE g.created_by = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        return $stmt->get_result();
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
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $result;
    }

    public function getPostsByGroup($group_id, $search_term) {
        $sql = "SELECT p.id, p.content, p.created_at, p.image_id
                FROM group_approved_posts AS p
                WHERE p.group_id = ? AND p.content LIKE ?
                ORDER BY p.created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $search_param = '%' . $search_term . '%';
        $stmt->bind_param('is', $group_id, $search_param);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $result;
    }

    public function getTopics() {
        $sql = "SELECT id, name FROM topics";
        return $this->conn->query($sql)->fetch_all(MYSQLI_ASSOC);
    }

    public function addPost($group_id, $user_id, $content, $topic_id, $image_id) {
        $sql = "INSERT INTO group_suggested_posts (group_id, user_id, content, status, topic_id, image_id)
                VALUES (?, ?, ?, 'on_moderation', ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('iisii', $group_id, $user_id, $content, $topic_id, $image_id);
        $stmt->execute();
        $stmt->close();
    }
}

