<?php

namespace models;

class SuggestedPostsModel
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function addPost($group_id, $user_id, $content, $image_id)
    {
        $sql = "INSERT INTO group_suggested_posts (group_id, user_id, content, status, image_id)
                VALUES (?, ?, ?, 'on_moderation', ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('iisi', $group_id, $user_id, $content, $image_id);
        return $stmt->execute();
    }

    public function deletePost($post_id, $user_id, $user_role)
    {
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

    public function updatePost($post_id, $content, $status = 'on_moderation')
    {
        $sql = "UPDATE group_suggested_posts SET content = ?, status = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('ssi', $content, $status, $post_id);
        return $stmt->execute();
    }

public function getPosts($group_id, $user_id, $user_role)
{
$sql = $user_role === 'moderator' ?
"SELECT p.id, p.content, p.user_id, u.username AS author, p.created_at, p.status, u.rating, p.image_id,
(SELECT COUNT(*) FROM group_suggested_posts WHERE user_id = p.user_id AND status = 'approved' AND group_id = ?) AS approved_count,
(SELECT COUNT(*) FROM group_suggested_posts WHERE user_id = p.user_id AND status = 'rejected' AND group_id = ?) AS rejected_count,
(SELECT COUNT(*) FROM group_suggested_posts WHERE user_id = p.user_id AND status = 'on_moderation' AND group_id = ?) AS on_moderation_count,
(u.rating * COALESCE(CAST((SELECT value FROM settings WHERE name = 'weight_factor1') AS DECIMAL(10, 2)), 0)) AS final_weight
FROM group_suggested_posts AS p
JOIN users AS u ON p.user_id = u.id
WHERE p.group_id = ?
ORDER BY 
FIELD(p.status, 'on_moderation', 'on_revision', 'rejected', 'approved'), 
final_weight DESC,
p.created_at DESC;" :
"SELECT id, content, user_id, created_at, status, image_id,
(SELECT comment FROM post_history WHERE post_id = group_suggested_posts.id ORDER BY id DESC LIMIT 1) AS comment
FROM group_suggested_posts
WHERE group_id = ? AND user_id = ? AND status != ''
ORDER BY FIELD(status, 'on_revision', 'on_moderation', 'rejected', 'approved'), created_at DESC";
$stmt = $this->conn->prepare($sql);
$user_role === 'moderator' ?
$stmt->bind_param('iiii', $group_id, $group_id, $group_id, $group_id) :
$stmt->bind_param('ii', $group_id, $user_id);;
$stmt->execute();
return $stmt->get_result();
}

    public function updatePostStatus($post_id, $status)
    {
        $sql = "UPDATE group_suggested_posts SET status = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('si', $status, $post_id);
        return $stmt->execute();
    }
}
