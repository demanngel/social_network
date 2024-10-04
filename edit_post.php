<?php
include './db.php';
include 'header.php';

try {
    if (!isset($_SESSION['user_id'])) {
        throw new Exception("Вы не авторизованы. Пожалуйста, войдите в систему.");
    }

    $post_id = intval($_GET['id']);
    $group_id = intval($_GET['group_id']);
    $user_id = $_SESSION['user_id'];

    $sql = "SELECT id, content FROM group_approved_posts WHERE id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param('i', $post_id);
        $stmt->execute();
        $post_result = $stmt->get_result();
        $post = $post_result->fetch_assoc();
        $stmt->close();
    } else {
        throw new Exception("Ошибка при подготовке запроса: " . $conn->error);
    }

    if (!$post) {
        throw new Exception("Ошибка: Пост не найден.");
    }

    if (isset($_POST['update_post'])) {
        $updated_content = $_POST['post_content'];
        $sql = "UPDATE group_approved_posts SET content = ? WHERE id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param('si', $updated_content, $post_id);
            $stmt->execute();
            $stmt->close();
            header('Location: group.php?id=' . $_GET['group_id']);
            exit();
        } else {
            throw new Exception("Ошибка при обновлении поста: " . $conn->error);
        }
    }
} catch (Exception $e) {
    header('Location: error.php?message=' . urlencode($e->getMessage()));
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit post</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="container">
    <div class="back-button action-button">
        <a href="group.php?id=<?php echo $group_id?>>>">🠔</a>
    </div>
    <h1>Edit post</h1>
    <form action="edit_post.php?id=<?php echo $post_id; ?>&group_id=<?php echo $_GET['group_id']; ?>" method="POST">
        <textarea name="post_content" class="post_content" required><?php echo htmlspecialchars($post['content']); ?></textarea>
        <button type="submit" name="update_post">Save changes</button>
    </form>
</div>
</body>
</html>
