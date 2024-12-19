<div class="container groups">
    <h1>Groups</h1>

    <?php if ($user_role == 'moderator'): ?>
        <form method="POST">
            <input type="text" name="group_name" placeholder="Group Name" required>
            <textarea name="group_description" placeholder="Group Description" required></textarea>
            <button type="submit" name="create_group">Create Group</button>
        </form>
    <?php endif; ?>

    <form method="GET">
        <div class="search-container">
            <input type="hidden" name="action" value="groups">
            <input type="text" name="search" placeholder="Search for groups" value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
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
                            <input type="hidden" name="group_id" value="<?php echo $group['id']; ?>">
                            <button type="submit" name="join_group">Join Group</button>
                        </form>
                    <?php else: ?>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="group_id" value="<?php echo $group['id']; ?>">
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
                    <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this group?');">
                        <input type="hidden" name="group_id" value="<?php echo $group['id']; ?>">
                        <button type="submit" name="delete_group" class="action-button">🗑</button>
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
            <input type="hidden" name="edit_group" value="true">
            <input type="hidden" name="group_id" id="edit_group_id">
            <input type="text" name="group_name" id="edit_group_name" required>
            <textarea name="group_description" id="edit_group_description" required></textarea>
            <button type="submit">Save Changes</button>
        </form>
    </div>
</div>

<script src="js/modals.js"></script>