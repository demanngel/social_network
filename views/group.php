<div class="container groups">
    <div class="back-button action-button">
        <a href="?action=groups">🠔</a>
    </div>
    <h1><?php echo htmlspecialchars($group['name']); ?></h1>
    <div class="group">
        <div class="group_info">
            <p>Description: <?php echo htmlspecialchars($group['description']); ?></p>
            <p>Created by: <?php echo htmlspecialchars($group['creator']); ?></p>
            <p>Subscribers: <a href="index.php?action=subscribers&group_id=<?php echo $group_id; ?>"><?php echo $group['subscriber_count']; ?></a></p>
        </div>
        <div class="actions group-actions">
            <?php if ($user_role == 'moderator'): ?>
                <button type="button" onclick="openEditModal(<?php echo $group_id; ?>, '<?php echo htmlspecialchars($group['name']); ?>', '<?php echo htmlspecialchars($group['description']); ?>')" class="action-button">✎</button>
                <form method="POST" onsubmit="return confirm('Are you sure you want to delete this group?');">
                    <input type="hidden" name="group_id" value="<?php echo $group_id; ?>">
                    <input type="hidden" name="delete_group" value="true">
                    <button type="submit" class="action-button">🗑</button>
                </form>
            <?php endif; ?>
            <?php if ($user_role == 'user'): ?>
                <div class="group-actions">
                    <form method="POST">
                        <input type="hidden" name="group_id" value="<?php echo $group_id; ?>">
                        <?php if ($is_member): ?>
                            <button type="submit" name="leave_group">Leave Group</button>
                        <?php else: ?>
                            <button type="submit" name="join_group">Join Group</button>
                        <?php endif; ?>
                    </form>
                </div>
            <?php endif; ?>
            <?php if ($user_role == 'moderator' || $is_member): ?>
                <form method="GET">
                    <input type="hidden" name="action" value="suggested_posts">
                    <input type="hidden" name="group_id" value="<?php echo $group_id; ?>">
                    <button type="submit" class="action-button action-button1">Suggested posts</button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($is_member): ?>
        <form method="POST" enctype="multipart/form-data" class="add_post_container">
            <input type="hidden" name="group_id" value="<?php echo $group_id; ?>">
            <textarea name="post_content" class="post_content" required></textarea>
            <input type="file" name="post_image" id="post_image" accept="image/*">
            <button type="submit" name="add_post">Add post</button>
        </form>
    <?php endif; ?>

    <?php if ($is_member || $user_role == "moderator"): ?>
        <form method="GET">
            <div class="search-container">
                <input type="hidden" name="action" value="group">
                <input type="hidden" name="id" value="<?php echo $group_id; ?>">
                <input type="text" name="search" placeholder="Search for posts" value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                <?php if (!empty($search_term)): ?>
                    <button class="search-button" type="submit" name="search" value="">×</button>
                <?php endif; ?>
                <button type="submit">Search</button>
            </div>
        </form>
        <div class="posts-container">
            <?php while ($post = $posts->fetch_assoc()): ?>
                <div class="post">
                    <div class="post-info">
                        <div class="post-content">
                            <p><?php echo htmlspecialchars($post['content']); ?></p>
                        </div>
                        <?php if ($post['image_id']!=null): ?>
                            <img src="index.php?action=display_image&id=<?php echo htmlspecialchars($post['image_id']);?>" />
                        <?php endif; ?>
                        <div class="post-date">
                            <p><?php echo htmlspecialchars($post['created_at']); ?></p>
                        </div>
                    </div>
                    <?php if ($user_role == 'moderator'): ?>
                        <div class="actions">
                            <button type="button" onclick="openEditPostModal(<?php echo $post['id']; ?>, '<?php echo htmlspecialchars($post['content']); ?>')" class="action-button">✎</button>
                            <form method="POST">
                                <input type="hidden" name="group_id" value="<?php echo $group_id; ?>">
                                <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                <button type="submit" name="delete_post" class="action-button">🗑</button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        </div>
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

<div id="editPostModal" class="modal" style="display: none;">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Edit Post</h2>
        <form method="POST" id="editPostForm">
            <input type="hidden" name="edit_post" value="true">
            <input type="hidden" name="post_id" id="edit_post_id">
            <textarea name="post_content" id="edit_post_content" required></textarea>
            <button type="submit">Save Changes</button>
        </form>
    </div>
</div>

<script src="js/modals.js"></script>