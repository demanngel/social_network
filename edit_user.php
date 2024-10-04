<?php
include './db.php';
include 'header.php';

try {
    $user_role = $_SESSION['user_role'] ?? 'user';

    if ($user_role !== 'admin') {
        header('Location: loginForm.php');
        exit();
    }

    $user_id = intval($_GET['user_id']);
    $existing_user = null;

    $sql = "SELECT username, role FROM users WHERE id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $existing_user = $result->fetch_assoc();
        $stmt->close();
    } else {
        throw new Exception("Ошибка при получении информации о пользователе: " . $conn->error);
    }

    $admin_count_sql = "SELECT COUNT(*) FROM users WHERE role = 'admin' AND id != ?";
    if ($count_stmt = $conn->prepare($admin_count_sql)) {
        $count_stmt->bind_param('i', $user_id);
        $count_stmt->execute();
        $count_stmt->bind_result($admin_count);
        $count_stmt->fetch();
        $count_stmt->close();
    } else {
        throw new Exception("Ошибка при подсчете администраторов: " . $conn->error);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $new_role = $_POST['role'];

        if ($existing_user['role'] === 'admin' && $admin_count === 0) {
            $new_role = 'admin';
        }

        if ($existing_user['role'] === 'user') {
            $delete_posts_sql = "DELETE FROM group_suggested_posts WHERE user_id = ?";
            if ($delete_stmt = $conn->prepare($delete_posts_sql)) {
                $delete_stmt->bind_param('i', $user_id);
                $delete_stmt->execute();
                $delete_stmt->close();
            }

            $delete_subscriptions_sql = "DELETE FROM group_members WHERE user_id = ?";
            if ($delete_stmt = $conn->prepare($delete_subscriptions_sql)) {
                $delete_stmt->bind_param('i', $user_id);
                $delete_stmt->execute();
                $delete_stmt->close();
            }
        }

        if ($existing_user['role'] === 'moderator') {
            $delete_posts_sql = "DELETE FROM `groups` WHERE created_by = ?";
            if ($delete_stmt = $conn->prepare($delete_posts_sql)) {
                $delete_stmt->bind_param('i', $user_id);
                $delete_stmt->execute();
                $delete_stmt->close();
            }
        }

        $update_sql = "UPDATE users SET role = ? WHERE id = ?";
        if ($update_stmt = $conn->prepare($update_sql)) {
            $update_stmt->bind_param('si', $new_username, $new_role, $user_id);
            $update_stmt->execute();
            $update_stmt->close();
            header('Location: users.php');
            exit();
        } else {
            throw new Exception("Ошибка при обновлении пользователя: " . $conn->error);
        }
    }

} catch (Exception $e) {
    header('Location: error.php?error=' . urlencode($e->getMessage()));
    exit();
}
?>

<div class="container edit-user">
    <div class="back-button action-button">
        <a href="users.php?>">>🠔</a>
    </div>
    <h2>Edit User</h2>
    <form action="edit_user.php?user_id=<?php echo $user_id; ?>" method="POST">
        <p>Username: <?php echo htmlspecialchars($existing_user['username']); ?></p>

        <div class="radio-group">
            <label for="role" class="form-label">Role:</label>
            <label class="radio-label">
                <input type="radio" name="role" value="user" id="role_user"
                    <?php if ($existing_user['role'] === 'user') echo 'checked'; ?>
                    <?php if ($admin_count === 0) echo 'disabled'; ?>> User
            </label>
            <label class="radio-label">
                <input type="radio" name="role" value="moderator" id="role_moderator"
                    <?php if ($existing_user['role'] === 'moderator') echo 'checked'; ?>
                    <?php if ($admin_count === 0) echo 'disabled'; ?>> Moderator
            </label>
            <label class="radio-label">
                <input type="radio" name="role" value="admin" id="role_admin"
                    <?php if ($existing_user['role'] === 'admin') echo 'checked'; ?>
                    <?php if ($admin_count === 0) echo 'disabled'; ?>> Admin
            </label>
        </div>

        <button type="submit">Save changes</button>
    </form>
</div>
