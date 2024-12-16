<?php

namespace models;
class SubscribersModel
{
    private $conn;
    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function getSubscribers($group_id, $search) {
        $sql = "SELECT u.id, u.username FROM group_members gm
            JOIN users u ON gm.user_id = u.id
            WHERE gm.group_id = ? AND u.username LIKE ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('is', $group_id, $search);
        $stmt->execute();
        return $stmt->get_result();
    }

    public function deleteSubscriber($group_id, $user_id) {
        $sql = "DELETE FROM group_members WHERE group_id = ? AND user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('ii', $group_id, $user_id);
        return $stmt->execute();
    }
}
