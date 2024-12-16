<div class="container">
    <div class="back-button action-button">
        <a href="index.php?action=group&id=<?php echo $group_id; ?>">🠔</a>
    </div>

    <h2>Subscribers of <?php echo htmlspecialchars($group_name); ?></h2>

    <form action="index.php" method="GET">
        <div class="search-container">
            <input type="hidden" name="group_id" value="<?php echo $group_id; ?>">
            <input type="hidden" name="action" value="subscribers">
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
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="group_id" value="<?php echo $group_id; ?>">
                            <input type="hidden" name="action" value="subscribers">
                            <input type="hidden" name="user_id" value="<?php echo $subscriber['id']; ?>">
                            <button type="submit" name="remove_user" class="action-button">🗑</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>
