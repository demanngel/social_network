<?php
session_start();
include './db.php';

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
    <link rel="stylesheet" type="text/css" href="dynamicStyles.php">
    <link rel="stylesheet" href="styles.css">

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

            <a href="logout.php" class="navel">Logout</a>

            <?php if ($user_role == 'guest'): ?>
                <a href="loginForm.php" class="navel">Login</a>
                <a href="registerForm.php" class="navel">Register</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
