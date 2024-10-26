<?php
include './db.php';
include 'header.php';

$notification = '';

try {
    if ($conn->connect_error) {
        throw new Exception("Database connection error: " . $conn->connect_error);
    }

    if (!isset($_SESSION['user_id'])) {
        header('Location: loginForm.php');
        exit();
    }

    $user_id = $_SESSION['user_id'];

    $sql = "SELECT username, email, profile_image_path, rating FROM users WHERE id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $stmt->bind_result($username, $email, $profile_image_path, $rating);
        $stmt->fetch();
        $stmt->close();
    } else {
        throw new Exception("Error fetching user information: " . $conn->error);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_image'])) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file = $_FILES['profile_image'];

        if ($file['error'] === UPLOAD_ERR_NO_FILE) {
            $notification = "No file was uploaded.";
        } elseif (!in_array($file['type'], $allowed_types)) {
            $notification = "Only JPG, PNG, and GIF files are allowed.";
        } elseif ($file['error'] !== UPLOAD_ERR_OK) {
            $notification = "An error occurred while uploading the file.";
        } else {
            $target_dir = "./uploads/";

            if (!is_dir($target_dir)) {
                if (!mkdir($target_dir, 0755, true)) {
                    throw new Exception("Error creating upload directory.");
                }
            }

            if (!is_writable($target_dir)) {
                throw new Exception("Upload directory is not writable.");
            }

            $target_file = $target_dir . basename($file['name']);

            if (move_uploaded_file($file['tmp_name'], $target_file)) {
                $update_sql = "UPDATE users SET profile_image_path = ? WHERE id = ?";
                if ($update_stmt = $conn->prepare($update_sql)) {
                    $update_stmt->bind_param('si', $target_file, $user_id);
                    $update_stmt->execute();
                    $update_stmt->close();
                }
                $profile_image_path = $target_file;
                $notification = "File uploaded successfully!";
            } else {
                throw new Exception("File saving error. Check file permissions.");
            }
        }
    }

} catch (Exception $e) {
    header('Location: error.php?message=' . urlencode($e->getMessage()));
    exit();
}
?>

<div class="container profile-container">
    <h1>Profile</h1>
    <div class="profile-info-container">
        <div class="profile-image" style="width: 150px; height: 150px; background-color: #ccc; display: flex; align-items: center; justify-content: center;">
            <?php if ($profile_image_path): ?>
                <img src="<?php echo htmlspecialchars($profile_image_path); ?>" style="width: 100%; height: 100%; object-fit: cover;">
            <?php else: ?>
                <span style="color: #777;">No Image</span>
            <?php endif; ?>
        </div>
        <div>
            <p>Username: <?php echo htmlspecialchars($username); ?></p>
            <p>Email: <?php echo htmlspecialchars($email); ?></p>
            <p>Rating: <?php echo htmlspecialchars($rating); ?></p>
        </div>
    </div>



    <form action="profile.php" method="POST" enctype="multipart/form-data">
        <label for="profile_image">Upload Profile Image:</label>
        <input type="file" name="profile_image" accept="image/*" required>
        <button type="submit">Upload</button>
    </form>

    <?php if ($notification): ?>
        <p><?php echo htmlspecialchars($notification); ?></p>
    <?php endif; ?>
</div>