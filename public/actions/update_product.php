<?php
session_start();
require_once "../../config/database.php"; 

if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'employee') {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $product_id = intval($_POST['product_id']);
    $name = trim($_POST['product_name']);
    $type = trim($_POST['product_type']);
    $desc = trim($_POST['product_description']);
    $cost = floatval($_POST['cost_price']);
    $sell = floatval($_POST['selling_price']);
    $qty = intval($_POST['stock_qty']);

    if (empty($name) || $product_id <= 0) {
        header("Location: ../products.php?status=db_error");
        exit;
    }

    $stmt = $mysqli->prepare("UPDATE products SET product_name = ?, product_type = ?, product_description = ?, cost_price = ?, selling_price = ?, stock_qty = ? WHERE product_id = ?");
    
    // "sssddii" = 3 strings, 2 doubles, 2 integers
    $stmt->bind_param("sssddii", $name, $type, $desc, $cost, $sell, $qty, $product_id);

    if ($stmt->execute()) {
        header("Location: ../products.php?status=updated");
    } else {
        header("Location: ../products.php?status=db_error");
    }

    $stmt->close();
    $mysqli->close();
} else {
    header("Location: ../products.php");
}
?>