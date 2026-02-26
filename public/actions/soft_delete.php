<?php
session_start();
require_once "../../config/database.php";

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // We update is_active to 0 instead of DELETE FROM
    $stmt = $mysqli->prepare("UPDATE employees SET is_active = 0 WHERE employee_id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        header("Location: ../employees.php?status=decommissioned");
    } else {
        header("Location: ../employees.php?status=error");
    }
}
?>