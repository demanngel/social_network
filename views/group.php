<div class="container groups">
    <div class="back-button action-button">
        <a href="groups.php?">🠔</a>
    </div>
    <h1><?php echo htmlspecialchars($group['name']); ?></h1>
    <div class="group">
        <div class="group_info">
            <p>Description: <?php echo htmlspecialchars($group['description']); ?></p>
            <p>Created by: <?php echo htmlspecialchars($group['creator']); ?></p>
            <p>Subscribers: <a href="subscribers.php?group_id=<?php echo $group_id; ?>"><?php echo $group['subscriber_count']; ?></a></p>
        </div>
        <div class="actions group-actions">
            <?php if ($user_role == 'moderator'): ?>
                <form action="edit_group.php" method="GET" >
                    <input type="hidden" name="group_id" value="<?php echo $group_id; ?>">
                    <button type="submit" class="action-button">✎</button>
                </form>
                <form action="group.php?id=<?php echo $group_id; ?>" method="POST" onsubmit="return confirm('Are you sure you want to delete this group?');">
                    <input type="hidden" name="delete_group" value="true">
                    <button type="submit" class="action-button">🗑</button>
                </form>
            <?php endif; ?>
            <?php if ($user_role == 'user'): ?>
                <div class="group-actions">
                    <form action="group.php?id=<?php echo $group_id; ?>" method="POST">
                        <?php if ($is_member): ?>
                            <button type="submit" name="leave_group">Leave Group</button>
                        <?php else: ?>
                            <button type="submit" name="join_group">Join Group</button>
                        <?php endif; ?>
                    </form>
                </div>
            <?php endif; ?>
            <?php if ($user_role == 'moderator' || $is_member): ?>
                <form action="suggested_posts.php" method="GET">
                    <input type="hidden" name="group_id" value="<?php echo $group_id; ?>">
                    <button type="submit" class="action-button action-button1">Suggested posts</button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($is_member): ?>
        <form action="group.php?id=<?php echo $group_id; ?>" method="POST" enctype="multipart/form-data" class="add_post_container">
            <label for="topic">Topic:</label>
            <select name="topic_id" id="topic" class="topic">
                <?php
                $topics_sql = "SELECT id, name FROM topics";
                $topics_result = $conn->query($topics_sql);
                while ($topic = $topics_result->fetch_assoc()):
                    ?>
                    <option value="<?php echo $topic['id']; ?>"><?php echo htmlspecialchars($topic['name']); ?></option>
                <?php endwhile; ?>
            </select>
            <textarea name="post_content" class="post_content" required></textarea>
            <input type="file" name="post_image" id="post_image" accept="image/*"">
            <button type="submit" name="add_post">Add post</button>
        </form>

    <?php endif; ?>
    <?php if ($is_member || $user_role == "moderator"): ?>
        <form action="group.php" method="GET">
            <div class="search-container">
                <input type="hidden" name="id" value="<?php echo $group_id; ?>">
                <input type="text" name="search" placeholder="Search for posts" value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                <?php if (!empty($search_term)): ?>
                    <button class="search-button" type="submit" name="search" value="">×</button>
                <?php endif; ?>
                <button type="submit">Search</button>
            </div>
        </form>
        <div class="posts-container">
            <?php while ($post = $posts_result->fetch_assoc()): ?>
                <div class="post">
                    <div class="post-info">
                        <div class="post-content">
                            <p><?php echo htmlspecialchars($post['content']); ?></p>
                        </div>
                        <?php if ($post['image_id']!=null): ?>
                            <img src="display_image.php?id=<?php echo htmlspecialchars($post['image_id']);?>" />
                        <?php endif; ?>
                        <div class="post-date">
                            <p><?php echo htmlspecialchars($post['created_at']); ?></p>
                        </div>
                    </div>
                    <?php if ($user_role == 'moderator'): ?>
                        <div class="actions">
                            <form action="edit_post.php?id=<?php echo $post['id'];?>" method="GET">
                                <input type="hidden" name="id" value="<?php echo $post['id']; ?>">
                                <input type="hidden" name="group_id" value="<?php echo $group_id; ?>">
                                <button type="submit" class="action-button">✎</button>
                            </form>
                            <form action="group.php?id=<?php echo $group_id; ?>" method="POST">
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