<?php
session_start();
require_once "../../config/database.php"; 

// 1. Security Check
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'employee') {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 2. Sanitize Input
    $name = trim($_POST['emp_name']); // Taking from form 'emp_name' but mapping to DB 'name'
    $position = trim($_POST['position']);

    // 3. Validation
    if (empty($name) || empty($position)) {
        header("Location: ../dashboard.php?status=missing_data");
        exit;
    }

    // 4. Generate Tactical Employee Code (EMP-2026-XXXX)
    $emp_code = "EMP-" . date("Y") . "-" . rand(1000, 9999);

    // 5. Prepared Statement using 'name' column
    // We include user_id as NULL because this is a manual staff add, not a registration
    $stmt = $mysqli->prepare("INSERT INTO employees (user_id, employee_code, name, position, is_active) VALUES (NULL, ?, ?, ?, 1)");
    
    // "sss" = 3 strings (code, name, position)
    $stmt->bind_param("sss", $emp_code, $name, $position);

    if ($stmt->execute()) {
        header("Location: ../employees.php?status=onboarded");
    } else {
        // If it fails, it might be a duplicate employee_code or DB error
        header("Location: ../employees.php?status=db_error");
    }

    $stmt->close();
    $mysqli->close();
} else {
    header("Location: ../employees.php");
    exit;
}