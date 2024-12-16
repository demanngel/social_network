<div class="container post-history">
    <h1>Post History</h1>
    <div class="history-container">
        <?php while ($history = $post_history->fetch_assoc()): ?>
            <div class="history-entry">
                <p><strong>Action:</strong> <?php echo htmlspecialchars($history['action']); ?></p>
                <p><strong><?php echo htmlspecialchars($history['role'] == 'moderator' ? 'Moderator' : 'User'); ?>:</strong> <?php echo htmlspecialchars($history['moderator']); ?></p>
                <p><strong>Comment:</strong> <?php echo htmlspecialchars($history['comment'] ?? ''); ?></p>
                <p><strong>Date:</strong> <?php echo htmlspecialchars($history['action_time']); ?></p>
            </div>
        <?php endwhile; ?>
    </div>
</div>
