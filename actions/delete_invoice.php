<?php
session_start();
require_once "../config/database.php";

// 1. STRICT SECURITY GUARD
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'employee') {
    header("Location: ../login.php");
    exit;
}

if (isset($_GET['id'])) {
    $invoice_id = intval($_GET['id']);
    
    // 2. Execute the DELETE query
    // This safely removes the invoice but leaves the original sale_orders and sale_order_details intact.
    $stmt = $mysqli->prepare("DELETE FROM invoices WHERE invoice_id = ?");
    $stmt->bind_param("i", $invoice_id);
    
    if ($stmt->execute()) {
        header("Location: ../invoices.php?status=deleted");
    } else {
        header("Location: ../invoices.php?status=db_error");
    }

    $stmt->close();
    $mysqli->close();
} else {
    // Kick out anyone trying to load the URL without an ID
    header("Location: ../invoices.php");
}
?>