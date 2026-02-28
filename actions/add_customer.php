<?php
session_start();
require_once "../config/database.php"; 

if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'employee') {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $name = trim($_POST['contact_name']);
    $tier = trim($_POST['membership_level']);
    $address = trim($_POST['address']);

    // AUTOMATE THE CLIENT CODE (Format: CLI-2026-8492)
    $code = "CLI-" . date("Y") . "-" . rand(1000, 9999);

    // Database Insertion (Assuming user_id is NULL for manual entries)
    $stmt = $mysqli->prepare("INSERT INTO customers (user_id, customer_code, contact_name, address, membership_level) VALUES (NULL, ?, ?, ?, ?)");
    
    // "ssss" = 4 strings
    $stmt->bind_param("ssss", $code, $name, $address, $tier);

    if ($stmt->execute()) {
        header("Location: ../customers.php?status=registered");
    } else {
        header("Location: ../customers.php?status=db_error");
    }

    $stmt->close();
    $mysqli->close();
} else {
    header("Location: ../customers.php");
}
?>