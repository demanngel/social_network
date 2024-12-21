<?php
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Content-Security-Policy: default-src \'self\'; script-src \'self\' \'unsafe-inline\'; style-src \'self\' \'unsafe-inline\';');
header('Strict-Transport-Security: max-age=31536000; includeSubDomains');

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die('CSRF token validation failed');
    }
}
?>

<div class="container groups">
    <h1>Groups</h1>

    <?php if ($user_role == 'moderator'): ?>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <input type="text" name="group_name" placeholder="Group Name" required
                   pattern="[A-Za-z0-9\s]{3,50}"
                   title="Group name must be 3-50 characters long and contain only letters, numbers and spaces">
            <textarea name="group_description" placeholder="Group Description" required
                      maxlength="500"></textarea>
            <button type="submit" name="create_group">Create Group</button>
        </form>
    <?php endif; ?>

    <form method="GET">
        <div class="search-container">
            <input type="hidden" name="action" value="groups">
            <input type="text" name="search" placeholder="Search for groups"
                   value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>"
                   maxlength="100">
            <?php if (!empty($search_term)): ?>
                <button class="search-button" type="submit" name="search" value="">×</button>
            <?php endif; ?>
            <button type="submit">Search</button>
        </div>
    </form>

<?php if ($user_role == 'user'): ?>
    <?php while ($group = $groups->fetch_assoc()): ?>
        <div class="group">
            <div class="group_info">
                <h2>
                    <a href="index.php?action=group&id=<?php echo htmlspecialchars($group['id']); ?>">
                        <?php echo htmlspecialchars($group['name']); ?>
                    </a>
                </h2>

                <p>Description: <?php echo htmlspecialchars($group['description']); ?></p>
                <p>Created by: <?php echo htmlspecialchars($group['creator']); ?></p>
            </div>

                <div class="group_actions">
                    <?php if ($group['is_member'] == 0): ?>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                            <input type="hidden" name="group_id" value="<?php echo (int)$group['id']; ?>">
                            <button type="submit" name="join_group">Join Group</button>
                        </form>
                    <?php else: ?>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                            <input type="hidden" name="group_id" value="<?php echo (int)$group['id']; ?>">
                            <button type="submit" name="leave_group">Leave Group</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        <?php endwhile; ?>
    <?php endif; ?>

    <?php if ($user_role == 'moderator'): ?>
        <?php while ($group = $groups->fetch_assoc()): ?>
            <div class="group">
                <div class="group_info">
                    <h2>
                        <a href="index.php?action=group&id=<?php echo htmlspecialchars($group['id']); ?>">
                            <?php echo htmlspecialchars($group['name']); ?>
                        </a>
                    </h2>
                    <p>Description: <?php echo htmlspecialchars($group['description']); ?></p>
                </div>
                <div class="actions">
                    <button type="button" onclick="openEditModal(<?php echo $group['id']; ?>, '<?php echo htmlspecialchars($group['name']); ?>', '<?php echo htmlspecialchars($group['description']); ?>')" class="action-button">✎</button>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <input type="hidden" name="group_id" value="<?php echo (int)$group['id']; ?>">
                        <button type="submit" name="delete_group" class="action-button"
                                onclick="return confirm('Are you sure you want to delete this group?')">🗑</button>
                    </form>
                </div>
            </div>
        <?php endwhile; ?>
    <?php endif; ?>
</div>

<div id="editModal" class="modal" style="display: none;">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Edit Group</h2>
        <form method="POST" id="editGroupForm">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <input type="hidden" name="edit_group" value="true">
            <input type="hidden" name="group_id" id="edit_group_id">
            <input type="text" name="group_name" id="edit_group_name" required
                   pattern="[A-Za-z0-9\s]{3,50}">
            <textarea name="group_description" id="edit_group_description" required
                      maxlength="500"></textarea>
            <button type="submit">Save Changes</button>
        </form>
    </div>
</div>

<script src="js/modals.js"></script>