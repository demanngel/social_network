<?php
include './db.php';
include 'header.php';

try {
    if (!isset($_SESSION['user_id'])) {
        header('Location: loginForm.php');
        exit();
    }
    $group_id = intval($_GET['group_id']);
    $user_id = $_SESSION['user_id'];
    $user_role = $_SESSION['user_role'] ?? 'user';

if($user_role == 'moderator') {
    $sql = "SELECT p.id, p.content, p.user_id, u.username AS author, p.created_at, p.status, u.rating,
    (SELECT COUNT(*) FROM group_suggested_posts WHERE user_id = p.user_id AND status = 'approved' AND group_id = ?) AS approved_count,
    (SELECT COUNT(*) FROM group_suggested_posts WHERE user_id = p.user_id AND status = 'rejected' AND group_id = ?) AS rejected_count,
    (SELECT COUNT(*) FROM group_suggested_posts WHERE user_id = p.user_id AND status = 'on_moderation' AND group_id = ?) AS on_moderation_count
    FROM group_suggested_posts AS p
    JOIN users AS u ON p.user_id = u.id
    WHERE p.group_id = ?
    ORDER BY 
    FIELD(p.status, 'on_moderation', 'on_revision', 'rejected', 'approved'), 
    u.rating DESC, 
    p.created_at DESC;
";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param('iiii', $group_id, $group_id, $group_id, $group_id);
        $stmt->execute();
        $posts_result = $stmt->get_result();
        $stmt->close();
    } else {
        throw new Exception("Ошибка при получении предложенных постов: " . $conn->error);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $post_id = intval($_POST['post_id']);
        $user_post_id = intval($_POST['user_id']);
        $comment = $_POST['comment'] ?? null;
        $action = $_POST['action'];
        $approved_count = $_POST['approved_count'];
        $rejected_count = $_POST['rejected_count'];
        $on_moderation_count = $_POST['on_moderation_count'];

        if ($action === 'approve') {
            $sql = "INSERT INTO group_approved_posts (group_id, content) 
                        SELECT group_id, content 
                        FROM group_suggested_posts WHERE id = ?";
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param('i', $post_id);
                $stmt->execute();
                $stmt->close();
            } else {
                throw new Exception("Ошибка при получении предложенных постов: " . $conn->error);
            }

            $sql = "INSERT INTO post_history (post_id, action, moderator_id, comment) VALUES (?, 'approved', ?, ?)";
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param('iis', $post_id, $_SESSION['user_id'], $comment);
                $stmt->execute();
                $stmt->close();
            } else {
                throw new Exception("Ошибка при получении предложенных постов: " . $conn->error);
            }

            $sql = "UPDATE group_suggested_posts SET status = 'approved' WHERE id = ?";
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param('i', $post_id);
                $stmt->execute();
                $stmt->close();
            } else {
                throw new Exception("Ошибка при получении предложенных постов: " . $conn->error);
            }

            $sql = "UPDATE users SET rating = rating + 1 + 0.1 * ? WHERE id = ?";
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param('ii', $approved_count,$user_post_id);
                $stmt->execute();
                $stmt->close();
            } else {
                throw new Exception("Ошибка при получении предложенных постов: " . $conn->error);
            }

            header("Location: suggested_posts.php?group_id=$group_id");
            exit();
        } elseif ($action === 'revision') {
            $sql = "INSERT INTO post_history (post_id, action, moderator_id, comment) VALUES (?, 'revision', ?, ?)";
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param('iis', $post_id, $_SESSION['user_id'], $comment);
                $stmt->execute();
                $stmt->close();
            } else {
                throw new Exception("Ошибка при получении предложенных постов: " . $conn->error);
            }

            $sql = "UPDATE group_suggested_posts SET status = 'on_revision' WHERE id = ?";
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param('i', $post_id);
                $stmt->execute();
                $stmt->close();
            } else {
                throw new Exception("Ошибка при получении предложенных постов: " . $conn->error);
            }

            $sql = "UPDATE users SET rating = rating - 1 - 0.1 * ? WHERE id = ?";
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param('ii', $on_moderation_count,$user_post_id);
                $stmt->execute();
                $stmt->close();
            } else {
                throw new Exception("Ошибка при получении предложенных постов: " . $conn->error);
            }

            header("Location: suggested_posts.php?group_id=$group_id");
            exit();
        } elseif ($action === 'reject') {
            $sql = "INSERT INTO post_history (post_id, action, moderator_id, comment) VALUES (?, 'rejected', ?, ?)";
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param('iis', $post_id, $_SESSION['user_id'], $comment);
                $stmt->execute();
                $stmt->close();
            } else {
                throw new Exception("Ошибка при получении предложенных постов: " . $conn->error);
            }

            $sql = "UPDATE group_suggested_posts SET status = 'rejected' WHERE id = ?";
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param('i', $post_id);
                $stmt->execute();
                $stmt->close();
            } else {
                throw new Exception("Ошибка при получении предложенных постов: " . $conn->error);
            }

            $sql = "UPDATE users SET rating = rating - 2 - 0.1 * ? WHERE id = ?";
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param('ii', $rejected_count,$user_post_id);
                $stmt->execute();
                $stmt->close();
            } else {
                throw new Exception("Ошибка при получении предложенных постов: " . $conn->error);
            }

            header("Location: suggested_posts.php?group_id=$group_id");
            exit();
        }
    }
}
    if($user_role == 'user') {
        $sql = "SELECT id, content, user_id, created_at, status, 
                (SELECT comment FROM post_history WHERE post_id = group_suggested_posts.id ORDER BY id DESC LIMIT 1) AS comment
                FROM group_suggested_posts
                WHERE group_id = ? AND user_id = ? AND status != ''
                ORDER BY FIELD(status, 'on_revision', 'on_moderation', 'rejected', 'approved'), created_at DESC";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param('ii', $group_id, $user_id);
            $stmt->execute();
            $posts_result = $stmt->get_result();
            $stmt->close();
        } else {
            throw new Exception("Ошибка при получении предложенных постов: " . $conn->error);
        }

        if (isset($_POST['send_to_moderation'])) {
            $post_id = intval($_POST['post_id']);
            $new_content = $_POST['content'];

            $sql = "UPDATE group_suggested_posts SET content = ?, status = 'on_moderation' WHERE id = ?";
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param('si', $new_content, $post_id);
                $stmt->execute();
                $stmt->close();
            } else {
                throw new Exception("Ошибка при получении предложенных постов: " . $conn->error);
            }

            $sql = "INSERT INTO post_history (post_id, action, moderator_id, comment) VALUES (?, 'resent_to_moderation', ?, NULL)";
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param('ii', $post_id, $user_id);
                $stmt->execute();
                $stmt->close();
            } else {
                throw new Exception("Ошибка при получении предложенных постов: " . $conn->error);
            }

            header("Location: suggested_posts.php?group_id=$group_id");
            exit();
        }
    }
} catch (Exception $e) {
    header('Location: error.php?message=' . urlencode($e->getMessage()));
    exit();
}
?>

