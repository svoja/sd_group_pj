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

    if ($detail_id <= 0 || $order_id <= 0 || $new_quantity <= 0) {
        header("Location: ../order_terminal.php?id=$order_id&status=invalid_input");
        exit;
    }

    // 1. Get current item data and ensure this detail belongs to this order.
    $stmt = $mysqli->prepare("SELECT product_id, quantity, unit_price FROM sale_order_details WHERE detail_id = ? AND order_id = ?");
    $stmt->bind_param("ii", $detail_id, $order_id);
    $stmt->execute();
    $item = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$item) {
        header("Location: ../order_terminal.php?id=$order_id&status=item_not_found");
        exit;
    }

    $product_id = $item['product_id'];
    $old_quantity = (int)$item['quantity'];
    $unit_price = (float)$item['unit_price'];

    // 2. Calculate the difference in quantity
    $qty_difference = $new_quantity - $old_quantity;

    if ($qty_difference === 0) {
        header("Location: ../order_terminal.php?id=$order_id&status=item_updated");
        exit;
    }

    $mysqli->begin_transaction();

    // 3. Verify Stock ONLY if we are adding more
    if ($qty_difference > 0) {
        $stmt = $mysqli->prepare("SELECT stock_qty FROM products WHERE product_id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $stockCheck = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$stockCheck || (int)$stockCheck['stock_qty'] < $qty_difference) {
            $mysqli->rollback();
            header("Location: ../order_terminal.php?id=$order_id&status=stock_error");
            exit;
        }
    }

    // 4. Adjust Inventory (+ or - depending on the difference)
    // We subtract the difference. If difference is negative (e.g. 5 -> 2 = -3), subtracting -3 ADDS 3 back to stock.
    $stmt = $mysqli->prepare("UPDATE products SET stock_qty = stock_qty - ? WHERE product_id = ?");
    $stmt->bind_param("ii", $qty_difference, $product_id);
    if (!$stmt->execute()) {
        $stmt->close();
        $mysqli->rollback();
        header("Location: ../order_terminal.php?id=$order_id&status=db_error");
        exit;
    }
    $stmt->close();

    // 5. Update Sale Order Details
    $new_total_price = $new_quantity * $unit_price;
    $stmt = $mysqli->prepare("UPDATE sale_order_details SET quantity = ?, total_price = ? WHERE detail_id = ? AND order_id = ?");
    $stmt->bind_param("idii", $new_quantity, $new_total_price, $detail_id, $order_id);
    if (!$stmt->execute()) {
        $stmt->close();
        $mysqli->rollback();
        header("Location: ../order_terminal.php?id=$order_id&status=db_error");
        exit;
    }
    $stmt->close();

    // 6. Recalculate Master Order Totals
    $stmt = $mysqli->prepare("SELECT SUM(total_price) as new_subtotal FROM sale_order_details WHERE order_id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $calc = $stmt->get_result()->fetch_assoc();
    $new_subtotal = $calc['new_subtotal'] ? (float)$calc['new_subtotal'] : 0.00;
    $stmt->close();

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

    if (!$orderData) {
        $mysqli->rollback();
        header("Location: ../order_terminal.php?id=$order_id&status=db_error");
        exit;
    }

    $membership_discount = ($orderData['membership_level'] === 'PREMIUM') ? ($new_subtotal * 0.05) : 0.00;
    $special_discount = ($new_subtotal >= 610) ? ($new_subtotal * 0.10) : (float)$orderData['special_discount'];
    $final_total = max(0, $new_subtotal - ($membership_discount + $special_discount));

    $stmt = $mysqli->prepare("UPDATE sale_orders SET subtotal = ?, membership_discount = ?, special_discount = ?, total_amount = ? WHERE order_id = ?");
    $stmt->bind_param("ddddi", $new_subtotal, $membership_discount, $special_discount, $final_total, $order_id);
    if (!$stmt->execute()) {
        $stmt->close();
        $mysqli->rollback();
        header("Location: ../order_terminal.php?id=$order_id&status=db_error");
        exit;
    }
    $stmt->close();

    $mysqli->commit();
    header("Location: ../order_terminal.php?id=$order_id&status=item_updated");
    exit;
}

header("Location: ../sales.php");
exit;
?>
