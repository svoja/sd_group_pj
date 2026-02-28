<?php
session_start();
require_once "../config/database.php";

// Security Check
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'employee') {
    header("Location: ../login.php");
    exit;
}

if (isset($_GET['id'])) {
    $customer_id = intval($_GET['id']);
    
    // Execute the DELETE query
    $stmt = $mysqli->prepare("DELETE FROM customers WHERE customer_id = ?");
    $stmt->bind_param("i", $customer_id);
    
    if ($stmt->execute()) {
        header("Location: ../customers.php?status=deleted");
    } else {
        header("Location: ../customers.php?status=db_error");
    }

    $stmt->close();
    $mysqli->close();
} else {
    header("Location: ../customers.php");
}
?>