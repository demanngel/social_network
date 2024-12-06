<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles/styles.css">
    <link rel="stylesheet" href="<?php echo $theme === 'dark' ? '../styles/dark-theme.css' : '../styles/light-theme.css'; ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
<header>
    <nav class="navbar">
        <?php foreach ($navLinks as $label => $url): ?>
            <a href="<?php echo htmlspecialchars($url); ?>" class="navel">
                <?php echo ucfirst($label); ?>
            </a>
        <?php endforeach; ?>
        <form method="post" class="theme-switcher">
            <button type="submit" name="toggle_theme" class="slider-button <?php echo $theme === 'dark' ? 'active' : ''; ?>">
                <span class="slider round"></span>
            </button>
        </form>
    </nav>

</header>
</body>
</html>
