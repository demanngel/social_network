<?php
session_start();
include './db.php';

if (!isset($_SESSION['theme'])) {
    $_SESSION['theme'] = 'light';
}

$theme = $_SESSION['theme'];

if (isset($_POST['toggle_theme'])) {
    $new_theme = $theme === 'dark' ? 'light' : 'dark';
    $_SESSION['theme'] = $new_theme;
    $theme = $new_theme;
}

if (isset($_POST['font_size'])) {
    $font_size = $_POST['font_size'];
    setcookie('font_size', $font_size, time() + (86400 * 30), "/");

    $history = isset($_COOKIE['font_size_history']) ? json_decode($_COOKIE['font_size_history'], true) : [];
    $current_time = date('Y-m-d H:i:s');

    $history[] = ['font_size' => $font_size, 'time' => $current_time];

    if (count($history) > 5) {
        array_shift($history);
    }

    setcookie('font_size_history', json_encode($history), time() + (86400 * 30), "/");
    setcookie('selected_font_size_history', json_encode($history[4]), time() + (86400 * 30), "/");

    $font_size_history = $history;
    $selected_font_size_history = end($history);

    header('Location: ' . $_SERVER['REQUEST_URI']);
    exit();
}

if (isset($_POST['font_size_history'])) {
    $selected_entry = json_decode($_POST['font_size_history'], true);

    setcookie('selected_font_size_history', json_encode($selected_entry), time() + (86400 * 30), "/");

    $font_size = $selected_entry['font_size'];
    setcookie('font_size', $font_size, time() + (86400 * 30), "/");

    $selected_font_size_history = $selected_entry;

    header('Location: ' . $_SERVER['REQUEST_URI']);
    exit();
}

$font_size_history = isset($_COOKIE['font_size_history']) ? json_decode($_COOKIE['font_size_history'], true) : [];
$selected_font_size_history = isset($_COOKIE['selected_font_size_history']) ? json_decode($_COOKIE['selected_font_size_history'], true) : null;

if (isset($_COOKIE['font_size'])) {
    $font_size = $selected_font_size_history ? $selected_font_size_history['font_size'] : $_COOKIE['font_size'];
} else {
    $font_size = 'medium';
    setcookie('font_size', $font_size, time() + (86400 * 30), "/");
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
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="<?php echo $theme === 'dark' ? 'dark-theme.css' : 'light-theme.css'; ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --font-size: <?php echo $font_size === 'small' ? '12px' : ($font_size === 'medium' ? '16px' : '20px'); ?>;
        }

        body {
            font-size: var(--font-size);
        }

        h1 {
            font-size: calc(var(--font-size) * 2);
        }

        h2 {
            font-size: calc(var(--font-size) * 1.5);
        }

        button, .navel {
            font-size: calc(var(--font-size) * 1.3);
        }

        p  {
            font-size: var(--font-size);
        }
    </style>
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
            <?php if ($user_role == 'guest'): ?>
                <a href="loginForm.php" class="navel">Login</a>
                <a href="registerForm.php" class="navel">Register</a>
            <?php endif; ?>
            <form method="post" class="theme-switcher">
                <button type="submit" name="toggle_theme" class="slider-button <?php echo $theme === 'dark' ? 'active' : ''; ?>">
                    <span class="slider round"></span>
                </button>
            </form>
            <form method="post" class="font-size-selector">
                <select name="font_size" onChange="this.form.submit()">
                    <option value="small" <?php echo $font_size === 'small' ? 'selected' : ''; ?>>Small</option>
                    <option value="medium" <?php echo $font_size === 'medium' ? 'selected' : ''; ?>>Medium</option>
                    <option value="large" <?php echo $font_size === 'large' ? 'selected' : ''; ?>>Large</option>
                </select>
            </form>
            <?php if (!empty($font_size_history)): ?>
                <form method="post" class="font-size-history" style="width: max-content">
                    <select name="font_size_history" onChange="this.form.submit()">
                        <?php
/*                        $selected_font_size_history = isset($_COOKIE['selected_font_size_history']) ? json_decode($_COOKIE['selected_font_size_history'], true) : null;
                        */?>
                        <?php foreach ($font_size_history as $entry): ?>
                            <option value='<?php echo json_encode($entry); ?>'
                                <?php
                                $isSelected = $selected_font_size_history
                                    && $selected_font_size_history['font_size'] === $entry['font_size']
                                    && $selected_font_size_history['time'] === $entry['time'];
                                echo $isSelected ? 'selected' : '';
                                ?>>
                                <?php echo $entry['font_size']; ?> - <?php echo $entry['time']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            <?php endif; ?>
        </nav>
    </div>
</header>
</body>
</html>
