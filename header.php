<?php
session_start();
include './db.php';

$conn = db_connect();

if (!isset($_SESSION['theme'])) {
    $_SESSION['theme'] = 'light';
}

$theme = $_SESSION['theme'];

if (isset($_POST['toggle_theme'])) {
    $new_theme = $theme === 'dark' ? 'light' : 'dark';
    $_SESSION['theme'] = $new_theme;
    $theme = $new_theme;
}

if (isset($_SESSION['user_id'])) {
    $user_id = intval($_SESSION['user_id']);
    $sql = "SELECT role FROM users WHERE id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $stmt->bind_result($user_role);
        if ($stmt->fetch()) {
            $_SESSION['user_role'] = $user_role;
        } else {
            session_unset();
            session_destroy();
            header('Location: loginForm.php');
            exit();
        }
        $stmt->close();
    } else {
        die("Ошибка запроса: " . $conn->error);
    }
} else {
    $user_role = 'guest';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Social Network'; ?></title>
    <link rel="stylesheet" href="styles/styles.css">
    <link rel="stylesheet" href="<?php echo $theme === 'dark' ? 'styles/dark-theme.css' : 'styles/light-theme.css'; ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
<header>
    <div class="header-container">
        <nav class="navbar">
            <a href="homepage.php" class="navel">Home</a>
            <?php if ($user_role == 'admin'): ?>
                <a href="users.php" class="navel">Users</a>
                <a href="settings.php" class="navel">Settings</a>
            <?php endif; ?>
            <?php if ($user_role == 'user' || $user_role == 'moderator'): ?>
                <a href="groups.php" class="navel">Groups</a>
            <?php endif; ?>
            <?php if ($user_role == 'user'): ?>
                <a href="profile.php" class="navel">Profile</a>
            <?php endif; ?>
            <a href="logout.php" class="navel">Logout</a>
            <form method="post" class="theme-switcher">
                <button type="submit" name="toggle_theme" class="slider-button <?php echo $theme === 'dark' ? 'active' : ''; ?>">
                    <span class="slider round"></span>
                </button>
            </form>
        </nav>
    </div>
</header>
</body>
</html>
