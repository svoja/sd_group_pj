<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../index.php");
    exit;
}

$allowedThemes = ['light', 'dark', 'mono'];
$allowedSizes = ['sm', 'md', 'lg'];

$requestedTheme = strtolower(trim($_POST['ui_theme'] ?? 'light'));
$requestedSize = strtolower(trim($_POST['ui_btn_size'] ?? 'md'));

if (in_array($requestedTheme, $allowedThemes, true)) {
    $_SESSION['ui_theme'] = $requestedTheme;
}

if (in_array($requestedSize, $allowedSizes, true)) {
    $_SESSION['ui_btn_size'] = $requestedSize;
}

$returnUrl = $_POST['return_url'] ?? '../index.php';
header("Location: " . $returnUrl);
exit;
?>
