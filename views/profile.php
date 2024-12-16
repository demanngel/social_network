<div class="container profile-container">
    <h1>Profile</h1>
    <div class="profile-info-container">
        <div class="profile-image"">
        <?php if ($profile_info['image_id']): ?>
            <img src="index.php?action=display_image&id=<?php echo htmlspecialchars($profile_info['image_id']); ?>"">
        <?php endif; ?>
    </div>
    <div>
        <p>Username: <?php echo htmlspecialchars($profile_info['username']); ?></p>
        <p>Email: <?php echo htmlspecialchars($profile_info['email']); ?></p>
        <p>Rating: <?php echo htmlspecialchars($profile_info['rating']); ?></p>
    </div>
</div>

<form method="POST" enctype="multipart/form-data">
    <label for="profile_image">Upload Profile Image:</label>
    <input type="file" name="profile_image" accept="image/*" required>
    <button type="submit">Upload</button>
</form>

<?php if ($notification): ?>
    <p><?php echo htmlspecialchars($notification); ?></p>
<?php endif; ?>
</div>