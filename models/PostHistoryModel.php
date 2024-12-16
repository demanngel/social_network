<?php

namespace models;
class PostHistoryModel
{
    private $conn;
    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function getPostsHistory($post_id) {
        $sql = "SELECT ph.action, ph.comment, ph.action_time, u.username AS moderator, u.role
            FROM post_history AS ph
            LEFT JOIN users AS u ON ph.moderator_id = u.id
            WHERE ph.post_id = ?
            ORDER BY ph.action_time DESC";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param('i', $post_id);
            $stmt->execute();
            return $stmt->get_result();
    }

    public function addPostHistory($post_id, $action, $user_id, $comment = null) {
        $sql = "INSERT INTO post_history (post_id, action, moderator_id, comment) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('isis', $post_id, $action, $user_id, $comment);
        return $stmt->execute();
    }
}
