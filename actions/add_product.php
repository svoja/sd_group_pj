<?php
session_start();
require_once "../config/database.php"; 

if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'employee') {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = trim($_POST['product_name']);
    $type = trim($_POST['product_type']);
    $desc = trim($_POST['product_description']);
    $cost = floatval($_POST['cost_price']);
    $sell = floatval($_POST['selling_price']);
    $qty = intval($_POST['stock_qty']);

    $prefix = strtoupper(substr($type, 0, 3));
    $code = $prefix . "-" . date("Y") . "-" . rand(1000, 9999);

    // ===== IMAGE UPLOAD =====
    $uploadDir = "../assets/images/products/";

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == 0) {

        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
        $fileExt = strtolower(pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION));

        if (!in_array($fileExt, $allowedTypes)) {
            header("Location: ../products.php?status=invalid_image");
            exit;
        }

        $imageName = $code . "_" . time() . "." . $fileExt;
        $targetPath = $uploadDir . $imageName;

        if (!move_uploaded_file($_FILES['product_image']['tmp_name'], $targetPath)) {
            header("Location: ../products.php?status=upload_failed");
            exit;
        }

    } else {
        header("Location: ../products.php?status=no_image");
        exit;
    }

    // ===== INSERT DATABASE =====
    $stmt = $mysqli->prepare("INSERT INTO products 
        (product_code, product_name, product_type, product_description, cost_price, selling_price, stock_qty, image_path) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param("ssssddis", 
        $code,
        $name,
        $type,
        $desc,
        $cost,
        $sell,
        $qty,
        $imageName   // <-- บันทึกชื่อไฟล์ลง image_path
    );

    if ($stmt->execute()) {
        header("Location: ../products.php?status=part_registered");
    } else {
        header("Location: ../products.php?status=db_error");
    }

    $stmt->close();
    $mysqli->close();
    exit;
}
?>