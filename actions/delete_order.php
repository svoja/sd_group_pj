<?php
session_start();
require_once "../config/database.php";

if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'employee') {
    header("Location: ../login.php");
    exit;
}

if (isset($_GET['id'])) {
    $order_id = intval($_GET['id']);
    
    // 1. Restore Inventory Stock for any items currently sitting in this order's cart
    $stmt = $mysqli->prepare("SELECT product_id, quantity FROM sale_order_details WHERE order_id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $items = $stmt->get_result();
    
    while ($item = $items->fetch_assoc()) {
        $restoreStmt = $mysqli->prepare("UPDATE products SET stock_qty = stock_qty + ? WHERE product_id = ?");
        $restoreStmt->bind_param("ii", $item['quantity'], $item['product_id']);
        $restoreStmt->execute();
        $restoreStmt->close();
    }
    $stmt->close();

    // 2. Delete the Items from sale_order_details
    $stmt = $mysqli->prepare("DELETE FROM sale_order_details WHERE order_id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $stmt->close();

    // 3. Delete the Master Order from sale_orders
    $stmt = $mysqli->prepare("DELETE FROM sale_orders WHERE order_id = ?");
    $stmt->bind_param("i", $order_id);
    
    if ($stmt->execute()) {
        header("Location: ../sales.php?status=deleted");
    } else {
        header("Location: ../sales.php?status=db_error");
    }

    $stmt->close();
    $mysqli->close();
} else {
    header("Location: ../sales.php");
}
?>