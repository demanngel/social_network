<?php
header("Content-Type: text/css");
include './db.php';

$sql = "SELECT background_color, text_color, primary_color, accent_color, border_color, secondary_color FROM settings WHERE id = 1";
$result = $conn->query($sql);
$colors = $result->fetch_assoc();

function hexToRgb($hex): string
{
    $hex = str_replace("#", "", $hex);
    if (strlen($hex) == 3) {
        $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
    }
    $rgb = sscanf($hex, "%02x%02x%02x");
    return implode(", ", $rgb);
}

function darkenColor($hex, $percent): string
{
    $hash = (strpos($hex, '#') !== false) ? '#' : '';
    $hex = ltrim($hex, '#');
    $rgb = sscanf($hex, "%02x%02x%02x");
    foreach ($rgb as &$value) {
        $value = round($value * (1 - $percent));
    }
    return $hash . vsprintf("%02x%02x%02x", $rgb);
}
?>

body {
background-color: <?php echo htmlspecialchars($colors['background_color']); ?>;
color: <?php echo htmlspecialchars($colors['text_color']); ?>;
}

h1, .post-author, h2 {
color: <?php echo htmlspecialchars($colors['primary_color']); ?>;
}

.header-container {
background: <?php echo htmlspecialchars($colors['secondary_color']); ?>;
}

button, .back-button, .search-button, .action-button {
background: <?php echo htmlspecialchars($colors['accent_color']); ?>;
color: <?php echo htmlspecialchars($colors['background_color']); ?>;
}

button:hover, .back-button:hover, .search-button:hover, .action-button:hover {
background: <?php echo htmlspecialchars(darkenColor($colors['accent_color'], 0.1)); ?>;
}

<!--.navel {
color: <?php /*echo htmlspecialchars($colors['background_color']); */?>;
}-->

a {
color: <?php echo htmlspecialchars($colors['background_color']); ?>;
}

<!--a:hover {
color: <?php /*echo htmlspecialchars(darkenColor($colors['accent_color'], 0.1)); */?>;
}-->

input:focus, select:focus {
border-color: <?php echo htmlspecialchars($colors['accent_color']); ?>;
box-shadow: 0 0 8px rgba(<?php echo hexToRgb($colors['accent_color']); ?>, 0.25);
}

th {
background-color: <?php echo htmlspecialchars($colors['secondary_color']); ?>;
}

td, table, th {
border-color: <?php echo htmlspecialchars($colors['border_color']); ?>;
}

table tr:nth-child(even) {
background-color: <?php echo htmlspecialchars(darkenColor($colors['secondary_color'], 0.1)); ?>;
}

button[name="leave_group"] {
background: <?php echo htmlspecialchars($colors['primary_color']); ?>;
}

button[name="leave_group"]:hover {
background: <?php echo htmlspecialchars(darkenColor($colors['primary_color'], 0.1)); ?>;
}

