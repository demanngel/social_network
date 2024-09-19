<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Ошибка</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="container">
    <h1>Ошибка</h1>
    <?php if (isset($_GET['error'])): ?>
        <p class="error"><?php echo htmlspecialchars($_GET['error']); ?></p>
    <?php else: ?>
        <p class="error">Произошла неизвестная ошибка.</p>
    <?php endif; ?>
    <a href="posts.php">Вернуться на страницу постов</a>
</div>
</body>
</html>

