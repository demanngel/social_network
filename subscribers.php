<?php
include './db.php';
include 'header.php';

try {
    $user_id = $_SESSION['user_id'];
    if (!isset($_SESSION['user_id'])) {
        header('Location: loginForm.php');
        exit();
    }

    if (isset($_SESSION['user_id'])) {
        $user_id = intval($_SESSION['user_id']);

        $sql = "SELECT role FROM users WHERE id = ?";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
            $stmt->bind_result($user_role);
            $stmt->fetch();
            $stmt->close();

            if (empty($user_role)) {
                $user_role = 'user';
            }
        } else {
            die("Ошибка запроса: " . $conn->error);
        }
    } else {
        $user_role = 'guest';

        if ($user_role !== 'admin') {
            header('Location: loginForm.php');
            exit();
        }
    }

    $group_id = intval($_GET['group_id']);
    $search_term = $_GET['search'] ?? '';

    $group_sql = "SELECT name FROM `groups` WHERE id = ?";
    if ($group_stmt = $conn->prepare($group_sql)) {
        $group_stmt->bind_param('i', $group_id);
        $group_stmt->execute();
        $group_stmt->bind_result($group_name);
        $group_stmt->fetch();
        $group_stmt->close();
    } else {
        throw new Exception("Ошибка при получении информации о группе: " . $conn->error);
    }

    if (!$group_name) {
        throw new Exception("Группа не найдена.");
    }

    $sql = "SELECT u.id, u.username FROM group_members gm
            JOIN users u ON gm.user_id = u.id
            WHERE gm.group_id = ? AND u.username LIKE ?";

    if ($stmt = $conn->prepare($sql)) {
        $search_param = '%' . $search_term . '%';
        $stmt->bind_param('is', $group_id, $search_param);
        $stmt->execute();
        $subscribers = $stmt->get_result();
        $stmt->close();
    } else {
        throw new Exception("Ошибка при получении подписчиков: " . $conn->error);
    }

    if (isset($_POST['remove_user'])) {
        $user_id_to_remove = intval($_POST['user_id']);
        $sql = "DELETE FROM group_members WHERE group_id = ? AND user_id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param('ii', $group_id, $user_id_to_remove);
            $stmt->execute();
            $stmt->close();
            header('Location: subscribers.php?group_id=' . $group_id);
            exit();
        } else {
            throw new Exception("Ошибка при удалении пользователя из группы: " . $conn->error);
        }
    }

} catch (Exception $e) {
    header('Location: error.php?error=' . urlencode($e->getMessage()));
    exit();
}
?>

<div class="container">
    <div class="back-button action-button">
        <a href="group.php?id=<?php echo $group_id; ?>">🠔</a>
    </div>

    <h2>Subscribers of <?php echo htmlspecialchars($group_name); ?></h2>

    <form action="subscribers.php" method="GET">
        <div class="search-container">
            <input type="hidden" name="group_id" value="<?php echo $group_id; ?>">
            <input type="text" name="search" placeholder="Search for posts" value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
            <?php if (!empty($search_term)): ?>
                <button class="search-button" type="submit" name="search" value="">×</button>
            <?php endif; ?>
            <button type="submit">Search</button>
        </div>
    </form>
    <div class="subscribers">
        <?php while ($subscriber = $subscribers->fetch_assoc()): ?>
            <div class="subscriber">
                <?php echo htmlspecialchars($subscriber['username']); ?>
                <div class="actions">
                    <?php if ($user_role == 'moderator'): ?>
                        <form action="subscribers.php?group_id=<?php echo $group_id; ?>" method="POST" style="display:inline;">
                            <input type="hidden" name="user_id" value="<?php echo $subscriber['id']; ?>">
                            <button type="submit" name="remove_user" class="action-button">🗑</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>
