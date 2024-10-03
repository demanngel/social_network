<?php
include './db.php';
include 'header.php';

try {
    $user_role = $_SESSION['user_role'] ?? 'user';

    if ($user_role !== 'admin') {
        header('Location: loginForm.php');
        exit();
    }

    $search_term = $_GET['search'] ?? '';

    $sql = "SELECT id, username, role FROM users WHERE username LIKE ? ORDER BY role";
    if ($stmt = $conn->prepare($sql)) {
        $search_param = '%' . $search_term . '%';
        $stmt->bind_param('s', $search_param);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
    } else {
        throw new Exception("Ошибка при подготовке запроса: " . $conn->error);
    }

    $admin_count_sql = "SELECT COUNT(*) FROM users WHERE role = 'admin'";
    if ($count_stmt = $conn->prepare($admin_count_sql)) {
        $count_stmt->execute();
        $count_stmt->bind_result($admin_count);
        $count_stmt->fetch();
        $count_stmt->close();
    } else {
        throw new Exception("Ошибка при подсчете администраторов: " . $conn->error);
    }

    if (isset($_POST['delete_user'])) {
        $user_id = intval($_POST['user_id']);
        $user_role = $_POST['user_role'];

        $delete_user_sql = "DELETE FROM users WHERE id = ?";
        if ($delete_stmt = $conn->prepare($delete_user_sql)) {
            $delete_stmt->bind_param('i', $user_id);
            $delete_stmt->execute();
            $delete_stmt->close();
            header('Location: users.php');
            exit();
        } else {
            throw new Exception("Ошибка при удалении пользователя: " . $conn->error);
        }
    }
} catch (Exception $e) {
    header('Location: error.php?error=' . urlencode($e->getMessage()));
    exit();
}
?>

<div class="container">
    <h2>User Management</h2>

    <form action="users.php" method="GET">
        <div class="search-container">
            <input type="text" name="search" placeholder="Search for users" value="<?php echo htmlspecialchars($search_term); ?>">
            <button type="submit">Search</button>
        </div>
    </form>

    <div class="users">
        <?php while ($user = $result->fetch_assoc()): ?>
            <div class="user">
                <span><?php echo htmlspecialchars($user['username']); ?> (<?php echo htmlspecialchars($user['role']); ?>)</span>
                <div class="actions">
                    <form action="edit_user.php" method="GET" style="display:inline;">
                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                        <button type="submit" class="action-button">✎</button>
                    </form>
                    <form action="users.php" method="POST" style="display:inline;">
                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                        <input type="hidden" name="user_role" value="<?php echo htmlspecialchars($user['role']); ?>">
                        <button type="submit" name="delete_user" class="action-button"
                            <?php if ($user['role'] === 'admin' && $admin_count <= 1) echo 'disabled'; ?>>
                            🗑
                        </button>
                    </form>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>
