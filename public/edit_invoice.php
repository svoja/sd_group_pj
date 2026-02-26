<?php
session_start();
require_once "../config/database.php";

// Security Check
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'employee') {
    header("Location: ../index.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: invoices.php");
    exit;
}

$invoice_id = intval($_GET['id']);

// Fetch the invoice along with context from the order and customer
$query = "
    SELECT i.*, o.po_reference, c.contact_name, c.customer_code 
    FROM invoices i
    JOIN sale_orders o ON i.order_id = o.order_id
    JOIN customers c ON i.customer_id = c.customer_id
    WHERE i.invoice_id = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("i", $invoice_id);
$stmt->execute();
$invoice = $stmt->get_result()->fetch_assoc();

if (!$invoice) {
    header("Location: invoices.php?status=db_error");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update Invoice | ARAI MOTO</title>
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
        body { background-image: radial-gradient(circle at 20% 30%, rgba(225, 29, 72, 0.03) 0%, transparent 40%), radial-gradient(circle at 80% 70%, rgba(59, 130, 246, 0.03) 0%, transparent 40%); }
    </style>
</head>
<body class="bg-obsidian-bg text-white font-sans min-h-screen flex flex-col selection:bg-premium selection:text-white">

    <?php include 'partials/nav.php'; ?>

    <main class="flex-grow flex items-center justify-center p-8">
        <div class="w-full max-w-xl bg-obsidian-surface border border-obsidian-edge p-8 relative shadow-2xl">
            <div class="absolute top-0 right-0 p-2 font-mono text-[8px] text-premium/30 uppercase tracking-widest">Sys_Edit: Active</div>
            
            <h2 class="text-2xl font-black uppercase tracking-tighter mb-2 border-l-4 border-premium pl-4">Update_Invoice</h2>
            <p class="text-obsidian-muted font-mono text-xs mb-6 pl-5">Target Reference: <span class="text-premium"><?= htmlspecialchars($invoice['invoice_reference']) ?></span></p>
            
            <div class="mb-8 p-4 bg-white/[0.02] border border-obsidian-edge grid grid-cols-2 gap-4 font-mono text-xs">
                <div>
                    <span class="text-obsidian-muted block text-[9px] uppercase tracking-widest mb-1">Client</span>
                    <span class="text-white font-bold uppercase"><?= htmlspecialchars($invoice['contact_name']) ?></span>
                </div>
                <div>
                    <span class="text-obsidian-muted block text-[9px] uppercase tracking-widest mb-1">Source P.O.</span>
                    <span class="text-white uppercase"><?= htmlspecialchars($invoice['po_reference']) ?></span>
                </div>
                <div class="col-span-2 border-t border-obsidian-edge pt-3 mt-1">
                    <span class="text-obsidian-muted block text-[9px] uppercase tracking-widest mb-1">Final Amount</span>
                    <span class="text-premium font-black text-lg">$<?= number_format($invoice['total_amount'], 2) ?></span>
                </div>
            </div>

            <form action="actions/update_invoice.php" method="POST" class="space-y-6">
                <input type="hidden" name="invoice_id" value="<?= $invoice_id ?>">

                <div>
                    <label class="block text-[10px] uppercase tracking-[0.2em] text-obsidian-muted mb-2 font-bold">Invoice Date</label>
                    <input type="date" name="invoice_date" required value="<?= htmlspecialchars($invoice['invoice_date']) ?>" 
                           class="w-full bg-obsidian-bg border border-obsidian-edge px-4 py-3 text-sm font-mono focus:outline-none focus:border-premium transition-colors text-white">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] uppercase tracking-[0.2em] text-obsidian-muted mb-2 font-bold">Payment Method</label>
                        <select name="payment_method" required class="w-full bg-obsidian-bg border border-obsidian-edge px-4 py-3 text-sm font-mono focus:outline-none focus:border-premium text-white appearance-none">
                            <option value="CASH" <?= $invoice['payment_method'] === 'CASH' ? 'selected' : '' ?>>Cash</option>
                            <option value="CARD" <?= $invoice['payment_method'] === 'CARD' ? 'selected' : '' ?>>Credit/Debit Card</option>
                            <option value="TRANSFER" <?= $invoice['payment_method'] === 'TRANSFER' ? 'selected' : '' ?>>Bank Transfer</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] uppercase tracking-[0.2em] text-obsidian-muted mb-2 font-bold">Status</label>
                        <select name="payment_status" required class="w-full bg-obsidian-bg border border-obsidian-edge px-4 py-3 text-sm font-mono focus:outline-none focus:border-premium text-white appearance-none">
                            <option value="PENDING" <?= $invoice['payment_status'] === 'PENDING' ? 'selected' : '' ?>>Pending</option>
                            <option value="PAID" <?= $invoice['payment_status'] === 'PAID' ? 'selected' : '' ?>>Paid</option>
                        </select>
                    </div>
                </div>

                <div class="flex gap-4 pt-4 mt-8">
                    <a href="invoices.php" class="w-1/3 text-center py-4 border border-obsidian-edge text-obsidian-muted text-[10px] font-black uppercase tracking-[0.2em] hover:text-white hover:border-white transition-colors">
                        Abort
                    </a>
                    <button type="submit" class="w-2/3 py-4 bg-premium text-white text-[10px] font-black uppercase tracking-[0.3em] hover:bg-white hover:text-black transition-all shadow-[0_0_20px_rgba(225,29,72,0.2)]">
                        Execute Update
                    </button>
                </div>
            </form>
        </div>
    </main>
</body>
</html>