<div class="container suggested-posts">
    <div class="back-button action-button">
        <a href="index.php?action=group&id=<?php echo $group_id; ?>">🠔</a>
    </div>
    <h1>Suggested posts</h1>
    <?php if($user_role == "moderator"): ?>
        <div class="posts-container">
            <?php while ($post = $posts->fetch_assoc()): ?>
                <div class="suggested-post">
                    <div class="post-container">
                        <div class="post">
                            <div class="post-info">
                                <div class="post-author">
                                    <p><strong><?php echo htmlspecialchars($post['author']); ?></strong></p>
                                </div>
                                <div class="post-content">
                                    <p><?php echo htmlspecialchars($post['content']); ?></p>
                                </div>
                                <div class="post-date">
                                    <p><?php echo htmlspecialchars($post['created_at']); ?></p>
                                </div>
                                <div class="post-status">
                                    <p>Status: <?php echo htmlspecialchars($post['status']); ?></p>
                                </div>
                                <div class="post-status">
                                    <p>User rating: <?php echo htmlspecialchars($post['rating']); ?></p>
                                </div>
                                <?php if ($post['image_id']!=null): ?>
                                    <img src="index.php?action=display_image&id=<?php echo htmlspecialchars($post['image_id']);?>" />
                                <?php endif; ?>
                                <div class="post-status">
                                    <p>Weight: <?php echo htmlspecialchars($post['final_weight']); ?></p>
                                </div>
                                <div class="post-status">
                                    <p>Topic: <?php echo htmlspecialchars($post['topic']??''); ?></p>
                                </div>
                            <?php if($post['status'] == 'on_moderation'): ?>
                                    <form method="POST">
                                        <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                        <input type="hidden" name="group_id" value="<?php echo $group_id; ?>">
                                        <input type="hidden" name="user_id" value="<?php echo $post['user_id']; ?>">
                                        <input type="hidden" name="approved_count" value="<?php echo $post['approved_count']; ?>">
                                        <input type="hidden" name="rejected_count" value="<?php echo $post['rejected_count']; ?>">
                                        <input type="hidden" name="on_moderation_count" value="<?php echo $post['on_moderation_count']; ?>">
                                        <input type="hidden" name="image_id" value="<?php echo $post['image_id']; ?>">

                                        <input type="hidden" name="action" value="">

                                        <textarea name="comment" placeholder="Comment"></textarea>

                                        <div class="suggested-post-actions">
                                            <button type="submit" name="approve_post" class="action-button action-button1" onclick="this.form.action.value='approve';">Approve</button>
                                            <button type="submit" name="revision_post" class="action-button action-button1" onclick="this.form.action.value='revision';">For revision</button>
                                            <button type="submit" name="reject_post" class="action-button action-button1" onclick="this.form.action.value='reject';" onsubmit="return confirm('Вы уверены, что хотите отклонить пост?');">Reject</button>
                                        </div>
                                    </form>
                            <?php endif?>
                            </div>
                            <div class="history">
                                <form action="post_history.php?post_id=<?php echo $post['id']; ?>" method="GET" class="history">
                                    <input type="hidden" name="id" value="<?php echo $post['id']; ?>">
                                    <input type="hidden" name="group_id" value="<?php echo $group_id; ?>">
                                    <button type="submit" class="action-button"><i class="fas fa-clock"></i></button>
                                </form>
                            </div>

                        </div>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>
    <?php if($user_role == "user"): ?>
        <div class="posts-container">
            <?php while ($post = $posts->fetch_assoc()): ?>
                <div class="suggested-post">
                    <div class="post-info">
                        <div class="post-content">
                            <p><?php echo htmlspecialchars($post['content']); ?></p>
                        </div>
                        <div class="post-date">
                            <p><?php echo htmlspecialchars($post['created_at']); ?></p>
                        </div>
                        <div class="post-status">
                            <p>Status: <?php echo htmlspecialchars($post['status']); ?></p>
                        </div>
                        <div class="post-image">
                            <?php if ($post['image_id']!=null): ?>
                                <img src="index.php?action=display_image&id=<?php echo htmlspecialchars($post['image_id']);?>" />
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($post['comment'])): ?>
                            <div class="post-comment">
                                <p><strong>Comment:</strong> <?php echo htmlspecialchars($post['comment']); ?></p>
                            </div>
                        <?php endif; ?>

                        <?php if ($post['status'] == 'on_revision'): ?>
                            <form method="POST">
                                <textarea name="content"><?php echo htmlspecialchars($post['content']); ?></textarea>
                                <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                <input type="hidden" name="group_id" value="<?php echo $group_id; ?>">
                                <button name="send_to_moderation" type="submit">Send for moderation</button>
                            </form>
                        <?php endif; ?>
                    </div>

                    <div class="history">
                        <form action="index.php" method="GET" class="history">
                            <input type="hidden" name="action" value="post_history">
                            <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                            <input type="hidden" name="group_id" value="<?php echo $group_id; ?>">
                            <button type="submit" class="action-button"><i class="fas fa-clock"></i></button>
                        </form>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>
</div>