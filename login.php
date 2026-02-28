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
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Login | ARAI MOTO</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;700;900&family=JetBrains+Mono:wght@400;700&display=swap" rel="stylesheet">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        mono: ['JetBrains Mono', 'monospace'],
                    },
                    colors: {
                        obsidian: {
                            bg: '#020202',
                            surface: '#0a0a0a',
                            edge: 'rgba(255, 0, 0, 0.12)',
                            muted: '#666666'
                        },
                        premium: '#e11d48'
                    }
                }
            }
        }
    </script>

    <style>
        body {
            background-color: #020202;
            background-image: 
                radial-gradient(circle at 0% 0%, rgba(225, 29, 72, 0.12) 0%, transparent 50%),
                radial-gradient(circle at 100% 100%, rgba(225, 29, 72, 0.05) 0%, transparent 50%),
                radial-gradient(rgba(255, 255, 255, 0.04) 1px, transparent 1px);
            background-size: 100% 100%, 100% 100%, 24px 24px;
            background-attachment: fixed;
        }

        @keyframes tectonicRise {
            from { opacity: 0; transform: translateY(40px) scale(0.98); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }
        .anim-load {
            animation: tectonicRise 0.8s forwards ease-out;
            opacity: 0;
        }
    </style>
</head>
<body class="text-white font-sans min-h-screen flex items-center justify-center p-6 selection:bg-premium selection:text-white">

    <div class="w-full max-w-md bg-obsidian-surface border border-obsidian-edge p-10 relative shadow-2xl shadow-black anim-load">
        
        <div class="absolute top-0 right-0 p-2 font-mono text-[8px] text-premium/30 uppercase tracking-widest">
            SECURE_LOGIN_PORTAL
        </div>
        <div class="absolute top-0 left-0 w-8 h-1 bg-premium"></div>

        <h2 class="text-3xl font-black uppercase tracking-tighter mb-2 border-l-4 border-premium pl-4">ARAI <span class="text-premium">MOTO</span></h2>
        <p class="text-obsidian-muted font-mono text-xs mb-8 pl-5 tracking-widest">SYSTEM_AUTHORIZATION</p>
        
        <?php if($message): ?>
            <div class="mb-6 p-4 bg-obsidian-bg border border-red-500 font-mono text-[10px] uppercase tracking-widest text-red-500 animate-pulse">
                [ AUTH_FAILURE: <?= $message ?> ]
            </div>
        <?php endif; ?>

        <form method="POST" action="actions/login_process.php" class="space-y-6">
            <div>
                <label for="email" class="block text-[10px] uppercase tracking-[0.2em] text-obsidian-muted mb-2 font-bold">Operator Email</label>
                <input type="email" id="email" name="email" required placeholder="sys.op@araimoto.com" 
                    class="w-full bg-obsidian-bg border border-obsidian-edge px-4 py-3 text-sm font-mono focus:outline-none focus:border-premium transition-colors text-white">
            </div>
            
            <div>
                <label for="password" class="block text-[10px] uppercase tracking-[0.2em] text-obsidian-muted mb-2 font-bold">Passkey</label>
                <input type="password" id="password" name="password" required placeholder="••••••••" 
                    class="w-full bg-obsidian-bg border border-obsidian-edge px-4 py-3 text-sm font-mono focus:outline-none focus:border-premium transition-colors text-white tracking-widest">
            </div>
            
            <button type="submit" class="w-full py-4 bg-premium text-white text-[10px] font-black uppercase tracking-[0.3em] hover:bg-white hover:text-black transition-all duration-300 shadow-[0_0_20px_rgba(225,29,72,0.2)] mt-4">
                Execute Login
            </button>
        </form>
        
        <div class="text-center mt-8 pt-6 border-t border-obsidian-edge">
            <span class="text-obsidian-muted font-mono text-[10px] uppercase tracking-widest">
                Unregistered Entity? 
                <a href="signup.php" class="text-premium hover:text-white transition-colors ml-2 font-bold">Initialize_Account</a>
            </span>
        </div>
    </div>

</body>
</html>