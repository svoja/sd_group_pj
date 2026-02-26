<?php
session_start();
require_once "../../config/database.php";

if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'employee') {
    header("Location: ../login.php");
    exit;
}

if (isset($_GET['id'])) {
    $product_id = intval($_GET['id']);
    
    $stmt = $mysqli->prepare("DELETE FROM products WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);
    
    if ($stmt->execute()) {
        header("Location: ../products.php?status=deleted");
    } else {
        header("Location: ../products.php?status=db_error");
    }

    $stmt->close();
    $mysqli->close();
} else {
    header("Location: ../products.php");
}
?>