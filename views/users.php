<div class="container">
    <h2>User Management</h2>

    <form method="GET">
        <div class="search-container">
            <input type="hidden" name="action" value="users">
            <input type="text" name="search" placeholder="Search for users" value="<?php echo htmlspecialchars($search_term); ?>">
            <?php if (!empty($search_term)): ?>
                <button class="search-button" type="submit" name="search" value="">×</button>
            <?php endif; ?>
            <button type="submit">Search</button>
        </div>
    </form>

    <div class="users">
        <?php while ($user = $users->fetch_assoc()): ?>
            <div class="user">
                <p><?php echo htmlspecialchars($user['username']); ?> (<?php echo htmlspecialchars($user['role']); ?>)</p>
                <div class="actions">
                    <button type="button" onclick="openEditUserModal('<?php echo $user['id']; ?>', '<?php echo addslashes($user['username']); ?>', '<?php echo addslashes($user['email']); ?>')" class="action-button">✎</button>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                        <button type="submit" name="delete_user" class="action-button" onclick="return confirm('Are you sure you want to delete this user?')">🗑</button>
                    </form>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<div id="editUserModal" class="modal" style="display: none;">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Edit User</h2>
        <form method="POST" id="editUserForm">
            <input type="hidden" name="edit_user" value="true">
            <input type="hidden" name="user_id" id="edit_user_id">
            <input type="text" name="username" id="edit_username" required placeholder="Username">
            <input type="email" name="email" id="edit_email" required placeholder="Email">
            <button type="submit">Save Changes</button>
        </form>
    </div>
</div>

<script src="js/modals.js"></script>
