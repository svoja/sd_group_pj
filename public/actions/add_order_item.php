<?php
session_start();
require_once "../../config/database.php";

if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'employee') {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $order_id = intval($_POST['order_id']);
    $product_id = intval($_POST['product_id']);
    $quantity = intval($_POST['quantity']);

    if ($order_id <= 0 || $product_id <= 0 || $quantity <= 0) {
        header("Location: ../order_terminal.php?id=$order_id&status=invalid_input");
        exit;
    }

    // 1. Get Product Details (Price & Stock)
    $stmt = $mysqli->prepare("SELECT selling_price, stock_qty FROM products WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$product || $product['stock_qty'] < $quantity) {
        // Not enough stock!
        header("Location: ../order_terminal.php?id=$order_id&status=stock_error");
        exit;
    }

    $unit_price = (float)$product['selling_price'];
    $total_price = $unit_price * $quantity;

    // 2. Insert into sale_order_details
    $stmt = $mysqli->prepare("INSERT INTO sale_order_details (order_id, product_id, quantity, unit_price, total_price) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iiidd", $order_id, $product_id, $quantity, $unit_price, $total_price);
    $stmt->execute();
    $stmt->close();

    // 3. Deduct Stock from Products Table
    $stmt = $mysqli->prepare("UPDATE products SET stock_qty = stock_qty - ? WHERE product_id = ?");
    $stmt->bind_param("ii", $quantity, $product_id);
    $stmt->execute();
    $stmt->close();

    // 4. Recalculate Master Order Totals
    // Sum all total_prices from sale_order_details for this order
    $stmt = $mysqli->prepare("SELECT SUM(total_price) as new_subtotal FROM sale_order_details WHERE order_id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $calcResult = $stmt->get_result()->fetch_assoc();
    $new_subtotal = (float)$calcResult['new_subtotal'];
    $stmt->close();

    // Fetch the discounts from the order to calculate final total
    $stmt = $mysqli->prepare("SELECT membership_discount, special_discount FROM sale_orders WHERE order_id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $discounts = (float)$order['membership_discount'] + (float)$order['special_discount'];
    $final_total = $new_subtotal - $discounts;
    // Prevent negative total
    if ($final_total < 0) $final_total = 0; 

    // Update the Orders table
    $stmt = $mysqli->prepare("UPDATE sale_orders SET subtotal = ?, total_amount = ? WHERE order_id = ?");
    $stmt->bind_param("ddi", $new_subtotal, $final_total, $order_id);
    $stmt->execute();
    $stmt->close();

    // 5. Success - Redirect back to terminal
    header("Location: ../order_terminal.php?id=$order_id&status=item_added");
    exit;
} else {
    header("Location: ../sales.php");
}
?>