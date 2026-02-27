<?php
// login_process.php

// 1. Start the session
session_start();

// 2. Include database connection
require_once "../../config/database.php";

// 3. Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!empty($email) && !empty($password)) {
        // Find the user in the database
        $stmt = $mysqli->prepare("SELECT user_id, email, password_hash, role FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            // Verify the password matches the hash in the database
            if (password_verify($password, $row['password_hash'])) {
                
                // Success! Save user data to the Session
                $_SESSION['user_id'] = $row['user_id'];
                $_SESSION['email'] = $row['email'];
                $_SESSION['role'] = $row['role'];
                $_SESSION['logged_in'] = true;
                
                // Route them based on their role
                if ($row['role'] === 'employee') {
                    header("Location: ../customers.php");
                } else {
                    header("Location: ../index.php"); // Customer dashboard
                }
                exit; 
                
            } else {
                // Incorrect password
                header("Location: ../login.php?error=invalid_credentials");
                exit;
            }
        } else {
            // User not found
            header("Location: ../login.php?error=invalid_credentials");
            exit;
        }
    } else {
        // Empty fields
        header("Location: ../login.php?error=empty_fields");
        exit;
    }
} else {
    // If someone tries to access this file directly without submitting the form
    header("Location: ../login.php");
    exit;
}
?>