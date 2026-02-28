<?php
session_start();
require_once "../config/database.php";

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
    $stmt = $mysqli->prepare("SELECT SUM(total_price) as new_subtotal FROM sale_order_details WHERE order_id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $calcResult = $stmt->get_result()->fetch_assoc();
    $new_subtotal = $calcResult['new_subtotal'] ? (float)$calcResult['new_subtotal'] : 0.00;
    $stmt->close();

    // 5. FETCH CUSTOMER TIER TO CALCULATE DISCOUNTS
    $stmt = $mysqli->prepare("
        SELECT o.special_discount, c.membership_level 
        FROM sale_orders o 
        JOIN customers c ON o.customer_id = c.customer_id 
        WHERE o.order_id = ?
    ");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $orderData = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    // Calculate the Membership Discount
    $membership_discount = 0.00;
    if ($orderData['membership_level'] === 'PREMIUM') {
        $membership_discount = $new_subtotal * 0.05; // 5% off for Premium
    } elseif ($orderData['membership_level'] === 'ELITE') {
        $membership_discount = $new_subtotal * 0.10; // 10% off for Elite
    }

    // --- NEW: 10% AUTOMATIC VOLUME DISCOUNT ---
    if ($new_subtotal >= 610) {
        // If they hit the $610 threshold, auto-fill the special discount with 10%
        $special_discount = $new_subtotal * 0.10;
    } else {
        // Otherwise, keep whatever manual discount was already typed in
        $special_discount = (float)$orderData['special_discount'];
    }
    
    // Calculate Final Total
    $final_total = $new_subtotal - ($membership_discount + $special_discount);
    if ($final_total < 0) $final_total = 0; // Prevent negative totals

    // 6. Update the Orders table (Notice we added special_discount = ? to this query)
    $stmt = $mysqli->prepare("UPDATE sale_orders SET subtotal = ?, membership_discount = ?, special_discount = ?, total_amount = ? WHERE order_id = ?");
    // "ddddi" = 4 decimals, 1 integer
    $stmt->bind_param("ddddi", $new_subtotal, $membership_discount, $special_discount, $final_total, $order_id);
    $stmt->execute();
    $stmt->close();

    // 7. Success - Redirect back to terminal
    header("Location: ../order_terminal.php?id=$order_id&status=item_added");
    exit;
} else {
    header("Location: ../sales.php");
}
?>