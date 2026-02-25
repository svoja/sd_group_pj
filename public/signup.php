<?php
// 1. Include database connection at the very top
require_once "../config/database.php";

$message = "";
$messageType = "";

// 2. Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'customer';

    if (!empty($email) && !empty($password)) {
        // Hash password and insert into users
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $mysqli->prepare("INSERT INTO users (email, password_hash, role, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("sss", $email, $hashed, $role);

        if ($stmt->execute()) {
            $user_id = $mysqli->insert_id;
            
            // Automatically set up the required profile data
            if ($role === 'customer') {
                $cust_code = "CUST-" . time(); 
                $mysqli->query("INSERT INTO customers (user_id, customer_code, contact_name, address) VALUES ($user_id, '$cust_code', 'New Customer', 'Not Provided')");
            } elseif ($role === 'employee') {
                $emp_code = "EMP-" . time();
                $mysqli->query("INSERT INTO employees (user_id, employee_code, name, position) VALUES ($user_id, '$emp_code', 'New Employee', 'Unassigned')");
            }
            
            $message = "Account created successfully! You can now login.";
            $messageType = "success";
        } else {
            $message = "Error creating account: " . $mysqli->error;
            $messageType = "danger";
        }
    } else {
        $message = "Please fill in all fields.";
        $messageType = "danger";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Sign Up</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="d-flex justify-content-center align-items-center vh-100 bg-light">
    <div class="card p-4 shadow" style="width: 400px;">
        <h2 class="text-center mb-4">Sign Up</h2>
        
        <?php if($message): ?>
            <div class="alert alert-<?= $messageType ?>"><?= $message ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Role</label>
                <select name="role" class="form-select">
                    <option value="customer">Customer</option>
                    <option value="employee">Employee</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary w-100">Create Account</button>
        </form>
    </div>
</body>
</html>