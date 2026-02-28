<?php
session_start();
require_once "config/currency.php"; // <-- ADDED CURRENCY ENGINE
require_once "config/database.php";

// Strict Security Guard
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'employee') {
    header("Location: ../index.php"); 
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: sales.php");
    exit;
}

$order_id = intval($_GET['id']);

// 1. Fetch Master Order Data (Using sale_orders based on your schema)
$orderQuery = "
    SELECT o.*, c.contact_name, c.customer_code, c.membership_level, e.name AS employee_name
    FROM sale_orders o 
    JOIN customers c ON o.customer_id = c.customer_id 
    LEFT JOIN employees e ON o.employee_id = e.employee_id
    WHERE o.order_id = ?";
$stmt = $mysqli->prepare($orderQuery);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    header("Location: sales.php?status=db_error");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Terminal | ARAI MOTO</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;700;900&family=JetBrains+Mono:wght@400;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'], mono: ['JetBrains Mono', 'monospace'] },
                    colors: { obsidian: { bg: '#020202', surface: '#0a0a0a', edge: 'rgba(255, 0, 0, 0.12)', muted: '#666666' }, premium: '#e11d48' }
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
        
        @keyframes tectonicRise { from { opacity: 0; transform: translateY(40px) scale(0.98); } to { opacity: 1; transform: translateY(0) scale(1); } }
        .anim-load { animation: tectonicRise 0.8s forwards ease-out; opacity: 0; }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #020202; }
        ::-webkit-scrollbar-thumb { background: #e11d48; border-radius: 10px; }
        /* NEW: Scrolling Marquee Animation */
        @keyframes scroll-left {
            0% { transform: translateX(100%); }
            100% { transform: translateX(-100%); }
        }
        .animate-marquee {
            display: inline-block;
            white-space: nowrap;
            animation: scroll-left 28s linear infinite;
        }
    </style>
</head>

<body class="bg-obsidian-bg text-white font-sans min-h-screen flex flex-col selection:bg-premium selection:text-white">

    <?php include 'partials/nav.php'; ?>

    <main class="flex-grow max-w-[1400px] mx-auto w-full py-10 px-8 flex flex-col lg:flex-row gap-10">

        <div class="lg:w-1/3 flex flex-col gap-6 anim-load" style="animation-delay: 0.1s;">
            
            <div class="bg-obsidian-surface border border-obsidian-edge p-6 relative shadow-2xl shadow-black">
                <div class="absolute top-0 right-0 p-2 font-mono text-[8px] text-premium/30 uppercase tracking-widest">Active_Cart</div>
                <h2 class="text-xl font-black uppercase tracking-tighter mb-4 border-l-4 border-premium pl-4">Order_Manifest</h2>
                
                <div class="font-mono text-xs space-y-2 text-obsidian-muted">
                    <p>PO REF: <span class="text-white"><?= htmlspecialchars($order['po_reference']) ?></span></p>
                    <p>CLIENT: <span class="text-white"><?= htmlspecialchars($order['contact_name']) ?> [<?= htmlspecialchars($order['membership_level']) ?>]</span></p>
                    <p>DATE: <span class="text-white"><?= date('d M Y', strtotime($order['order_date'])) ?></span></p>
                    <p>STAFF: <span class="text-white"><?= htmlspecialchars($order['employee_name'] ?? 'UNIDENTIFIED') ?></span></p>
                </div>
            </div>

            <?php if (isset($_GET['status'])): ?>
                <div class="p-4 bg-obsidian-bg border border-obsidian-edge font-mono text-[10px] uppercase tracking-widest text-premium shadow-2xl shadow-black">
                    <?php 
                        if ($_GET['status'] == 'item_added') echo "[ SYS: COMPONENT_APPENDED ]";
                        if ($_GET['status'] == 'item_updated') echo "[ SYS: QUANTITY_RECALIBRATED ]";
                        if ($_GET['status'] == 'item_removed') echo "[ SYS: COMPONENT_PURGED ]";
                        if ($_GET['status'] == 'stock_error') echo "<span class='text-red-500'>[ ERROR: INSUFFICIENT_WAREHOUSE_STOCK ]</span>";
                    ?>
                </div>
            <?php endif; ?>

            <div class="bg-obsidian-surface border border-obsidian-edge p-6 flex-grow shadow-2xl shadow-black">
                <h3 class="text-sm font-black uppercase tracking-widest mb-6 text-premium">Add_Component</h3>
                
                <form action="actions/add_order_item.php" method="POST" class="space-y-4">
                    <input type="hidden" name="order_id" value="<?= $order_id ?>">

                    <div>
                        <div class="flex justify-between items-end mb-2">
                            <label class="block text-[10px] uppercase tracking-[0.2em] text-obsidian-muted font-bold">Select Component</label>
                            <span class="text-[9px] font-mono text-obsidian-muted uppercase tracking-widest">Format: <span class="text-white"><?= $_SESSION['currency'] ?></span></span>
                        </div>
                        <select name="product_id" required class="w-full bg-obsidian-bg border border-obsidian-edge px-4 py-3 text-xs font-mono focus:outline-none focus:border-premium transition-colors text-white appearance-none">
                            <option value="" disabled selected>-- Inventory Matrix --</option>
                            <?php
                            $products = $mysqli->query("SELECT product_id, product_code, product_name, selling_price, stock_qty FROM products WHERE stock_qty > 0 ORDER BY product_name ASC");
                            while ($p = $products->fetch_assoc()) {
                                // Updated to use formatCurrency() in the dropdown
                                echo "<option value='{$p['product_id']}'>{$p['product_code']} - {$p['product_name']} [Q: {$p['stock_qty']}] (" . formatCurrency($p['selling_price']) . ")</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[10px] uppercase tracking-[0.2em] text-obsidian-muted mb-2 font-bold">Quantity</label>
                        <input type="number" name="quantity" min="1" value="1" required 
                               class="w-full bg-obsidian-bg border border-obsidian-edge px-4 py-3 text-sm font-bold focus:outline-none focus:border-premium transition-colors text-white">
                    </div>

                    <button type="submit" class="w-full py-4 bg-premium text-white text-[10px] font-black uppercase tracking-[0.3em] hover:bg-white hover:text-black transition-all duration-300">
                        Append to Manifest
                    </button>
                </form>
            </div>

            <div class="bg-premium/10 border border-premium overflow-hidden p-3 shadow-[0_0_15px_rgba(225,29,72,0.2)]">
                <div class="w-full overflow-hidden">
                    <div class="animate-marquee font-mono text-xs font-black text-premium uppercase tracking-[0.2em]">
                        >>> ACTIVE PROMOTION: BUY 19,000 BAHT GET 10% DISCOUNT ON FINAL SALE!! <<<
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        >>> ACTIVE PROMOTION: BUY 19,000 BAHT GET 10% DISCOUNT ON FINAL SALE!! <<<
                    </div>
                </div>
            </div>
        </div>

        <div class="lg:w-2/3 flex flex-col anim-load" style="animation-delay: 0.2s;">
            <div class="bg-obsidian-surface border border-obsidian-edge flex-grow flex flex-col shadow-2xl shadow-black">
                <div class="px-8 py-6 border-b border-obsidian-edge bg-white/[0.02] flex justify-between items-center">
                    <h2 class="text-xl font-black uppercase tracking-tighter">Acquisition_List</h2>
                    <a href="sales.php" class="text-[10px] font-black uppercase tracking-widest text-obsidian-muted hover:text-white transition-colors"><- Back to Ledger</a>
                </div>

                <div class="overflow-x-auto flex-grow p-4">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="text-[10px] uppercase tracking-widest text-obsidian-muted border-b border-obsidian-edge">
                                <th class="px-4 py-3">Component</th>
                                <th class="px-4 py-3">Qty</th>
                                <th class="px-4 py-3 text-right">Unit Price</th>
                                <th class="px-4 py-3 text-right">Total Price</th>
                                <th class="px-4 py-3 text-right">Operations</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-obsidian-edge">
                            <?php
                            $detailsQuery = "
                                SELECT sod.*, p.product_code, p.product_name 
                                FROM sale_order_details sod 
                                JOIN products p ON sod.product_id = p.product_id 
                                WHERE sod.order_id = ? ORDER BY sod.detail_id DESC";
                            
                            $stmt = $mysqli->prepare($detailsQuery);
                            $stmt->bind_param("i", $order_id);
                            $stmt->execute();
                            $items = $stmt->get_result();

                            if ($items->num_rows > 0):
                                while($item = $items->fetch_assoc()):
                            ?>
                            <tr class="hover:bg-white/[0.02] transition-colors group">
                                <td class="px-4 py-4">
                                    <div class="text-[9px] font-mono text-premium uppercase tracking-widest"><?= htmlspecialchars($item['product_code']) ?></div>
                                    <div class="text-xs font-bold uppercase text-white"><?= htmlspecialchars($item['product_name']) ?></div>
                                </td>
                                
                                <td class="px-4 py-4">
                                    <form action="actions/update_order_item.php" method="POST" class="flex items-center gap-2">
                                        <input type="hidden" name="detail_id" value="<?= $item['detail_id'] ?>">
                                        <input type="hidden" name="order_id" value="<?= $order_id ?>">
                                        <input type="number" name="quantity" min="1" value="<?= $item['quantity'] ?>" 
                                               class="w-16 bg-obsidian-bg border border-obsidian-edge text-center py-1 text-xs font-mono text-white focus:border-premium focus:outline-none">
                                        <button type="submit" class="text-[8px] font-black uppercase tracking-widest text-obsidian-muted hover:text-white transition-colors">
                                            Updt
                                        </button>
                                    </form>
                                </td>
                                
                                <td class="px-4 py-4 text-right font-mono text-xs text-obsidian-muted"><?= formatCurrency($item['unit_price']) ?></td>
                                <td class="px-4 py-4 text-right font-mono text-sm font-bold text-white"><?= formatCurrency($item['total_price']) ?></td>
                                
                                <td class="px-4 py-4 text-right">
                                    <a href="actions/remove_order_item.php?detail_id=<?= $item['detail_id'] ?>&order_id=<?= $order_id ?>" 
                                       onclick="return confirm('Purge this item from the manifest?');"
                                       class="text-[9px] font-black uppercase tracking-widest text-premium/50 hover:text-premium transition-colors">Drop</a>
                                </td>
                            </tr>
                            <?php endwhile; else: ?>
                            <tr>
                                <td colspan="5" class="px-4 py-10 text-center text-obsidian-muted font-mono text-xs">Manifest is empty. Append components.</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-obsidian-edge p-6 bg-obsidian-bg/50">
                    <div class="flex justify-end mb-2">
                        <div class="w-64 flex justify-between text-xs font-mono text-obsidian-muted">
                            <span>SUBTOTAL:</span>
                            <span><?= formatCurrency($order['subtotal']) ?></span>
                        </div>
                    </div>
                    <div class="flex justify-end mb-4 border-b border-obsidian-edge pb-4">
                        <div class="w-64 flex justify-between text-xs font-mono text-premium">
                            <span>DISCOUNT:</span>
                            <span>-<?= formatCurrency($order['membership_discount'] + $order['special_discount']) ?></span>
                        </div>
                    </div>
                    <div class="flex justify-end">
                        <div class="w-64 flex justify-between text-lg font-black tracking-tighter">
                            <span>FINAL:</span>
                            <span><?= formatCurrency($order['total_amount']) ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </main>
</body>
</html>