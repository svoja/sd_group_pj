<?php
session_start();
require_once "../../config/database.php"; 

// Security Guard
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'employee') {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $order_id = intval($_POST['order_id']);
    $invoice_date = trim($_POST['invoice_date']);
    $payment_method = trim($_POST['payment_method']);
    $payment_status = trim($_POST['payment_status']);

    if ($order_id <= 0) {
        header("Location: ../invoices.php?status=db_error");
        exit;
    }

    // 1. Fetch backend truth from the sale_orders table
    $stmt = $mysqli->prepare("SELECT customer_id, total_amount FROM sale_orders WHERE order_id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $orderData = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$orderData) {
        header("Location: ../invoices.php?status=db_error");
        exit;
    }

    $customer_id = $orderData['customer_id'];
    $total_amount = $orderData['total_amount'];

    // 2. Automate Invoice Reference (e.g., INV-2026-94821)
    $year = date("Y", strtotime($invoice_date));
    $rand = rand(10000, 99999);
    $invoice_reference = "INV-" . $year . "-" . $rand;

    // 3. Database Insertion
    // Assuming columns: invoice_reference, order_id, customer_id, total_amount, payment_method, payment_status, invoice_date
    $stmt = $mysqli->prepare("INSERT INTO invoices (invoice_reference, order_id, customer_id, total_amount, payment_method, payment_status, invoice_date) VALUES (?, ?, ?, ?, ?, ?, ?)");
    
    // "siidsss" = string, integer, integer, double, string, string, string
    $stmt->bind_param("siidsss", $invoice_reference, $order_id, $customer_id, $total_amount, $payment_method, $payment_status, $invoice_date);

    if ($stmt->execute()) {
        header("Location: ../invoices.php?status=generated");
    } else {
        header("Location: ../invoices.php?status=db_error");
    }

    $stmt->close();
    $mysqli->close();
} else {
    header("Location: ../invoices.php");
}
?>