<?php
include './db.php';
include 'header.php';

try {
    $conn = db_connect();

    $group_id = intval($_GET['group_id']);
    $user_id = $_SESSION['user_id'];
    $user_role = $_SESSION['user_role'];

    if (!isset($user_id) || $user_role !== 'moderator') {
        throw new Exception("У вас нет прав доступа к этой странице.");
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $group_name = $_POST['group_name'];
        $group_description = $_POST['group_description'];

        $sql = "UPDATE `groups` SET name = ?, description = ? WHERE id = ?";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param('ssi', $group_name, $group_description, $group_id);
            $stmt->execute();
            $stmt->close();
            header('Location: groups.php');
            exit();
        } else {
            throw new Exception("Ошибка при обновлении группы: " . $conn->error);
        }
    } else {
        $sql = "SELECT name, description FROM `groups` WHERE id = ?";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param('i', $group_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows === 0) {
                throw new Exception("Группа не найдена.");
            }
            $group = $result->fetch_assoc();
            $stmt->close();
        } else {
            throw new Exception("Ошибка при получении информации о группе: " . $conn->error);
        }
    }
    db_close($conn);
} catch (Exception $e) {
    header('Location: error.php?message=' . urlencode($e->getMessage()));
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Group</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="container">
    <div class="back-button action-button">
        <a href="groups.php?>">🠔</a>
    </div>
    <h1>Edit Group</h1>
    <form action="edit_group.php?group_id=<?php echo $group_id; ?>" method="POST">
        <input type="text" name="group_name" value="<?php echo htmlspecialchars($group['name']); ?>" required>
        <textarea name="group_description" required><?php echo htmlspecialchars($group['description']); ?></textarea>
        <button type="submit">Save Changes</button>
    </form>
</div>
</body>
</html>
