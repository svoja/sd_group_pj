<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['currency'])) {
    $allowed_currencies = ['USD', 'THB', 'JPY'];
    $requested_currency = strtoupper(trim($_POST['currency']));

    // Security check: Only allow defined currencies
    if (in_array($requested_currency, $allowed_currencies)) {
        $_SESSION['currency'] = $requested_currency;
    }

    // Redirect back to the exact page they were on
    $return_url = $_POST['return_url'] ?? '../dashboard.php';
    header("Location: " . $return_url);
    exit;
} else {
    header("Location: ../dashboard.php");
    exit;
}
?>