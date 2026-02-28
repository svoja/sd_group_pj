<?php
session_start();
require_once "../config/database.php";

if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'employee') {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $detail_id = intval($_POST['detail_id']);
    $order_id = intval($_POST['order_id']);
    $new_quantity = intval($_POST['quantity']);

    if ($new_quantity <= 0) {
        header("Location: ../order_terminal.php?id=$order_id&status=invalid_input");
        exit;
    }

    // 1. Get Current Item Data
    $stmt = $mysqli->prepare("SELECT product_id, quantity, unit_price FROM sale_order_details WHERE detail_id = ?");
    $stmt->bind_param("i", $detail_id);
    $stmt->execute();
    $item = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $product_id = $item['product_id'];
    $old_quantity = $item['quantity'];
    $unit_price = $item['unit_price'];

    // 2. Calculate the difference in quantity
    $qty_difference = $new_quantity - $old_quantity;

    // 3. Verify Stock ONLY if we are adding more
    if ($qty_difference > 0) {
        $stmt = $mysqli->prepare("SELECT stock_qty FROM products WHERE product_id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $stockCheck = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($stockCheck['stock_qty'] < $qty_difference) {
            header("Location: ../order_terminal.php?id=$order_id&status=stock_error");
            exit;
        }
    }

    // 4. Adjust Inventory (+ or - depending on the difference)
    // We subtract the difference. If difference is negative (e.g. 5 -> 2 = -3), subtracting -3 ADDS 3 back to stock.
    $stmt = $mysqli->prepare("UPDATE products SET stock_qty = stock_qty - ? WHERE product_id = ?");
    $stmt->bind_param("ii", $qty_difference, $product_id);
    $stmt->execute();
    $stmt->close();

    // 5. Update Sale Order Details
    $new_total_price = $new_quantity * $unit_price;
    $stmt = $mysqli->prepare("UPDATE sale_order_details SET quantity = ?, total_price = ? WHERE detail_id = ?");
    $stmt->bind_param("idi", $new_quantity, $new_total_price, $detail_id);
    $stmt->execute();
    $stmt->close();

    // 6. Recalculate Master Order Totals
    $stmt = $mysqli->prepare("SELECT SUM(total_price) as new_subtotal FROM sale_order_details WHERE order_id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $new_subtotal = (float)$stmt->get_result()->fetch_assoc()['new_subtotal'];
    $stmt->close();

    $stmt = $mysqli->prepare("SELECT membership_discount, special_discount FROM sale_orders WHERE order_id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $orderData = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $final_total = max(0, $new_subtotal - ($orderData['membership_discount'] + $orderData['special_discount']));

    $stmt = $mysqli->prepare("UPDATE sale_orders SET subtotal = ?, total_amount = ? WHERE order_id = ?");
    $stmt->bind_param("ddi", $new_subtotal, $final_total, $order_id);
    $stmt->execute();
    $stmt->close();

    header("Location: ../order_terminal.php?id=$order_id&status=item_updated");
    exit;
}
?>