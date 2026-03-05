<?php
// login.php
session_start();

// If they are already logged in, don't let them see the login page!
if (isset($_SESSION['logged_in'])) {
    if ($_SESSION['role'] === 'employee') {
        // You can change this to sales.php or dashboard.php if you prefer!
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
        $message = "INVALID CREDENTIALS DETECTED.";
    } elseif ($_GET['error'] === 'empty_fields') {
        $message = "INCOMPLETE DATA SEQUENCE.";
    } else {
        $message = "SYSTEM ERROR. RETRY SEQUENCE.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<?php
$pageTitle = 'System Login | ARAII MOTO';
include 'partials/head.php';
?>
<body class="page-bg-soft text-black font-sans min-h-screen flex items-center justify-center p-6 selection:bg-premium selection:text-white">

    <div class="w-full max-w-md bg-white border border-obsidian-edge p-10 relative shadow-2xl shadow-black/10 anim-load">
        
        <div class="absolute top-0 right-0 p-2 font-mono text-sm text-premium/30 uppercase tracking-widest">
            SECURE_LOGIN_PORTAL
        </div>
        <div class="absolute top-0 left-0 w-8 h-1 bg-premium"></div>

        <h2 class="text-3xl font-black uppercase tracking-tighter mb-2 border-l-4 border-premium pl-4">ARAII <span class="text-premium">MOTO</span></h2>
        <p class="text-obsidian-muted font-mono text-sm mb-8 pl-5 tracking-widest">SYSTEM_AUTHORIZATION</p>
        
        <?php if($message): ?>
            <div class="mb-6 p-4 bg-white border border-red-500 font-mono text-sm uppercase tracking-widest text-red-500 animate-pulse">
                [ AUTH_FAILURE: <?= $message ?> ]
            </div>
        <?php endif; ?>

        <form method="POST" action="actions/login_process.php" class="space-y-6">
            <div>
                <label for="email" class="block text-sm uppercase tracking-[0.2em] text-obsidian-muted mb-2 font-bold">Operator Email</label>
                <input type="email" id="email" name="email" required placeholder="sys.op@araiimoto.com" 
                    class="w-full bg-white border border-obsidian-edge px-4 py-3 text-sm font-mono focus:outline-none focus:border-premium transition-colors text-black">
            </div>
            
            <div>
                <label for="password" class="block text-sm uppercase tracking-[0.2em] text-obsidian-muted mb-2 font-bold">Passkey</label>
                <input type="password" id="password" name="password" required placeholder="••••••••" 
                    class="w-full bg-white border border-obsidian-edge px-4 py-3 text-sm font-mono focus:outline-none focus:border-premium transition-colors text-black tracking-widest">
            </div>
            
            <button type="submit" class="w-full py-4 bg-premium text-white text-sm font-black uppercase tracking-[0.3em] hover:bg-white hover:text-black transition-all duration-300 shadow-[0_0_20px_rgba(176,0,32,0.22)] mt-4">
                Execute Login
            </button>
        </form>
        
        <div class="text-center mt-8 pt-6 border-t border-obsidian-edge">
            <span class="text-obsidian-muted font-mono text-sm uppercase tracking-widest">
                Unregistered Entity? 
                <a href="signup.php" class="text-premium hover:text-black transition-colors ml-2 font-bold">Initialize_Account</a>
            </span>
        </div>
    </div>

</body>
</html>