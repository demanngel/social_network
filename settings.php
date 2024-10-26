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
        <?php foreach ($topics as $topic): ?>
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
</div>