<?php
session_start();
require_once "../config/database.php"; 

// 1. STRICT SECURITY GUARD
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'employee') {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 2. Fetch Data from the Form & Session
    $customer_id = intval($_POST['customer_id']);
    $special_discount = floatval($_POST['special_discount']);
    $employee_id = intval($_POST['employee_id']); // This was securely saved in sales.php
    
    // Validate that a client was actually selected
    if ($customer_id <= 0) {
        header("Location: ../sales.php?status=db_error");
        exit;
    }

    // 3. AUTOMATE THE SECURE IDs
    $year = date("Y");
    $rand = rand(10000, 99999);
    
    // Format: REF-2026-84921
    $reference_id = "REF-" . $year . "-" . $rand;
    // Format: PO-2026-84921
    $po_reference = "PO-" . $year . "-" . $rand;

    // 4. Default Financial Values (Empty Cart)
    $subtotal = 0.00;
    $membership_discount = 0.00; // This gets calculated when items are added based on the client's tier
    $total_amount = 0.00; // Even with a discount, an empty cart costs $0.00

    // Generate the exact timestamp for order_date
    $order_date = date("Y-m-d H:i:s");

    // 5. Database Insertion matching your exact columns
    $stmt = $mysqli->prepare("INSERT INTO sale_orders (reference_id, po_reference, customer_id, employee_id, subtotal, membership_discount, special_discount, total_amount, order_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    // "ssiidddds" = 2 strings, 2 integers, 4 doubles (decimals), 1 string (datetime)
    $stmt->bind_param("ssiidddds", $reference_id, $po_reference, $customer_id, $employee_id, $subtotal, $membership_discount, $special_discount, $total_amount, $order_date);

    if ($stmt->execute()) {
        // Success: Send them back to the Sales Ledger so they can click "Add Parts"
        header("Location: ../sales.php?status=initialized");
    } else {
        // Error: Likely a database constraint issue
        header("Location: ../sales.php?status=db_error");
    }

    $stmt->close();
    $mysqli->close();
} else {
    // Kick out anyone trying to load this URL directly
    header("Location: ../sales.php");
    exit;
}
?>