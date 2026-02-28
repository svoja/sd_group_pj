<?php
session_start();
require_once "../config/database.php"; 

if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'employee') {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $invoice_id = intval($_POST['invoice_id']);
    $invoice_date = trim($_POST['invoice_date']);
    $payment_method = trim($_POST['payment_method']);
    $payment_status = trim($_POST['payment_status']);

    if ($invoice_id <= 0 || empty($invoice_date)) {
        header("Location: ../invoices.php?status=db_error");
        exit;
    }

    $stmt = $mysqli->prepare("UPDATE invoices SET invoice_date = ?, payment_method = ?, payment_status = ? WHERE invoice_id = ?");
    
    // "sssi" = string, string, string, integer
    $stmt->bind_param("sssi", $invoice_date, $payment_method, $payment_status, $invoice_id);

    if ($stmt->execute()) {
        header("Location: ../invoices.php?status=updated");
    } else {
        header("Location: ../invoices.php?status=db_error");
    }

    $stmt->close();
    $mysqli->close();
} else {
    header("Location: ../invoices.php");
}
?>