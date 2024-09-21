<?php
session_start();
include './db.php';

try {
    if (!isset($_SESSION['user_id'])) {
        header('Location: loginForm.php');
        exit();
    }

    $user_id = $_SESSION['user_id'];
    $sql = "SELECT id FROM users WHERE id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            session_destroy();
            header('Location: loginForm.php?error=user_deleted');
            exit();
        }
    } else {
        throw new Exception("Ошибка при подготовке запроса: " . $conn->error);
    }

    if ($conn->connect_error) {
        throw new Exception('Ошибка подключения к базе данных: ' . $conn->connect_error);
    }

    $create_table_sql = "
        CREATE TABLE IF NOT EXISTS users (
            id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            email VARCHAR(100) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";

    if (!$conn->query($create_table_sql)) {
        throw new Exception("Ошибка при создании таблицы: " . $conn->error);
    }

    $createTableSQL = "
        CREATE TABLE IF NOT EXISTS posts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT UNSIGNED NOT NULL,
            content TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB;
    ";

    if (!$conn->query($createTableSQL)) {
        throw new Exception('Ошибка при создании таблицы постов: ' . $conn->error);
    }

    if (isset($_GET['delete'])) {
        $post_id = intval($_GET['delete']);
        $sql = "DELETE FROM posts WHERE id = ?";
        /*$sql = "DELETE FROM posts WHERE id = ? AND user_id = ?";*/
        if ($stmt = $conn->prepare($sql)) {
            /*$stmt->bind_param('ii', $post_id, $_SESSION['user_id']);*/
            $stmt->bind_param('i', $post_id);
            if ($stmt->execute()) {
                header('Location: posts.php?message=post_deleted');
                exit();
            } else {
                throw new Exception("Ошибка при удалении поста.");
            }
            $stmt->close();
        } else {
            throw new Exception("Ошибка при подготовке запроса: " . $conn->error);
        }
    }

    $updated_post = null;
    if (isset($_GET['update'])) {
        $post_id = intval($_GET['update']);
        $sql = "SELECT posts.id AS post_id, posts.content, posts.created_at, posts.user_id, users.username 
                FROM posts 
                JOIN users ON posts.user_id = users.id 
                WHERE posts.id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param('i', $post_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $updated_post = $result->fetch_assoc();
            $stmt->close();
        } else {
            throw new Exception("Ошибка при подготовке запроса: " . $conn->error);
        }
    }
    
    
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['content'])) {
        $content = $_POST['content'];
        $user_id = $_SESSION['user_id'];
        $sql = "INSERT INTO posts (user_id, content, created_at) VALUES (?, ?, NOW())";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param('is', $user_id, $content);
            if ($stmt->execute()) {
                header('Location: posts.php?message=post_added');
                exit();
            } else {
                throw new Exception("Ошибка при добавлении поста.");
            }
            $stmt->close();
        } else {
            throw new Exception("Ошибка при подготовке запроса: " . $conn->error);
        }
    }

    $search = '';
    if (isset($_GET['search'])) {
        $search = $_GET['search'];
    }

    $sql = "SELECT posts.id, posts.content, posts.created_at, users.username 
        FROM posts
        JOIN users ON posts.user_id = users.id
        WHERE (posts.content LIKE ? OR users.username LIKE ? OR posts.created_at LIKE ?) 
        ORDER BY posts.created_at DESC";

    $search_query = "%$search%";
    if(!isset($_GET['update'])) {
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param('sss', $search_query, $search_query, $search_query);
            $stmt->execute();
            $result = $stmt->get_result();
            $_SESSION['posts'] = $result->fetch_all(MYSQLI_ASSOC);
        } else {
            throw new Exception("Ошибка при подготовке запроса: " . $conn->error);
        } 
    }

    if ($updated_post != null) {
        if (isset($_SESSION['posts'])) {
            foreach ($_SESSION['posts'] as $index => $post) {
                if ($post['id'] == $updated_post['post_id']) {
                    $_SESSION['posts'][$index] = [
                        'id' => $updated_post['post_id'],
                        'content' => $updated_post['content'],
                        'created_at' => $updated_post['created_at'],
                        'username' => $updated_post['username']
                    ];
                    break;
                }
            }
        }
    }
    
    $result= isset($_SESSION['posts']) ? $_SESSION['posts'] : [];
} catch (Exception $e) {
    header('Location: error.php?error=' . urlencode($e->getMessage()));
    exit();
} finally {
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Posts</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="container posts">
    <h1>Posts</h1>

    <form action="logout.php" method="POST" class="logout-form">
        <button type="submit">Logout</button>
    </form>

    <?php if (isset($_GET['message'])): ?>
        <?php if ($_GET['message'] == 'post_added'): ?>
            <p class="success">Пост успешно добавлен!</p>
        <?php elseif ($_GET['message'] == 'post_deleted'): ?>
            <p class="success">Пост успешно удален!</p>
        <?php endif; ?>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <p class="error"><?php echo htmlspecialchars($_GET['error']); ?></p>
    <?php endif; ?>

    <form action="posts.php" method="POST">
        <textarea name="content" rows="4" placeholder="Расскажите свои мысли" required></textarea>
        <button type="submit">Add Post</button>
    </form>

    <form action="posts.php" method="GET" class="search-form">
        <div class="search-container">
            <input type="text" name="search" placeholder="Search posts..." value="<?php echo htmlspecialchars($search); ?>">
            <?php if (!empty($search)): ?>
                <button class="search-button" type="submit" name="search" value="">×</button>
            <?php endif; ?>
        </div>
        <button type="submit">Search</button>
    </form>

    <table border="1">
        <tr>
            <th>ID</th>
            <th>Content</th>
            <th>Created At</th>
            <th>Author</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($result as $row): ?>
            <tr>
            <td><?php echo htmlspecialchars($row['id']); ?></td>
            <td><?php echo htmlspecialchars($row['content']); ?></td>
            <td><?php echo htmlspecialchars($row['created_at']); ?></td>
            <td><?php echo htmlspecialchars($row['username']); ?></td>
            <td>
                <a href="posts.php?delete=<?php echo htmlspecialchars($row['id']); ?>" onclick="return confirm('Вы уверены, что хотите удалить этот пост?');">Delete</a>
                <a href="posts.php?update=<?php echo htmlspecialchars($row['id']); ?>">Update</a>
            </td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>
</body>
</html>


