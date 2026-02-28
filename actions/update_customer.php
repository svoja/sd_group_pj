<?php
session_start();
require_once "../config/database.php"; 

// Security Check
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'employee') {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $customer_id = intval($_POST['customer_id']);
    $contact_name = trim($_POST['contact_name']);
    $membership_level = trim($_POST['membership_level']);
    $address = trim($_POST['address']);

    // Validation
    if (empty($contact_name) || $customer_id <= 0) {
        header("Location: ../customers.php?status=db_error");
        exit;
    }

    // Update the Database
    $stmt = $mysqli->prepare("UPDATE customers SET contact_name = ?, membership_level = ?, address = ? WHERE customer_id = ?");
    
    // "sssi" = 3 strings, 1 integer
    $stmt->bind_param("sssi", $contact_name, $membership_level, $address, $customer_id);

    if ($stmt->execute()) {
        header("Location: ../customers.php?status=updated");
    } else {
        header("Location: ../customers.php?status=db_error");
    }

    $stmt->close();
    $mysqli->close();
} else {
    header("Location: ../customers.php");
}
?>