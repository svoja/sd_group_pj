<?php
session_start();
require_once "../../config/database.php"; 

if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'employee') {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 1. Get the manual inputs
    $name = trim($_POST['product_name']);
    $type = trim($_POST['product_type']);
    $desc = trim($_POST['product_description']);
    $cost = floatval($_POST['cost_price']);
    $sell = floatval($_POST['selling_price']);
    $qty = intval($_POST['stock_qty']);

    // 2. AUTOMATE THE PART CODE
    // Grab the first 3 letters of the type (e.g., "Engine" -> "ENG") and make it uppercase
    $prefix = strtoupper(substr($type, 0, 3)); 
    // Format: ENG-2026-4921
    $code = $prefix . "-" . date("Y") . "-" . rand(1000, 9999);

    // 3. Database Insertion
    $stmt = $mysqli->prepare("INSERT INTO products (product_code, product_name, product_type, product_description, cost_price, selling_price, stock_qty) VALUES (?, ?, ?, ?, ?, ?, ?)");
    
    // "ssssddi" = 4 strings, 2 doubles (floats), 1 integer
    $stmt->bind_param("ssssddi", $code, $name, $type, $desc, $cost, $sell, $qty);

    if ($stmt->execute()) {
        header("Location: ../products.php?status=part_registered");
    } else {
        header("Location: ../products.php?status=db_error");
    }

    $stmt->close();
    $mysqli->close();
} else {
    header("Location: ../products.php");
}
?>