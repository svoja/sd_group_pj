<?php
session_start();
require_once "../config/database.php"; 

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

    // ===== CHECK IF NEW IMAGE UPLOADED =====
    if (!empty($_FILES['product_image']['name'])) {

        $uploadDir = "../assets/images/products/";
        $allowed = ['jpg','jpeg','png','gif'];
        $fileExt = strtolower(pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION));

        if (!in_array($fileExt, $allowed)) {
            header("Location: ../products.php?status=invalid_image");
            exit;
        }

        // Get old image to delete later
        $oldStmt = $mysqli->prepare("SELECT image_path FROM products WHERE product_id = ?");
        $oldStmt->bind_param("i", $product_id);
        $oldStmt->execute();
        $oldResult = $oldStmt->get_result();
        $oldData = $oldResult->fetch_assoc();
        $oldImage = $oldData['image_path'] ?? null;
        $oldStmt->close();

        // Generate new filename
        $newImage = time() . "_" . basename($_FILES['product_image']['name']);
        $targetPath = $uploadDir . $newImage;

        if (!move_uploaded_file($_FILES['product_image']['tmp_name'], $targetPath)) {
            header("Location: ../products.php?status=upload_failed");
            exit;
        }

        // Delete old image if exists
        if (!empty($oldImage) && file_exists($uploadDir . $oldImage)) {
            unlink($uploadDir . $oldImage);
        }

        // UPDATE WITH IMAGE
        $stmt = $mysqli->prepare("UPDATE products 
            SET product_name=?, product_type=?, product_description=?, 
                cost_price=?, selling_price=?, stock_qty=?, image_path=? 
            WHERE product_id=?");

        $stmt->bind_param("sssddisi", 
            $name, 
            $type, 
            $desc, 
            $cost, 
            $sell, 
            $qty, 
            $newImage, 
            $product_id
        );

    } else {

        // UPDATE WITHOUT IMAGE
        $stmt = $mysqli->prepare("UPDATE products 
            SET product_name=?, product_type=?, product_description=?, 
                cost_price=?, selling_price=?, stock_qty=? 
            WHERE product_id=?");

        $stmt->bind_param("sssddii", 
            $name, 
            $type, 
            $desc, 
            $cost, 
            $sell, 
            $qty, 
            $product_id
        );
    }

    if ($stmt->execute()) {
        header("Location: ../products.php?status=updated");
    } else {
        header("Location: ../products.php?status=db_error");
    }

    $stmt->close();
    $mysqli->close();
    exit;

} else {
    header("Location: ../products.php");
    exit;
}
?>