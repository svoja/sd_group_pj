<?php
session_start();
require_once "../../config/database.php";

if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'employee') {
    header("Location: ../login.php");
    exit;
}

if (isset($_GET['detail_id']) && isset($_GET['order_id'])) {
    $detail_id = intval($_GET['detail_id']);
    $order_id = intval($_GET['order_id']);

    // 1. Fetch Item before deleting so we know how much to restore
    $stmt = $mysqli->prepare("SELECT product_id, quantity FROM sale_order_details WHERE detail_id = ?");
    $stmt->bind_param("i", $detail_id);
    $stmt->execute();
    $item = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($item) {
        // 2. Restore Stock in Warehouse
        $stmt = $mysqli->prepare("UPDATE products SET stock_qty = stock_qty + ? WHERE product_id = ?");
        $stmt->bind_param("ii", $item['quantity'], $item['product_id']);
        $stmt->execute();
        $stmt->close();

        // 3. Delete from Order Details
        $stmt = $mysqli->prepare("DELETE FROM sale_order_details WHERE detail_id = ?");
        $stmt->bind_param("i", $detail_id);
        $stmt->execute();
        $stmt->close();

        // 4. Recalculate Master Order Totals
        $stmt = $mysqli->prepare("SELECT SUM(total_price) as new_subtotal FROM sale_order_details WHERE order_id = ?");
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $calc = $stmt->get_result()->fetch_assoc();
        $new_subtotal = $calc['new_subtotal'] ? (float)$calc['new_subtotal'] : 0.00;
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
    }

    header("Location: ../order_terminal.php?id=$order_id&status=item_removed");
    exit;
}
?>