<?php
session_start();
require_once "../config/database.php";

// 1. STRICT SECURITY GUARD
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'employee') {
    header("Location: ../index.php"); 
    exit;
}

$user_id = $_SESSION['user_id'];

// 2. Fetch EMPLOYEE profile data for the header
$stmt = $mysqli->prepare("SELECT employee_code, name, position FROM employees WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$employeeProfile = $result->fetch_assoc();

$name = $employeeProfile['name'] ?? 'UNIDENTIFIED_STAFF';
$position = $employeeProfile['position'] ?? 'UNASSIGNED_UNIT';
$code = $employeeProfile['employee_code'] ?? 'SYS-0000';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Ledger | ARAI MOTO</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;700;900&family=JetBrains+Mono:wght@400;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'], mono: ['JetBrains Mono', 'monospace'] },
                    colors: {
                        obsidian: { bg: '#020202', surface: '#0a0a0a', edge: 'rgba(255, 0, 0, 0.12)', muted: '#666666' },
                        premium: '#e11d48'
                    }
                }
            }
        }
    </script>

    <style>
        body {

            background-color: #020202;



            /* Multi-layered cinematic background */

            background-image:



                /* Top-left red plasma glow */

                radial-gradient(circle at 0% 0%, rgba(225, 29, 72, 0.18) 0%, transparent 55%),



                /* Bottom-right blue tech glow */

                radial-gradient(circle at 100% 100%, rgba(255, 255, 255, 0.12) 0%, transparent 60%),



                /* Center ambient energy */

                radial-gradient(circle at 50% 40%, rgba(168, 85, 247, 0.05) 0%, transparent 65%),



                /* Dot grid matrix */

                radial-gradient(rgba(255, 255, 255, 0.05) 1px, transparent 1px);



            background-size:

                100% 100%,

                100% 100%,

                100% 100%,

                26px 26px;



            background-attachment: fixed;

        }

        @keyframes tectonicRise {
            from { opacity: 0; transform: translateY(40px) scale(0.98); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }
        .anim-load { animation: tectonicRise 0.8s forwards ease-out; opacity: 0; }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #020202; }
        ::-webkit-scrollbar-thumb { background: #e11d48; border-radius: 10px; }
    </style>
</head>

<body class="bg-obsidian-bg text-white font-sans min-h-screen overflow-x-hidden selection:bg-premium selection:text-white">

    <?php include 'partials/nav.php'; ?>

    <main class="max-w-[1400px] mx-auto py-20 px-8">

        <?php if (isset($_GET['status'])): ?>
            <div class="mb-6 p-4 bg-obsidian-surface border-l-4 border-premium font-mono text-[10px] uppercase tracking-widest anim-load">
                <?php 
                    if ($_GET['status'] == 'registered') echo "[ STATUS: NEW_CLIENT_REGISTERED ]";
                    if ($_GET['status'] == 'updated') echo "[ STATUS: CLIENT_DATA_RECALIBRATED ]";
                    if ($_GET['status'] == 'deleted') echo "[ STATUS: CLIENT_PURGED_FROM_SYSTEM ]";
                    if ($_GET['status'] == 'db_error') echo "<span class='text-red-500'>[ ERROR: DATABASE_SYNC_FAILURE ]</span>";
                ?>
            </div>
        <?php endif; ?>
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
            
            <div class="lg:col-span-1 anim-load" style="animation-delay: 0.1s;">
                <div class="bg-obsidian-surface border border-obsidian-edge p-8 relative overflow-hidden">
                    <div class="absolute top-0 right-0 p-2 font-mono text-[8px] text-premium/30 uppercase tracking-widest">Client_Access: Granted</div>
                    
                    <h2 class="text-2xl font-black uppercase tracking-tighter mb-8 border-l-4 border-premium pl-4">Register_Client</h2>
                    
                    <form action="actions/add_customer.php" method="POST" class="space-y-4">
                        
                        <div>
                            <label class="block text-[10px] uppercase tracking-[0.2em] text-obsidian-muted mb-2 font-bold">Client Identity</label>
                            <input type="text" name="contact_name" required placeholder="e.g. RYU MOTORS" 
                                class="w-full bg-obsidian-bg border border-obsidian-edge px-4 py-3 text-sm font-bold focus:outline-none focus:border-premium transition-colors text-white uppercase">
                        </div>

                        <div>
                            <label class="block text-[10px] uppercase tracking-[0.2em] text-obsidian-muted mb-2 font-bold">Membership Tier</label>
                            <select name="membership_level" class="w-full bg-obsidian-bg border border-obsidian-edge px-4 py-3 text-sm font-mono focus:outline-none focus:border-premium transition-colors text-white appearance-none">
                                <option value="STANDARD">Standard</option>
                                <option value="PREMIUM">Premium</option>
                                <option value="ELITE">Elite</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-[10px] uppercase tracking-[0.2em] text-obsidian-muted mb-2 font-bold">Base Location / Address</label>
                            <textarea name="address" rows="3" required placeholder="Enter primary location..."
                                class="w-full bg-obsidian-bg border border-obsidian-edge px-4 py-3 text-xs font-mono focus:outline-none focus:border-premium transition-colors text-white resize-none"></textarea>
                        </div>

                        <button type="submit" class="w-full py-4 bg-premium text-white text-[10px] font-black uppercase tracking-[0.3em] hover:bg-white hover:text-black transition-all duration-300 shadow-[0_0_20px_rgba(225,29,72,0.2)]">
                            Initialize Client
                        </button>
                    </form>
                </div>
            </div>

            <div class="lg:col-span-2 anim-load" style="animation-delay: 0.2s;">
                <div class="bg-obsidian-surface border border-obsidian-edge overflow-hidden h-full flex flex-col">
                    <div class="px-8 py-6 border-b border-obsidian-edge bg-white/[0.02] flex justify-between items-center">
                        <h2 class="text-xl font-black uppercase tracking-tighter">Client_Ledger</h2>
                        <span class="text-premium font-mono text-[10px] animate-pulse">[ NETWORK_SYNC_ACTIVE ]</span>
                    </div>

                    <div class="overflow-x-auto flex-grow">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="text-[10px] uppercase tracking-widest text-obsidian-muted border-b border-obsidian-edge">
                                    <th class="px-6 py-4">Client Data</th>
                                    <th class="px-6 py-4">Location</th>
                                    <th class="px-6 py-4">Tier</th>
                                    <th class="px-6 py-4 text-right">Operations</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-obsidian-edge">
                                <?php
                                $customerQuery = "SELECT * FROM customers ORDER BY created_at DESC";
                                $customerResult = $mysqli->query($customerQuery);

                                if ($customerResult->num_rows > 0):
                                    while($customer = $customerResult->fetch_assoc()):
                                ?>
                                <tr class="group hover:bg-white/[0.01] transition-colors">
                                    
                                    <td class="px-6 py-5">
                                        <div class="text-[9px] font-mono text-premium uppercase tracking-widest">
                                            <?= htmlspecialchars($customer['customer_code']) ?>
                                        </div>
                                        <div class="text-sm font-bold uppercase text-white group-hover:text-premium transition-colors">
                                            <?= htmlspecialchars($customer['contact_name']) ?>
                                        </div>
                                        <div class="text-[9px] font-mono text-obsidian-muted mt-1">
                                            UID: #<?= str_pad($customer['customer_id'], 4, '0', STR_PAD_LEFT) ?>
                                        </div>
                                    </td>
                                    
                                    <td class="px-6 py-5">
                                        <div class="text-[10px] font-mono text-obsidian-muted line-clamp-2 max-w-[200px]">
                                            <?= htmlspecialchars($customer['address']) ?>
                                        </div>
                                    </td>

                                    <td class="px-6 py-5">
                                        <?php if($customer['membership_level'] === 'PREMIUM' || $customer['membership_level'] === 'ELITE'): ?>
                                            <span class="inline-flex items-center gap-2 px-3 py-1 bg-premium/10 border border-premium/30 text-premium text-[9px] font-black uppercase tracking-widest shadow-[0_0_10px_rgba(225,29,72,0.1)]">
                                                <?= htmlspecialchars($customer['membership_level']) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center gap-2 px-3 py-1 bg-white/5 border border-white/10 text-obsidian-muted text-[9px] font-black uppercase tracking-widest">
                                                STANDARD
                                            </span>
                                        <?php endif; ?>
                                    </td>

                                    <td class="px-6 py-5 text-right">
                                        <div class="flex justify-end gap-3 opacity-30 group-hover:opacity-100 transition-opacity">
                                            <a href="edit_customer.php?id=<?= $customer['customer_id'] ?>" class="text-[10px] font-black uppercase tracking-widest text-white/50 hover:text-white transition-colors">Edit</a>
                                            <a href="actions/delete_customer.php?id=<?= $customer['customer_id'] ?>" 
                                            onclick="return confirm('Purge this client from the database?')"
                                            class="text-[10px] font-black uppercase tracking-widest text-premium/50 hover:text-premium transition-colors">Purge</a>
                                        </div>
                                    </td>

                                </tr>
                                <?php endwhile; else: ?>
                                <tr>
                                    <td colspan="4" class="px-6 py-10 text-center text-obsidian-muted font-mono text-xs">No clients found in ledger.</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </main>

</body>
</html>