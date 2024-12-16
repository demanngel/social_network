<?php

namespace models;

class ApprovedPostsModel
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function getGroupPosts($group_id, $search)
    {
        $sql = "SELECT p.id, p.content, p.created_at, p.image_id
                FROM group_approved_posts AS p
                WHERE p.group_id = ? AND p.content LIKE ?
                ORDER BY p.created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('is', $group_id, $search);
        $stmt->execute();
        return $stmt->get_result();
    }

    public function addPost($post_id)
    {
        $sql = "INSERT INTO group_approved_posts (group_id, content, image_id) 
                SELECT group_id, content, image_id 
                FROM group_suggested_posts WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $post_id);
        return $stmt->execute();
    }

    public function deletePost($post_id, $user_id, $user_role)
    {
        $sql = $user_role === 'admin'
            ? "DELETE FROM group_posts WHERE id = ?"
            : "DELETE FROM group_posts WHERE id = ? AND author_id = ?";
        $stmt = $this->db->prepare($sql);
        if ($user_role === 'admin') {
            $stmt->bind_param('i', $post_id);
        } else {
            $stmt->bind_param('ii', $post_id, $user_id);
        }
        return $stmt->execute();
    }

}
