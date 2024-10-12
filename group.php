 <?php
include './db.php';
include 'header.php';

try {
    if ($conn->connect_error) {
        throw new Exception("Ошибка подключения к базе данных: " . $conn->connect_error);
    }

    if (!isset($_SESSION['user_id'])) {
        header('Location: loginForm.php');
        exit();
    }

    $user_id = $_SESSION['user_id'];
    $user_role = $_SESSION['user_role'] ?? 'user';

    $group_id = intval($_GET['id']);
    $search_term = $_GET['search'] ?? '';

    $sql = "SELECT g.id, g.name, g.description, u.username AS creator
            FROM `groups` AS g
            JOIN users AS u ON g.created_by = u.id
            WHERE g.id = ?";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param('i', $group_id);
        $stmt->execute();
        $group_result = $stmt->get_result();
        $group = $group_result->fetch_assoc();
        $stmt->close();
    } else {
        throw new Exception("Ошибка при получении информации о группе: " . $conn->error);
    }

    if (!$group) {
        throw new Exception("Группа не найдена.");
    }

    $is_member = false;
    if ($user_role == 'user') {
        $sql = "SELECT COUNT(*) FROM group_members WHERE group_id = ? AND user_id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param('ii', $group_id, $user_id);
            $stmt->execute();
            $stmt->bind_result($is_member_count);
            $stmt->fetch();
            $is_member = $is_member_count > 0;
            $stmt->close();
        } else {
            throw new Exception("Ошибка при проверке членства: " . $conn->error);
        }

        if (isset($_POST['add_post'])) {
            $post_content = $_POST['post_content'];
            $sql = "INSERT INTO group_suggested_posts (group_id, user_id, content, status) VALUES (?, ?, ?, 'on_moderation')";
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param('iis', $group_id, $user_id, $post_content);
                $stmt->execute();
                $stmt->close();
                header("Location: group.php?id=$group_id");
                exit();
            } else {
                throw new Exception("Ошибка при добавлении поста: " . $conn->error);
            }
        }

        if (isset($_POST['join_group'])) {
            $sql = "INSERT INTO group_members (group_id, user_id) VALUES (?, ?)";
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param('ii', $group_id, $user_id);
                $stmt->execute();
                $stmt->close();
                header('Location: group.php?id=' . $group_id);
                exit();
            } else {
                throw new Exception("Ошибка при добавлении пользователя в группу: " . $conn->error);
            }
        }

        if (isset($_POST['leave_group'])) {
            $sql = "DELETE FROM group_members WHERE group_id = ? AND user_id = ?";
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param('ii', $group_id, $user_id);
                $stmt->execute();
                $stmt->close();
                header('Location: group.php?id=' . $group_id);
                exit();
            } else {
                throw new Exception("Ошибка при выходе из группы: " . $conn->error);
            }
        }
    }

    if ($user_role == 'moderator') {
        if (isset($_POST['delete_post'])) {
            $post_id = intval($_POST['post_id']);
            $sql = "DELETE FROM group_approved_posts WHERE id = ?";
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param('i', $post_id);
                $stmt->execute();
                $stmt->close();
                header("Location: group.php?id=$group_id");
                exit();
            } else {
                throw new Exception("Ошибка при удалении поста: " . $conn->error);
            }
        }

        if (isset($_POST['delete_group'])) {
            $sql = "DELETE FROM `groups` WHERE id = ?";
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param('i', $group_id);
                $stmt->execute();
                $stmt->close();

                $sql = "DELETE FROM group_members WHERE group_id = ?";
                if ($stmt = $conn->prepare($sql)) {
                    $stmt->bind_param('i', $group_id);
                    $stmt->execute();
                    $stmt->close();
                }
                $sql = "DELETE FROM group_suggested_posts WHERE group_id = ?";
                if ($stmt = $conn->prepare($sql)) {
                    $stmt->bind_param('i', $group_id);
                    $stmt->execute();
                    $stmt->close();
                }
                header("Location: groups.php");
                exit();
            } else {
                throw new Exception("Ошибка при удалении группы: " . $conn->error);
            }
        }
    }

    $sql = "SELECT p.id, p.content, p.created_at
            FROM group_approved_posts AS p
            WHERE p.group_id = ? AND p.content LIKE ?
            ORDER BY p.created_at DESC";

    if ($stmt = $conn->prepare($sql)) {
        $search_param = '%' . $search_term . '%';
        $stmt->bind_param('is', $group_id, $search_param);
        $stmt->execute();
        $posts_result = $stmt->get_result();
        $stmt->close();
    }

    $sql = "SELECT COUNT(*) AS subscriber_count FROM group_members WHERE group_id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param('i', $group_id);
        $stmt->execute();
        $stmt->bind_result($subscriber_count);
        $stmt->fetch();
        $stmt->close();
    } else {
        throw new Exception("Ошибка при получении количества подписчиков: " . $conn->error);
    }
} catch (Exception $e) {
    header('Location: error.php?message=' . urlencode($e->getMessage()));
    exit();
}
?>

