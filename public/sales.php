<?php
session_start();
require_once "../config/currency.php"; // <-- Added Currency Engine
require_once "../config/database.php"; 

// 1. STRICT SECURITY GUARD
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'employee') {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// 2. Fetch EMPLOYEE profile data
$stmt = $mysqli->prepare("SELECT employee_id, employee_code, name, position FROM employees WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$employeeProfile = $result->fetch_assoc();

$emp_db_id = $employeeProfile['employee_id'] ?? 0;

$name = $employeeProfile['name'] ?? 'UNIDENTIFIED_STAFF';
$position = $employeeProfile['position'] ?? 'UNASSIGNED_UNIT';
$code = $employeeProfile['employee_code'] ?? 'SYS-0000';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Ledger | ARAI MOTO</title>
    
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
                    if ($_GET['status'] == 'initialized') echo "[ STATUS: NEW_PURCHASE_ORDER_INITIALIZED ]";
                    if ($_GET['status'] == 'deleted') echo "[ STATUS: PURCHASE_ORDER_PURGED_FROM_SYSTEM ]";
                    if ($_GET['status'] == 'db_error') echo "<span class='text-red-500'>[ ERROR: DATABASE_SYNC_FAILURE ]</span>";
                ?>
            </div>
        <?php endif; ?>
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
            
            <div class="lg:col-span-1 anim-load" style="animation-delay: 0.1s;">
                <div class="bg-obsidian-surface border border-obsidian-edge p-8 relative overflow-hidden shadow-2xl shadow-black">
                    <div class="absolute top-0 right-0 p-2 font-mono text-[8px] text-premium/30 uppercase tracking-widest">Sys_Op: <?= htmlspecialchars($code) ?></div>
                    
                    <h2 class="text-2xl font-black uppercase tracking-tighter mb-8 border-l-4 border-premium pl-4">Initialize_P.O.</h2>
                    
                    <form action="actions/create_order.php" method="POST" class="space-y-4">
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[10px] uppercase tracking-[0.2em] text-obsidian-muted mb-2 font-bold">Ref ID</label>
                                <div class="w-full bg-obsidian-bg/50 border border-obsidian-edge px-4 py-3 text-[10px] font-mono text-obsidian-muted uppercase cursor-not-allowed text-center">
                                    [ AUTO ]
                                </div>
                            </div>
                            <div>
                                <label class="block text-[10px] uppercase tracking-[0.2em] text-obsidian-muted mb-2 font-bold">PO Ref</label>
                                <div class="w-full bg-obsidian-bg/50 border border-obsidian-edge px-4 py-3 text-[10px] font-mono text-obsidian-muted uppercase cursor-not-allowed text-center">
                                    [ AUTO ]
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-[10px] uppercase tracking-[0.2em] text-obsidian-muted mb-2 font-bold">Select Client Entity</label>
                            <select name="customer_id" required class="w-full bg-obsidian-bg border border-obsidian-edge px-4 py-3 text-sm font-mono focus:outline-none focus:border-premium transition-colors text-white appearance-none">
                                <option value="" disabled selected>-- Select Authorized Client --</option>
                                <?php
                                $clients = $mysqli->query("SELECT customer_id, customer_code, contact_name, membership_level FROM customers ORDER BY contact_name ASC");
                                while ($client = $clients->fetch_assoc()) {
                                    echo "<option value='{$client['customer_id']}'>{$client['contact_name']} [{$client['membership_level']}]</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div>
                            <label class="block text-[10px] uppercase tracking-[0.2em] text-obsidian-muted mb-2 font-bold">
                                Assign Sales Operator
                            </label>

                            <select name="employee_id" required
                                class="w-full bg-obsidian-bg border border-obsidian-edge px-4 py-3 text-sm font-mono focus:outline-none focus:border-premium transition-colors text-white appearance-none">

                                <option value="" disabled selected>-- Select Active Operator --</option>

                                <?php
                                $emps = $mysqli->query("SELECT employee_id, employee_code, name 
                                                        FROM employees 
                                                        WHERE is_active = 1 
                                                        ORDER BY name ASC");

                                while ($empRow = $emps->fetch_assoc()) {
                                    $selected = ($empRow['employee_id'] == $emp_db_id) ? 'selected' : '';
                                    echo "<option value='{$empRow['employee_id']}' $selected>
                                            {$empRow['name']} [{$empRow['employee_code']}]
                                        </option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div>
                            <div class="flex justify-between items-end mb-2">
                                <label class="block text-[10px] uppercase tracking-[0.2em] text-obsidian-muted font-bold">Special Discount (USD Base)</label>
                                <span class="text-[9px] font-mono text-obsidian-muted uppercase tracking-widest">
                                    Format: <span class="text-white"><?= $_SESSION['currency'] ?></span>
                                </span>
                            </div>
                            <input type="number" step="0.01" name="special_discount" value="0.00" 
                                class="w-full bg-obsidian-bg border border-obsidian-edge px-4 py-3 text-sm font-mono focus:outline-none focus:border-premium transition-colors text-white">
                        </div>

                        <button type="submit" class="w-full py-4 bg-premium text-white text-[10px] font-black uppercase tracking-[0.3em] hover:bg-white hover:text-black transition-all duration-300 shadow-[0_0_20px_rgba(225,29,72,0.2)] mt-4">
                            Generate Order Ledger
                        </button>
                    </form>
                </div>
            </div>

            <div class="lg:col-span-2 anim-load" style="animation-delay: 0.2s;">
                <div class="bg-obsidian-surface border border-obsidian-edge overflow-hidden h-full flex flex-col shadow-2xl shadow-black">
                    <div class="px-8 py-6 border-b border-obsidian-edge bg-white/[0.02] flex justify-between items-center">
                        <h2 class="text-xl font-black uppercase tracking-tighter">Sales_Ledger</h2>
                        <span class="text-premium font-mono text-[10px] animate-pulse">[ AWAITING_TRANSACTIONS ]</span>
                    </div>

                    <div class="overflow-x-auto flex-grow">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="text-[10px] uppercase tracking-widest text-obsidian-muted border-b border-obsidian-edge">
                                    <th class="px-6 py-4">Transaction ID</th>
                                    <th class="px-6 py-4">Client</th>
                                    <th class="px-6 py-4">Operator</th>
                                    <th class="px-6 py-4">Financials</th>
                                    <th class="px-6 py-4 text-right">Operations</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-obsidian-edge">
                                <?php
                                $orderQuery = "
                                    SELECT o.*, c.contact_name, c.customer_code, c.membership_level, e.name as employee_name,
                                    (SELECT SUM(quantity) FROM sale_order_details WHERE order_id = o.order_id) as total_items
                                    FROM sale_orders o 
                                    LEFT JOIN customers c ON o.customer_id = c.customer_id 
                                    LEFT JOIN employees e ON o.employee_id = e.employee_id
                                    ORDER BY o.order_date DESC LIMIT 20";
                                
                                $orderResult = $mysqli->query($orderQuery);

                                if ($orderResult && $orderResult->num_rows > 0):
                                    while($order = $orderResult->fetch_assoc()):
                                        $itemCount = (int)$order['total_items'];
                                ?>
                                <tr class="group hover:bg-white/[0.01] transition-colors <?= $itemCount === 0 ? 'opacity-60' : '' ?>">
                                    
                                    <td class="px-6 py-5">
                                        <div class="text-[9px] font-mono text-premium uppercase tracking-widest">
                                            <?= htmlspecialchars($order['reference_id']) ?>
                                        </div>
                                        <div class="text-xs font-bold uppercase text-white mt-1">
                                            <?= htmlspecialchars($order['po_reference']) ?>
                                        </div>
                                        <div class="text-[9px] font-mono text-obsidian-muted mt-1">
                                            <?= date('d M Y - H:i', strtotime($order['order_date'])) ?>
                                        </div>
                                    </td>
                                    
                                    <td class="px-6 py-5">
                                        <div class="text-xs font-bold uppercase text-white">
                                            <?= htmlspecialchars($order['contact_name']) ?>
                                        </div>
                                        <div class="text-[9px] font-mono text-obsidian-muted">
                                            <?= htmlspecialchars($order['customer_code']) ?>
                                        </div>
                                    </td>
                                    
                                    <td class="px-6 py-5 font-mono text-xs">
                                        <div class="text-[9px] font-bold uppercase">
                                            <?= htmlspecialchars($order['employee_name'] ?? 'N/A') ?>
                                        </div>
                                    </td>

                                    <td class="px-6 py-5 font-mono text-xs">
                                        <div class="text-white mb-2 border-b border-obsidian-edge pb-1">
                                            TOTAL: <span class="text-premium font-bold"><?= formatCurrency($order['total_amount']) ?></span>
                                        </div>
                                        <div class="text-[9px] text-obsidian-muted flex flex-col gap-1">
                                            <div>ITEMS: <span class="<?= $itemCount > 0 ? 'text-green-500' : 'text-red-500' ?>"><?= $itemCount ?></span></div>
                                            
                                            <?php if ($order['membership_discount'] > 0): ?>
                                                <div class="text-blue-400">MEM (<?= htmlspecialchars($order['membership_level']) ?>): -<?= formatCurrency($order['membership_discount']) ?></div>
                                            <?php else: ?>
                                                <div>MEM DISC: -<?= formatCurrency(0) ?></div>
                                            <?php endif; ?>

                                            <div>SPL DISC: -<?= formatCurrency($order['special_discount']) ?></div>
                                        </div>
                                    </td>

                                    <td class="px-6 py-5 text-right">
                                        <div class="flex justify-end items-center gap-4 opacity-50 group-hover:opacity-100 transition-opacity">
                                            
                                            <a href="actions/delete_order.php?id=<?= $order['order_id'] ?>" 
                                               onclick="return confirm('WARNING: Purging this order will restore all its items back to inventory and permanently delete the record. Proceed?')"
                                               class="text-[10px] font-black uppercase tracking-widest text-premium hover:underline transition-colors">
                                               Drop
                                            </a>

                                            <a href="order_terminal.php?id=<?= $order['order_id'] ?>" 
                                               class="inline-block px-4 py-2 border <?= $itemCount === 0 ? 'border-red-600 text-red-600 hover:bg-red-600 hover:text-white animate-pulse' : 'border-premium text-premium hover:bg-premium hover:text-white' ?> text-[9px] font-black uppercase tracking-widest transition-colors">
                                                <?= $itemCount === 0 ? 'Add Parts' : 'Modify Cart' ?>
                                            </a>

                                        </div>
                                    </td>

                                </tr>
                                <?php endwhile; else: ?>
                                <tr>
                                    <td colspan="5" class="px-6 py-10 text-center text-obsidian-muted font-mono text-xs">No active purchase orders found.</td>
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