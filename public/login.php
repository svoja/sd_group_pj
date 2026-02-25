<?php
// login.php
session_start();

// If they are already logged in, don't let them see the login page!
if (isset($_SESSION['logged_in'])) {
    if ($_SESSION['role'] === 'employee') {
        header("Location: customers.php");
    } else {
        header("Location: index.php");
    }
    exit;
}

$message = "";
$messageType = "";

// Check the URL for error codes sent by login_process.php
if (isset($_GET['error'])) {
    $messageType = "danger";
    if ($_GET['error'] === 'invalid_credentials') {
        $message = "Invalid email or password.";
    } elseif ($_GET['error'] === 'empty_fields') {
        $message = "Please fill in all fields.";
    } else {
        $message = "An error occurred. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sales System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
    </style>
</head>
<body class="d-flex justify-content-center align-items-center p-3">
    
    <div class="card shadow-lg p-5" style="width: 100%; max-width: 400px; border-radius: 15px;">
        <h2 class="text-center mb-4">Welcome Back</h2>
        
        <?php if($message): ?>
            <div class="alert alert-<?= $messageType ?>"><?= $message ?></div>
        <?php endif; ?>

        <form method="POST" action="login_process.php">
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" required placeholder="your@email.com">
            </div>
            
            <div class="mb-4">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required placeholder="••••••••">
            </div>
            
            <button type="submit" class="btn btn-primary w-100 py-2">Log In</button>
        </form>
        
        <div class="text-center mt-4">
            Don't have an account? <a href="signup.php" class="text-decoration-none fw-bold">Sign up</a>
        </div>
    </div>

</body>
</html>