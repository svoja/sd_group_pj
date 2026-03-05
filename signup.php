<?php
// 1. Include database connection at the very top
require_once "config/database.php";

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
            
            $message = "[ SYS: ENTITY_INITIALIZED ] Proceed to Authorization.";
            $messageType = "success";
        } else {
            $message = "[ ERROR: DATABASE_SYNC_FAILURE ]";
            $messageType = "danger";
        }
    } else {
        $message = "[ ERROR: INCOMPLETE_DATA_SEQUENCE ]";
        $messageType = "danger";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<?php
$pageTitle = 'Initialize Entity | ARAII MOTO';
include 'partials/head.php';
?>
<body class="page-bg-soft text-black font-sans min-h-screen flex items-center justify-center p-6 selection:bg-premium selection:text-white">

    <div class="w-full max-w-md bg-white border border-obsidian-edge p-10 relative shadow-2xl shadow-black/10 anim-load">
        
        <div class="absolute top-0 right-0 p-2 font-mono text-sm text-premium/30 uppercase tracking-widest">
            ENTITY_REGISTRATION
        </div>
        <div class="absolute top-0 left-0 w-8 h-1 bg-premium"></div>

        <h2 class="text-3xl font-black uppercase tracking-tighter mb-2 border-l-4 border-premium pl-4">ARAII <span class="text-premium">MOTO</span></h2>
        <p class="text-obsidian-muted font-mono text-sm mb-8 pl-5 tracking-widest">INITIALIZE_NEW_OPERATOR</p>
        
        <?php if($message): ?>
            <?php 
                $borderColor = ($messageType === 'success') ? 'border-premium text-premium' : 'border-premium text-premium animate-pulse'; 
            ?>
            <div class="mb-6 p-4 bg-white border <?= $borderColor ?> font-mono text-sm uppercase tracking-widest">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" class="space-y-6">
            <div>
                <label class="block text-sm uppercase tracking-[0.2em] text-obsidian-muted mb-2 font-bold">Email Address</label>
                <input type="email" name="email" required placeholder="entity@domain.com" 
                    class="w-full bg-white border border-obsidian-edge px-4 py-3 text-sm font-mono focus:outline-none focus:border-premium transition-colors text-black">
            </div>
            
            <div>
                <label class="block text-sm uppercase tracking-[0.2em] text-obsidian-muted mb-2 font-bold">Passkey Generation</label>
                <input type="password" name="password" required placeholder="••••••••" 
                    class="w-full bg-white border border-obsidian-edge px-4 py-3 text-sm font-mono focus:outline-none focus:border-premium transition-colors text-black tracking-widest">
            </div>

            <div>
                <label class="block text-sm uppercase tracking-[0.2em] text-obsidian-muted mb-2 font-bold">Access Level</label>
                <select name="role" required class="w-full bg-white border border-obsidian-edge px-4 py-3 text-sm font-mono focus:outline-none focus:border-premium transition-colors text-black appearance-none">
                    <option value="customer">Client / Customer</option>
                    <option value="employee">Internal Staff / Employee</option>
                </select>
            </div>
            
            <button type="submit" class="w-full py-4 bg-premium text-white text-sm font-black uppercase tracking-[0.3em] hover:bg-white hover:text-black transition-all duration-300 shadow-[0_0_20px_rgba(176,0,32,0.22)] mt-4">
                Execute Registration
            </button>
        </form>
        
        <div class="text-center mt-8 pt-6 border-t border-obsidian-edge">
            <span class="text-obsidian-muted font-mono text-sm uppercase tracking-widest">
                Existing Entity? 
                <a href="login.php" class="text-premium hover:text-black transition-colors ml-2 font-bold">Initiate_Login</a>
            </span>
        </div>
    </div>

</body>
</html>
