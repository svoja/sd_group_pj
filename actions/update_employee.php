<?php
session_start();
require_once "../config/database.php"; 

// 1. Security Check
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'employee') {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 2. Sanitize Input
    $employee_id = (int)$_POST['employee_id'];
    $name = trim($_POST['name']);
    $position = trim($_POST['position']);

    // 3. Validation
    if (empty($name) || empty($position) || $employee_id <= 0) {
        header("Location: ../dashboard.php?status=missing_data");
        exit;
    }

    // 4. Update the Database
    $stmt = $mysqli->prepare("UPDATE employees SET name = ?, position = ? WHERE employee_id = ?");
    
    // "ssi" = string, string, integer
    $stmt->bind_param("ssi", $name, $position, $employee_id);

    if ($stmt->execute()) {
        header("Location: ../employees.php?status=updated");
    } else {
        header("Location: ../employees.php?status=db_error");
    }

    $stmt->close();
    $mysqli->close();
} else {
    header("Location: ../employees.php");
    exit;
}