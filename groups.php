<?php
include './db.php';
include 'header.php';

try {
    if ($conn->connect_error) {
        throw new Exception("Ошибка подключения к базе данных: " . $conn->connect_error);
    }
    $user_id = $_SESSION['user_id'];

    if (!isset($_SESSION['user_id'])) {
        header('Location: loginForm.php');
        exit();
    }

    $user_role = $_SESSION['user_role'] ?? 'user';

    $search_term = $_GET['search'] ?? '';
    $search = '%' . $conn->real_escape_string($search_term) . '%';

    if ($user_role == 'user') {
        $sql = "SELECT g.id, g.name, g.description, g.created_by, u.username AS creator, 
                   IF(gm.user_id IS NOT NULL, 1, 0) AS is_member
            FROM `groups` AS g
            JOIN users AS u ON g.created_by = u.id
            LEFT JOIN group_members AS gm ON g.id = gm.group_id AND gm.user_id = ?
            WHERE g.name LIKE ? OR g.description LIKE ?
            ORDER BY is_member DESC, g.name ";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param('iss', $user_id, $search, $search);
            $stmt->execute();
            $user_groups = $stmt->get_result();
            $stmt->close();
        } else {
            throw new Exception("Ошибка при получении групп: " . $conn->error);
        }

        if (isset($_POST['join_group'])) {
            $group_id = intval($_POST['group_id']);
            $sql = "INSERT INTO group_members (group_id, user_id) VALUES (?, ?)";
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param('ii', $group_id, $user_id);
                $stmt->execute();
                $stmt->close();
                header('Location: groups.php');
                exit();
            } else {
                throw new Exception("Ошибка при добавлении пользователя в группу: " . $conn->error);
            }
        }

        if (isset($_POST['leave_group'])) {
            $group_id = intval($_POST['group_id']);
            $sql = "DELETE FROM group_members WHERE group_id = ? AND user_id = ?";
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param('ii', $group_id, $user_id);
                $stmt->execute();
                $stmt->close();
                header('Location: groups.php');
                exit();
            } else {
                throw new Exception("Ошибка при выходе из группы: " . $conn->error);
            }
        }
    }

    if ($user_role == 'moderator') {
        $sql = "SELECT g.id, g.name, g.description 
                FROM `groups` AS g 
                WHERE g.created_by = ?";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
            $moderator_groups = $stmt->get_result();
            $stmt->close();
        } else {
            throw new Exception("Ошибка при получении групп модератора: " . $conn->error);
        }

        if (isset($_POST['create_group'])) {
            $group_name = $_POST['group_name'];
            $group_description = $_POST['group_description'];
            $sql = "INSERT INTO `groups` (name, description, created_by) VALUES (?, ?, ?)";
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param('ssi', $group_name, $group_description, $user_id);
                $stmt->execute();
                $stmt->close();
                header('Location: groups.php');
                exit();
            } else {
                throw new Exception("Ошибка при создании группы: " . $conn->error);
            }
        }

        if (isset($_POST['delete_group'])) {
            $group_id = intval($_POST['group_id']);
            $sql = "DELETE FROM `groups` WHERE id = ? AND created_by = ?";

            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param('ii', $group_id, $user_id);
                $stmt->execute();
                $stmt->close();
                header('Location: groups.php');
                exit();
            } else {
                throw new Exception("Ошибка при удалении группы: " . $conn->error);
            }
        }
    }

} catch (Exception $e) {
    header('Location: error.php?message=' . urlencode($e->getMessage()));
    exit();
}
?>

<div class="container groups">
    <h1>Groups</h1>

    <?php if ($user_role == 'moderator'): ?>
        <form action="groups.php" method="POST">
            <input type="text" name="group_name" placeholder="Group Name" required>
            <textarea name="group_description" placeholder="Group Description" required></textarea>
            <button type="submit" name="create_group">Create Group</button>
        </form>
    <?php endif; ?>

    <form action="groups.php" method="GET">
        <div class="search-container">
            <input type="text" name="search" placeholder="Search for groups" value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
            <?php if (!empty($search_term)): ?>
                <button class="search-button" type="submit" name="search" value="">×</button>
            <?php endif; ?>
            <button type="submit">Search</button>
        </div>
    </form>

    <?php if ($user_role == 'user'): ?>
        <?php while ($group = $user_groups->fetch_assoc()): ?>
                <div class="group">
                    <div class="group_info">
                        <h2>
                            <a href="group.php?id=<?php echo htmlspecialchars($group['id']); ?>">
                                <?php echo htmlspecialchars($group['name']); ?>
                            </a>
                        </h2>

                        <p>Description: <?php echo htmlspecialchars($group['description']); ?></p>
                        <p>Created by: <?php echo htmlspecialchars($group['creator']); ?></p>
                    </div>

                    <div class="group_actions">
                        <?php if ($group['is_member'] == 0): ?>
                            <form action="groups.php" method="POST" style="display:inline;">
                                <input type="hidden" name="group_id" value="<?php echo $group['id']; ?>">
                                <button type="submit" name="join_group">Join Group</button>
                            </form>
                        <?php else: ?>
                            <form action="groups.php" method="POST" style="display:inline;">
                                <input type="hidden" name="group_id" value="<?php echo $group['id']; ?>">
                                <button type="submit" name="leave_group">Leave Group</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
        <?php endwhile; ?>
    <?php endif; ?>

    <?php if ($user_role == 'moderator'): ?>
        <?php while ($group = $moderator_groups->fetch_assoc()): ?>
            <div class="group">
                <div class="group_info">
                    <h2>
                        <a href="group.php?id=<?php echo htmlspecialchars($group['id']); ?>">
                            <?php echo htmlspecialchars($group['name']); ?>
                        </a>
                    </h2>
                    <p>Description: <?php echo htmlspecialchars($group['description']); ?></p>
                </div>
                <div class="actions">
                    <form action="edit_group.php" method="GET" style="display:inline;">
                        <input type="hidden" name="group_id" value="<?php echo $group['id']; ?>">
                        <button type="submit" class="action-button">✎</button>
                    </form>
                    <form action="groups.php" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this group?');">
                        <input type="hidden" name="group_id" value="<?php echo $group['id']; ?>">
                        <button type="submit" name="delete_group" class="action-button">🗑</button>
                    </form>
                </div>
            </div>
        <?php endwhile; ?>
    <?php endif; ?>
</div>