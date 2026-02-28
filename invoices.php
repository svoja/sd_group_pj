<?php
session_start();
require_once "config/database.php";
require_once "config/currency.php"; // Currency engine loaded

if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'employee') {
    header("Location: ../index.php"); 
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $mysqli->prepare("SELECT employee_code, name, position FROM employees WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$employeeProfile = $stmt->get_result()->fetch_assoc();
$code = $employeeProfile['employee_code'] ?? 'SYS-0000';

// Check if an order is selected for preview
$selected_order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
$previewOrder = null;
$previewItems = null;

if ($selected_order_id > 0) {
    // Fetch Order Data
    $orderQuery = "SELECT o.*, c.contact_name, c.customer_code, c.membership_level 
                   FROM sale_orders o 
                   JOIN customers c ON o.customer_id = c.customer_id 
                   WHERE o.order_id = ?";
    $stmt = $mysqli->prepare($orderQuery);
    $stmt->bind_param("i", $selected_order_id);
    $stmt->execute();
    $previewOrder = $stmt->get_result()->fetch_assoc();

    // Fetch Order Items
    if ($previewOrder) {
        $itemQuery = "SELECT sod.*, p.product_code, p.product_name 
                      FROM sale_order_details sod 
                      JOIN products p ON sod.product_id = p.product_id 
                      WHERE sod.order_id = ?";
        $stmt = $mysqli->prepare($itemQuery);
        $stmt->bind_param("i", $selected_order_id);
        $stmt->execute();
        $previewItems = $stmt->get_result();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice Generator | ARAI MOTO</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;700;900&family=JetBrains+Mono:wght@400;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: { fontFamily: { sans: ['Inter', 'sans-serif'], mono: ['JetBrains Mono', 'monospace'] }, colors: { obsidian: { bg: '#020202', surface: '#0a0a0a', edge: 'rgba(255, 0, 0, 0.12)', muted: '#666666' }, premium: '#e11d48' } }
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
        
        @keyframes tectonicRise { from { opacity: 0; transform: translateY(40px) scale(0.98); } to { opacity: 1; transform: translateY(0) scale(1); } }
        .anim-load { animation: tectonicRise 0.8s forwards ease-out; opacity: 0; }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #020202; }
        ::-webkit-scrollbar-thumb { background: #e11d48; border-radius: 10px; }
    </style>
</head>

<body class="bg-obsidian-bg text-white font-sans min-h-screen flex flex-col selection:bg-premium selection:text-white">

    <?php include 'partials/nav.php'; ?>

    <main class="flex-grow max-w-[1400px] mx-auto w-full py-10 px-8 flex flex-col gap-10">

        <?php if (isset($_GET['status'])): ?>
            <div class="p-4 bg-obsidian-surface border-l-4 border-premium font-mono text-[10px] uppercase tracking-widest anim-load shadow-2xl shadow-black">
                <?php 
                    if ($_GET['status'] == 'generated') echo "[ SYS: INVOICE_SUCCESSFULLY_GENERATED ]";
                    if ($_GET['status'] == 'updated') echo "[ SYS: INVOICE_DATA_RECALIBRATED ]";
                    if ($_GET['status'] == 'deleted') echo "[ SYS: INVOICE_PURGED_FROM_LEDGER ]";
                    if ($_GET['status'] == 'db_error') echo "<span class='text-red-500'>[ ERROR: DATABASE_SYNC_FAILURE ]</span>";
                ?>
            </div>
        <?php endif; ?>

        <div class="flex flex-col lg:flex-row gap-10">
            <div class="lg:w-1/3 flex flex-col gap-6 anim-load" style="animation-delay: 0.1s;">
                <div class="bg-obsidian-surface border border-obsidian-edge p-6 relative flex-grow shadow-2xl shadow-black">
                    <div class="absolute top-0 right-0 p-2 font-mono text-[8px] text-premium/30 uppercase tracking-widest">Sys_Op: <?= htmlspecialchars($code) ?></div>
                    <h2 class="text-2xl font-black uppercase tracking-tighter mb-8 border-l-4 border-premium pl-4">Generate_Invoice</h2>

                    <form method="GET" action="invoices.php" class="mb-6 border-b border-obsidian-edge pb-6">
                        <label class="block text-[10px] uppercase tracking-[0.2em] text-obsidian-muted mb-2 font-bold">Target P.O. Reference</label>
                        <select name="order_id" onchange="this.form.submit()" class="w-full bg-obsidian-bg border border-obsidian-edge px-4 py-3 text-sm font-mono focus:outline-none focus:border-premium text-white appearance-none">
                            <option value="0" <?= $selected_order_id == 0 ? 'selected' : '' ?>>-- Select Pending Order --</option>
                            <?php
                            $pendingOrders = $mysqli->query("
                                SELECT o.order_id, o.po_reference, c.contact_name 
                                FROM sale_orders o 
                                JOIN customers c ON o.customer_id = c.customer_id 
                                LEFT JOIN invoices i ON o.order_id = i.order_id 
                                WHERE i.invoice_id IS NULL 
                                ORDER BY o.order_id DESC");
                            
                            while ($po = $pendingOrders->fetch_assoc()) {
                                $sel = ($selected_order_id == $po['order_id']) ? 'selected' : '';
                                echo "<option value='{$po['order_id']}' $sel>{$po['po_reference']} - {$po['contact_name']}</option>";
                            }
                            ?>
                        </select>
                    </form>

                    <form action="actions/create_invoice.php" method="POST" class="space-y-4 <?= !$previewOrder ? 'opacity-30 pointer-events-none' : '' ?>">
                        <input type="hidden" name="order_id" value="<?= $selected_order_id ?>">

                        <div>
                            <label class="block text-[10px] uppercase tracking-[0.2em] text-obsidian-muted mb-2 font-bold">Invoice Date</label>
                            <input type="date" name="invoice_date" value="<?= date('Y-m-d') ?>" required 
                                   class="w-full bg-obsidian-bg border border-obsidian-edge px-4 py-3 text-sm font-mono focus:outline-none focus:border-premium text-white">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[10px] uppercase tracking-[0.2em] text-obsidian-muted mb-2 font-bold">Payment Method</label>
                                <select name="payment_method" required class="w-full bg-obsidian-bg border border-obsidian-edge px-4 py-3 text-sm font-mono focus:outline-none focus:border-premium text-white appearance-none">
                                    <option value="CASH">Cash</option>
                                    <option value="CARD">Credit/Debit Card</option>
                                    <option value="TRANSFER">Bank Transfer</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[10px] uppercase tracking-[0.2em] text-obsidian-muted mb-2 font-bold">Payment Status</label>
                                <select name="payment_status" required class="w-full bg-obsidian-bg border border-obsidian-edge px-4 py-3 text-sm font-mono focus:outline-none focus:border-premium text-white appearance-none">
                                    <option value="PENDING">Pending</option>
                                    <option value="PAID">Paid</option>
                                </select>
                            </div>
                        </div>

                        <div class="flex gap-4 pt-4 mt-8">
                            <a href="invoices.php" class="w-1/3 text-center py-4 border border-obsidian-edge text-obsidian-muted text-[10px] font-black uppercase tracking-[0.2em] hover:text-white hover:border-white transition-colors">
                                Reset
                            </a>
                            <button type="submit" class="w-2/3 py-4 bg-premium text-white text-[10px] font-black uppercase tracking-[0.3em] hover:bg-white hover:text-black transition-all shadow-[0_0_20px_rgba(225,29,72,0.2)]">
                                Generate Invoice
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="lg:w-2/3 flex flex-col anim-load" style="animation-delay: 0.2s;">
                <div class="bg-obsidian-surface border border-obsidian-edge flex-grow flex flex-col shadow-2xl shadow-black">
                    <div class="px-8 py-6 border-b border-obsidian-edge bg-white/[0.02] flex justify-between items-center">
                        <h2 class="text-xl font-black uppercase tracking-tighter">Order_Preview</h2>
                        <span class="text-premium font-mono text-[10px] animate-pulse">[ <?= $previewOrder ? 'DATA_LOCKED' : 'AWAITING_SELECTION' ?> ]</span>
                    </div>

                    <?php if ($previewOrder): ?>
                        <div class="p-6 border-b border-obsidian-edge bg-obsidian-bg/50 grid grid-cols-3 gap-4">
                            <div>
                                <p class="text-[10px] uppercase tracking-widest text-obsidian-muted mb-1">Client Entity</p>
                                <p class="font-bold uppercase"><?= htmlspecialchars($previewOrder['contact_name']) ?></p>
                                <p class="text-xs font-mono text-premium mt-1"><?= htmlspecialchars($previewOrder['customer_code']) ?></p>
                            </div>
                            <div>
                                <p class="text-[10px] uppercase tracking-widest text-obsidian-muted mb-1">P.O. Reference</p>
                                <p class="font-bold uppercase text-white"><?= htmlspecialchars($previewOrder['po_reference']) ?></p>
                                <p class="text-xs font-mono text-obsidian-muted mt-1"><?= date('d M Y', strtotime($previewOrder['order_date'])) ?></p>
                            </div>
                            <div class="text-right">
                                <p class="text-[10px] uppercase tracking-widest text-obsidian-muted mb-1">Final Authorization Math</p>
                                <p class="text-2xl font-black text-white"><?= formatCurrency($previewOrder['total_amount']) ?></p>
                            </div>
                        </div>

                        <div class="overflow-x-auto flex-grow p-4">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="text-[10px] uppercase tracking-widest text-obsidian-muted border-b border-obsidian-edge">
                                        <th class="px-4 py-3">Component</th>
                                        <th class="px-4 py-3 text-center">Qty</th>
                                        <th class="px-4 py-3 text-right">Unit Price</th>
                                        <th class="px-4 py-3 text-right">Total Price</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-obsidian-edge">
                                    <?php while($item = $previewItems->fetch_assoc()): ?>
                                    <tr class="hover:bg-white/[0.02] transition-colors">
                                        <td class="px-4 py-4">
                                            <div class="text-[9px] font-mono text-premium uppercase tracking-widest"><?= htmlspecialchars($item['product_code']) ?></div>
                                            <div class="text-xs font-bold uppercase text-white"><?= htmlspecialchars($item['product_name']) ?></div>
                                        </td>
                                        <td class="px-4 py-4 text-center font-mono text-xs"><?= $item['quantity'] ?></td>
                                        <td class="px-4 py-4 text-right font-mono text-xs text-obsidian-muted"><?= formatCurrency($item['unit_price']) ?></td>
                                        <td class="px-4 py-4 text-right font-mono text-sm font-bold text-white"><?= formatCurrency($item['total_price']) ?></td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="flex-grow flex items-center justify-center p-10 text-center">
                            <p class="text-obsidian-muted font-mono text-xs uppercase tracking-[0.3em]">Select a Purchase Order to view manifest data.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="anim-load" style="animation-delay: 0.3s;">
            <div class="bg-obsidian-surface border border-obsidian-edge flex flex-col shadow-2xl shadow-black">
                <div class="px-8 py-6 border-b border-obsidian-edge bg-white/[0.02] flex justify-between items-center">
                    <h2 class="text-xl font-black uppercase tracking-tighter">Invoice_Ledger</h2>
                    <span class="text-obsidian-muted font-mono text-[10px] uppercase tracking-widest">[ HISTORICAL_RECORDS ]</span>
                </div>

                <div class="overflow-x-auto p-4">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="text-[10px] uppercase tracking-widest text-obsidian-muted border-b border-obsidian-edge">
                                <th class="px-4 py-3">Invoice Details</th>
                                <th class="px-4 py-3">Client / Source P.O.</th>
                                <th class="px-4 py-3 text-right">Financials</th>
                                <th class="px-4 py-3 text-center">Status</th>
                                <th class="px-4 py-3 text-right">Operations</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-obsidian-edge">
                            <?php
                            $invQuery = "
                                SELECT i.*, o.po_reference, c.contact_name 
                                FROM invoices i 
                                JOIN sale_orders o ON i.order_id = o.order_id 
                                JOIN customers c ON i.customer_id = c.customer_id 
                                ORDER BY i.invoice_date DESC, i.invoice_id DESC";
                            $invResult = $mysqli->query($invQuery);

                            if ($invResult && $invResult->num_rows > 0):
                                while($inv = $invResult->fetch_assoc()):
                            ?>
                            <tr class="hover:bg-white/[0.02] transition-colors group">
                                <td class="px-4 py-4">
                                    <div class="text-[10px] font-mono text-premium uppercase tracking-widest"><?= htmlspecialchars($inv['invoice_reference']) ?></div>
                                    <div class="text-[9px] font-mono text-obsidian-muted mt-1"><?= date('d M Y', strtotime($inv['invoice_date'])) ?></div>
                                </td>
                                
                                <td class="px-4 py-4">
                                    <div class="text-xs font-bold uppercase text-white"><?= htmlspecialchars($inv['contact_name']) ?></div>
                                    <div class="text-[9px] font-mono text-obsidian-muted mt-1">PO: <?= htmlspecialchars($inv['po_reference']) ?></div>
                                </td>
                                
                                <td class="px-4 py-4 text-right font-mono">
                                    <div class="text-sm font-bold text-white"><?= formatCurrency($inv['total_amount']) ?></div>
                                    <div class="text-[9px] text-obsidian-muted uppercase tracking-widest mt-1">[ <?= htmlspecialchars($inv['payment_method']) ?> ]</div>
                                </td>

                                <td class="px-4 py-4 text-center">
                                    <?php if($inv['payment_status'] === 'PAID'): ?>
                                        <span class="inline-flex items-center gap-2 px-3 py-1 bg-green-500/10 border border-green-500/20 text-green-500 text-[9px] font-black uppercase tracking-widest">
                                            PAID
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center gap-2 px-3 py-1 bg-yellow-500/10 border border-yellow-500/20 text-yellow-500 text-[9px] font-black uppercase tracking-widest animate-pulse">
                                            PENDING
                                        </span>
                                    <?php endif; ?>
                                </td>

                                <td class="px-4 py-4 text-right">
                                    <div class="flex justify-end gap-4 opacity-50 group-hover:opacity-100 transition-opacity">
                                        <a href="print_invoice.php?id=<?= $inv['invoice_id'] ?>" target="_blank" class="text-[10px] font-black uppercase tracking-widest hover:text-white transition-colors">
                                            Print
                                        </a>
                                        
                                        <a href="edit_invoice.php?id=<?= $inv['invoice_id'] ?>" class="text-[10px] font-black uppercase tracking-widest hover:text-white transition-colors">
                                            Edit
                                        </a>

                                        <a href="actions/delete_invoice.php?id=<?= $inv['invoice_id'] ?>" 
                                           onclick="return confirm('Purge this invoice? This will NOT delete the original Purchase Order.')"
                                           class="text-[10px] font-black uppercase tracking-widest text-premium hover:underline transition-colors">
                                           Drop
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; else: ?>
                            <tr>
                                <td colspan="5" class="px-4 py-10 text-center text-obsidian-muted font-mono text-xs">No invoices found in ledger.</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </main>
</body>
</html>