<div class="container groups">
    <div class="back-button action-button">
        <a href="groups.php?">🠔</a>
    </div>
    <h1><?php echo htmlspecialchars($group['name']); ?></h1>
    <div class="group">
        <div class="group_info">
            <p>Description: <?php echo htmlspecialchars($group['description']); ?></p>
            <p>Created by: <?php echo htmlspecialchars($group['creator']); ?></p>
            <p>Subscribers: <a href="subscribers.php?group_id=<?php echo $group_id; ?>"><?php echo $subscriber_count; ?></a></p>
        </div>
        <div class="actions group-actions">
            <?php if ($user_role == 'moderator'): ?>
                <form action="edit_group.php" method="GET" >
                    <input type="hidden" name="group_id" value="<?php echo $group_id; ?>">
                    <button type="submit" class="action-button">✎</button>
                </form>
                <form action="group.php?id=<?php echo $group_id; ?>" method="POST" onsubmit="return confirm('Are you sure you want to delete this group?');">
                    <input type="hidden" name="delete_group" value="true">
                    <button type="submit" class="action-button">🗑</button>
                </form>
            <?php endif; ?>
            <?php if ($user_role == 'user'): ?>
                <div class="group-actions">
                    <form action="group.php?id=<?php echo $group_id; ?>" method="POST">
                        <?php if ($is_member): ?>
                            <button type="submit" name="leave_group">Leave Group</button>
                        <?php else: ?>
                            <button type="submit" name="join_group">Join Group</button>
                        <?php endif; ?>
                    </form>
                </div>
            <?php endif; ?>
            <?php if ($user_role == 'moderator' || $is_member): ?>
                <form action="suggested_posts.php" method="GET">
                    <input type="hidden" name="group_id" value="<?php echo $group_id; ?>">
                    <button type="submit" class="action-button action-button1">Suggested posts</button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($is_member): ?>
        <form action="group.php?id=<?php echo $group_id; ?>" method="POST">
            <textarea name="post_content" class="post_content" required></textarea>
            <button type="submit" name="add_post">Add post</button>
        </form>
    <?php endif; ?>
    <?php if ($is_member || $user_role == "moderator"): ?>
        <form action="group.php" method="GET">
            <div class="search-container">
                <input type="hidden" name="id" value="<?php echo $group_id; ?>">
                <input type="text" name="search" placeholder="Search for posts" value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                <?php if (!empty($search_term)): ?>
                    <button class="search-button" type="submit" name="search" value="">×</button>
                <?php endif; ?>
                <button type="submit">Search</button>
            </div>
        </form>
        <div class="posts-container">
            <?php while ($post = $posts_result->fetch_assoc()): ?>
                <div class="post">
                    <div class="post-info">
                        <div class="post-content">
                            <p><?php echo htmlspecialchars($post['content']); ?></p>
                        </div>
                        <div class="post-date">
                            <p><?php echo htmlspecialchars($post['created_at']); ?></p>
                        </div>
                    </div>
                    <?php if ($user_role == 'moderator'): ?>
                        <div class="actions">
                            <form action="edit_post.php?id=<?php echo $post['id'];?>" method="GET">
                                <input type="hidden" name="id" value="<?php echo $post['id']; ?>">
                                <input type="hidden" name="group_id" value="<?php echo $group_id; ?>">
                                <button type="submit" class="action-button">✎</button>
                            </form>
                            <form action="group.php?id=<?php echo $group_id; ?>" method="POST">
                                <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                <button type="submit" name="delete_post" class="action-button">🗑</button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>
</div>
