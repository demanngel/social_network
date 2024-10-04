<?php
include './db.php';
include 'header.php';

try {
    if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'admin') {
        header('Location: loginForm.php');
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $background_color = $_POST['background_color'] ?? '#ffffff';
        $text_color = $_POST['text_color'] ?? '#000000';
        $primary_color = $_POST['primary_color'] ?? '#000000';
        $accent_color = $_POST['accent_color'] ?? '#000000';
        $border_color = $_POST['border_color'] ?? '#000000';
        $secondary_color = $_POST['secondary_color'] ?? '#000000';

        $sql = "UPDATE settings SET background_color = ?, text_color = ?, primary_color = ?, accent_color = ?, border_color = ?, secondary_color = ? WHERE id = 1";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param('ssssss', $background_color, $text_color, $primary_color, $accent_color, $border_color, $secondary_color);
            $stmt->execute();
            $stmt->close();
        } else {
            throw new Exception("Ошибка при обновлении данных: " . $conn->error);
        }

        header('Location: settings.php');
        exit();
    }

    $sql = "SELECT background_color, text_color, primary_color, accent_color, border_color, secondary_color FROM settings WHERE id = 1";
    $result = $conn->query($sql);
    $colors = $result->fetch_assoc();
} catch (Exception $e) {
    header('Location: error.php?message=' . urlencode($e->getMessage()));
    exit();
}
?>

<div class="container">
    <h1>Color settings</h1>
    <form action="settings.php" method="POST">
        <div class="color-settings">
            <label for="background_color">Background color:</label>
            <input type="color" id="background_color" name="background_color" class="color" value="<?php echo htmlspecialchars($colors['background_color']); ?>">
        </div>
        <div class="color-settings">
            <label for="text_color">Text color:</label>
            <input type="color" id="text_color" name="text_color" class="color" value="<?php echo htmlspecialchars($colors['text_color']); ?>">
        </div>
        <div class="color-settings">
            <label for="primary_color">Primary color:</label>
            <input type="color" id="primary_color" name="primary_color" class="color" value="<?php echo htmlspecialchars($colors['primary_color']); ?>">
        </div>
        <div class="color-settings">
            <label for="accent_color">Accent color:</label>
            <input type="color" id="accent_color" name="accent_color" class="color" value="<?php echo htmlspecialchars($colors['accent_color']); ?>">
        </div>
        <div class="color-settings">
            <label for="border_color">Border color:</label>
            <input type="color" id="border_color" name="border_color" class="color" value="<?php echo htmlspecialchars($colors['border_color']); ?>">
        </div>
        <div class="color-settings">
            <label for="secondary_color">Secondary color:</label>
            <input type="color" id="secondary_color" name="secondary_color" class="color" value="<?php echo htmlspecialchars($colors['secondary_color']); ?>">
        </div>
        <button type="submit">Save</button>
    </form>
</div>
