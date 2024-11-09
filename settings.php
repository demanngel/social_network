<?php
include './db.php';
include 'header.php';

$notification = '';

try {
    if ($conn->connect_error) {
        throw new Exception("Ошибка подключения к базе данных: " . $conn->connect_error);
    }

    if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'admin') {
        header('Location: loginForm.php');
        exit();
    }

    $sql = "SELECT name, value FROM settings";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->execute();
        $settings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    } else {
        throw new Exception("Ошибка подготовки запроса: " . $conn->error);
    }

    $settings = array_column($settings, 'value', 'name');

    $topics = [];

    $sql = "SELECT id, name, weight FROM topics";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->execute();
        $topics = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    } else {
        throw new Exception("Ошибка подготовки запроса: " . $conn->error);
    }

    $sql = "SELECT id, mime_type, enabled FROM allowed_image_types";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->execute();
        $image_types = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    } else {
        throw new Exception("Ошибка подготовки запроса: " . $conn->error);
    }

    $sql = "SELECT name, value FROM settings WHERE name = 'max_image_size'";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->execute();
        $result = $stmt->get_result();
        $max_image_size = $result->fetch_assoc()['value'] ?? 5;
        $stmt->close();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['max_image_size'])) {
        $new_max_size = min(floatval($_POST['max_image_size']), 50);

        $sql = "UPDATE settings SET value = ? WHERE name = 'max_image_size'";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param('s', $new_max_size);
            $stmt->execute();
            $stmt->close();
            $settings['max_image_size'] = $new_max_size;
        } else {
            throw new Exception("Ошибка при обновлении максимального размера изображения: " . $stmt->error);
        }
    }


    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_topic'])) {
        $topic_name = trim($_POST['topic_name']);
        $topic_weight = floatval($_POST['topic_weight']);

        if (empty($topic_name)) {
            $notification = 'Topic name cannot be empty or consist only of spaces.';
        } else {
            $isUnique = true;
            foreach ($topics as $topic) {
                if (strcasecmp($topic['name'], $topic_name) === 0) {
                    $isUnique = false;
                    break;
                }
            }

            if (!$isUnique) {
                $notification = 'Topic name must be unique.';
            } else {
                $sql = "INSERT INTO topics (name, weight) VALUES (?, ?)";
                if ($stmt = $conn->prepare($sql)) {
                    $stmt->bind_param('sd', $topic_name, $topic_weight);
                    $stmt->execute();
                    $stmt->close();
                    header("Location: " . $_SERVER['PHP_SELF']);
                    exit();
                } else {
                    throw new Exception("Ошибка при добавлении тематики: " . $stmt->error);
                }
            }
        }
    }

    if (isset($_GET['delete_topic'])) {
        $topic_id = intval($_GET['delete_topic']);
        $sql = "DELETE FROM topics WHERE id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param('i', $topic_id);
            $stmt->execute();
            $stmt->close();
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } else {
            throw new Exception("Ошибка при удалении тематики: " . $stmt->error);
        }
    }

    if (isset($_POST['update_weights'])) {
        foreach ($_POST['weights'] as $id => $weight) {
            $sql = "UPDATE topics SET weight = ? WHERE id = ?";
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param('di', $weight, $id);
                $stmt->execute();
                $stmt->close();
            } else {
                throw new Exception("Ошибка при обновлении веса тематики: " . $stmt->error);
            }
        }
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['weight_factor1'])) {
        $weightFactor1 = isset($_POST['weight_factor1']) ? floatval($_POST['weight_factor1']) : 0;

        $sql = "UPDATE settings SET value = ? WHERE name = ?";

        if ($stmt = $conn->prepare($sql)) {
            $str = 'weight_factor1';
            $stmt->bind_param('ss', $weightFactor1, $str);
            $stmt->execute();
            $settings['weight_factor1'] = $weightFactor1;
            $stmt->close();
        } else {
            throw new Exception("Ошибка при обновлении weight_factor1: " . $stmt->error);
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_image_types'])) {
        foreach ($image_types as $type) {
            $enabled = isset($_POST['image_types'][$type['id']]) ? 1 : 0;
            $sql = "UPDATE allowed_image_types SET enabled = ? WHERE id = ?";
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param('ii', $enabled, $type['id']);
                $stmt->execute();
                $stmt->close();
            }
        }
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }

} catch (Exception $e) {
    header('Location: error.php?message=' . urlencode($e->getMessage()));
    exit();
}
?>

<div class="container">
    <h1>Settings</h1>

    <form method="POST" class="weight_form">
        <div class="factors_container">
            <div class="factor1">
                <label for="weight_factor1">Weight user rating:</label>
                <input type="number" id="weight_factor1" name="weight_factor1" min="0" max="1" step="0.01"
                       value="<?= isset($settings['weight_factor1']) ? htmlspecialchars(floatval($settings['weight_factor1'])) : '0' ?>" required>
            </div>
        </div>
        <button type="submit">Save</button>
    </form>

    <h2>Topics</h2>

    <?php if ($notification): ?>
        <div class="notification"><?= htmlspecialchars($notification) ?></div>
    <?php endif; ?>

    <form method="POST" class="add_topic_form">
        <div class="add_topic_container">
            <div>
                <label for="topic_name">Topic name:</label>
                <input type="text" name="topic_name" id="topic_name" required maxlength="100">
            </div>
            <div class="topic_weight_container">
                <label for="topic_weight">Weight:</label>
                <input type="number" name="topic_weight" class="topic_weight" id="topic_weight" min="0" max="1" step="0.01" required>
            </div>
        </div>
        <button type="submit" name="add_topic">Add Topic</button>
    </form>

    <form method="POST" class="update_weights_form">
        <p>
            <?php foreach ($topics as $topic): ?>
        </p>
        <div class="topic_container">
            <?= htmlspecialchars($topic['name']) ?>
            <div class="update_topic_container">
                <input class="topic_weight" type="number" name="weights[<?= $topic['id'] ?>]" value="<?= htmlspecialchars($topic['weight']) ?>" min="0" max="1" step="0.01">
                <div class="back-button action-button">
                    <a href="?delete_topic=<?= $topic['id'] ?>">🗑</a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        <button type="submit" name="update_weights">Update</button>
    </form>
    <form method="POST" class="image_settings_form">
        <h2>Image settings</h2>

        <div class="image-size-container">
            <label for="max_image_size">Maximum image size (MB):</label>
            <input type="number" id="max_image_size" name="max_image_size" min="1" max="50" step="0.1"
                   value="<?= htmlspecialchars(floatval($settings['max_image_size'] ?? 5)) ?>" required>
        </div>

        <div class="image-types">
            <?php foreach ($image_types as $type): ?>
                <div class="mime-types">
                    <input type="checkbox" name="image_types[<?= $type['id'] ?>]" value="1" <?= $type['enabled'] ? 'checked' : '' ?>>
                    <p><?= htmlspecialchars($type['mime_type']) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
        <button type="submit" name="update_image_types">Save</button>
    </form>


    </form>
</div>