<div class="container suggested-posts">
    <div class="back-button action-button">
        <a href="group.php?id=<?php echo $group_id?>">🠔</a>
    </div>
    <h1>Suggested posts</h1>
    <?php if($user_role == "moderator"): ?>
        <div class="posts-container">
            <?php while ($post = $posts_result->fetch_assoc()): ?>
                <div class="suggested-post">
                    <div class="post-container">
                        <div class="post">
                            <div class="post-info">
                                <div class="post-author">
                                    <p><strong><?php echo htmlspecialchars($post['author']); ?></strong></p>
                                </div>
                                <div class="post-content">
                                    <p><?php echo htmlspecialchars($post['content']); ?></p>
                                </div>
                                <div class="post-date">
                                    <p><?php echo htmlspecialchars($post['created_at']); ?></p>
                                </div>
                                <div class="post-status">
                                    <p>Status: <?php echo htmlspecialchars($post['status']); ?></p>
                                </div>
                                <div class="post-status">
                                    <p>Rating: <?php echo htmlspecialchars($post['rating']); ?></p>
                                </div>
                            <?php if($post['status'] == 'on_moderation'): ?>
                                    <form action="suggested_posts.php?group_id=<?php echo $group_id; ?>" method="POST">
                                        <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                        <input type="hidden" name="user_id" value="<?php echo $post['user_id']; ?>">
                                        <input type="hidden" name="approved_count" value="<?php echo $post['approved_count']; ?>">
                                        <input type="hidden" name="rejected_count" value="<?php echo $post['rejected_count']; ?>">
                                        <input type="hidden" name="on_moderation_count" value="<?php echo $post['on_moderation_count']; ?>">

                                        <input type="hidden" name="action" value="">

                                        <textarea name="comment" placeholder="Comment"></textarea>

                                        <div class="suggested-post-actions">
                                            <button type="submit" name="approve_post" class="action-button action-button1" onclick="this.form.action.value='approve';">Approve</button>
                                            <button type="submit" name="revision_post" class="action-button action-button1" onclick="this.form.action.value='revision';">For revision</button>
                                            <button type="submit" name="reject_post" class="action-button action-button1" onclick="this.form.action.value='reject';" onsubmit="return confirm('Вы уверены, что хотите отклонить пост?');">Reject</button>
                                        </div>
                                    </form>
                            <?php endif?>
                            </div>
                            <div class="history">
                                <form action="post_history.php?post_id=<?php echo $post['id']; ?>" method="GET" class="history">
                                    <input type="hidden" name="id" value="<?php echo $post['id']; ?>">
                                    <input type="hidden" name="group_id" value="<?php echo $group_id; ?>">
                                    <button type="submit" class="action-button"><i class="fas fa-clock"></i></button>
                                </form>
                            </div>

                        </div>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>
    <?php if($user_role == "user"): ?>
        <div class="posts-container">
            <?php while ($post = $posts_result->fetch_assoc()): ?>
                <div class="suggested-post">
                    <div class="post-info">
                        <div class="post-content">
                            <p><?php echo htmlspecialchars($post['content']); ?></p>
                        </div>
                        <div class="post-date">
                            <p><?php echo htmlspecialchars($post['created_at']); ?></p>
                        </div>
                        <div class="post-status">
                            <p>Status: <?php echo htmlspecialchars($post['status']); ?></p>
                        </div>
                        <?php if (!empty($post['comment'])): ?>
                            <div class="post-comment">
                                <p><strong>Comment:</strong> <?php echo htmlspecialchars($post['comment']); ?></p>
                            </div>
                        <?php endif; ?>

                        <?php if ($post['status'] == 'on_revision'): ?>
                            <form action="suggested_posts.php?group_id=<?php echo $group_id;?>" method="POST">
                                <textarea name="content"><?php echo htmlspecialchars($post['content']); ?></textarea>
                                <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                <button name="send_to_moderation" type="submit">Send for moderation</button>
                            </form>
                        <?php endif; ?>
                    </div>

                    <div class="history">
                        <form action="post_history.php?post_id=<?php echo $post['id']; ?>" method="GET" class="history">
                            <input type="hidden" name="id" value="<?php echo $post['id']; ?>">
                            <input type="hidden" name="group_id" value="<?php echo $group_id; ?>">
                            <button type="submit" class="action-button"><i class="fas fa-clock"></i></button>
                        </form>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>
</div>