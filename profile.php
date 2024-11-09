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
        $file = $_FILES['profile_image'];

        $sql = "SELECT mime_type FROM allowed_image_types WHERE enabled = 1";
        $result = $conn->query($sql);

        $allowed_types = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $allowed_types[] = $row['mime_type'];
            }
        }

        if (empty($allowed_types)) {
            die("No allowed types are set in the database.");
        }

        $sql = "SELECT value FROM settings WHERE name = 'max_image_size'";
        $result = $conn->query($sql);

        $max_file_size = 2;
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $max_file_size = (int)$row['value'];
        }

        if ($file['error'] === UPLOAD_ERR_NO_FILE) {
            $notification = "No file was uploaded.";
        } elseif ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['size'] > ($max_file_size * 1024 * 1024)) {
            $notification = "File size exceeds the maximum limit of " . $max_file_size . ' MB.';
        } elseif (!in_array($file['type'], $allowed_types)) {
            $notification = "File type not allowed.";
        } elseif ($file['error'] !== UPLOAD_ERR_OK) {
            $notification = "An error occurred while uploading the file. Error Code: " . $file['error'];
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
                if (getimagesize($target_file) === false) {
                    unlink($target_file);
                    $notification = "Uploaded file is not a valid image or is corrupted.";
                } else {
                    $update_sql = "UPDATE users SET profile_image_path = ? WHERE id = ?";
                    if ($update_stmt = $conn->prepare($update_sql)) {
                        $update_stmt->bind_param('si', $target_file, $user_id);
                        $update_stmt->execute();
                        $update_stmt->close();
                    }
                    $profile_image_path = $target_file;
                    $notification = "File uploaded successfully!";
                }
            } else {
                throw new Exception("File saving error. Check file permissions.");
            }
        }
    }




} catch (Exception $e) {
    $notification = "Error: " . htmlspecialchars($e->getMessage());
}
?>

<div class="container profile-container">
    <h1>Profile</h1>
    <div class="profile-info-container">
        <div class="profile-image"">
            <?php if ($profile_image_path): ?>
                <img src="<?php echo htmlspecialchars($profile_image_path); ?>"">
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