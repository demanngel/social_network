<?php
include './db.php';
include 'header.php';

try {
    if (!isset($_SESSION['user_id'])) {
        header('Location: loginForm.php');
        exit();
    }

    $post_id = intval($_GET['id']);

    $sql = "SELECT ph.action, ph.comment, ph.action_time, u.username AS moderator, u.role
            FROM post_history AS ph
            LEFT JOIN users AS u ON ph.moderator_id = u.id
            WHERE ph.post_id = ?
            ORDER BY ph.action_time DESC";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param('i', $post_id);
        $stmt->execute();
        $history_result = $stmt->get_result();
        $stmt->close();
    } else {
        throw new Exception("Ошибка при получении истории поста: " . $conn->error);
    }
} catch (Exception $e) {
    header('Location: error.php?message=' . urlencode($e->getMessage()));
    exit();
}
?>

<div class="container post-history">
    <h1>Post History</h1>
    <div class="history-container">
        <?php while ($history = $history_result->fetch_assoc()): ?>
            <div class="history-entry">
                <p><strong>Action:</strong> <?php echo htmlspecialchars($history['action']); ?></p>
                <p><strong><?php echo htmlspecialchars($history['role'] == 'moderator' ? 'Moderator' : 'User'); ?>:</strong> <?php echo htmlspecialchars($history['moderator']); ?></p>
                <p><strong>Comment:</strong> <?php echo htmlspecialchars($history['comment'] ?? ''); ?></p>
                <p><strong>Date:</strong> <?php echo htmlspecialchars($history['action_time']); ?></p>
            </div>
        <?php endwhile; ?>
    </div>
</div>
