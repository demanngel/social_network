<div class="container">
    <h2>User Management</h2>

    <form method="GET">
        <div class="search-container">
            <input type="text" name="search" placeholder="Search for users" value="<?php echo htmlspecialchars($search_term); ?>">
            <button type="submit">Search</button>
        </div>
    </form>

    <div class="users">
        <?php while ($user = $users->fetch_assoc()): ?>
            <div class="user">
                <p><?php echo htmlspecialchars($user['username']); ?> (<?php echo htmlspecialchars($user['role']); ?>)</p>
                <div class="actions">
                    <form action="edit_user.php" method="GET" style="display:inline;">
                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                        <button type="submit" class="action-button">✎</button>
                    </form>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="action" value="users">
                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                        <button type="submit" name="delete_user" class="action-button">🗑</button>
                    </form>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>
