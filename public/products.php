<?php
session_start();
require_once "../config/database.php";

// 1. STRICT SECURITY GUARD
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'employee') {
    header("Location: ../index.php"); 
    exit;
}

$user_id = $_SESSION['user_id'];

// 2. Fetch EMPLOYEE profile data for the session
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
    <title>Inventory Matrix | ARAI MOTO</title>
    
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
            background-image: 
                radial-gradient(circle at 20% 30%, rgba(225, 29, 72, 0.03) 0%, transparent 40%),
                radial-gradient(circle at 80% 70%, rgba(59, 130, 246, 0.03) 0%, transparent 40%);
        }
        @keyframes tectonicRise {
            from { opacity: 0; transform: translateY(40px) scale(0.98); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }
        .anim-load { animation: tectonicRise 0.8s forwards ease-out; opacity: 0; }
        
        /* Custom scrollbar for textareas */
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
                    if ($_GET['status'] == 'part_registered') echo "[ STATUS: NEW_COMPONENT_INITIALIZED ]";
                    if ($_GET['status'] == 'updated') echo "[ STATUS: COMPONENT_DATA_RECALIBRATED ]";
                    if ($_GET['status'] == 'deleted') echo "[ STATUS: COMPONENT_PURGED_FROM_SYSTEM ]";
                    if ($_GET['status'] == 'db_error') echo "<span class='text-red-500'>[ ERROR: DATABASE_SYNC_FAILURE ]</span>";
                ?>
            </div>
        <?php endif; ?>
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
            
            <div class="lg:col-span-1 anim-load" style="animation-delay: 0.1s;">
                <div class="bg-obsidian-surface border border-obsidian-edge p-8 relative overflow-hidden">
                    <div class="absolute top-0 right-0 p-2 font-mono text-[8px] text-premium/30 uppercase tracking-widest">Inventory_Access: Granted</div>
                    
                    <h2 class="text-2xl font-black uppercase tracking-tighter mb-8 border-l-4 border-premium pl-4">Register_Part</h2>
                    
                    <form action="actions/add_product.php" method="POST" class="space-y-4">
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[10px] uppercase tracking-[0.2em] text-obsidian-muted mb-2 font-bold">Part Code</label>
                                <div class="w-full bg-obsidian-bg/50 border border-obsidian-edge px-4 py-3 text-xs font-mono text-obsidian-muted uppercase cursor-not-allowed flex items-center justify-between">
                                    <span>AUTO_GENERATED</span>
                                    <svg class="w-4 h-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-[10px] uppercase tracking-[0.2em] text-obsidian-muted mb-2 font-bold">Category</label>
                                <select name="product_type" class="w-full bg-obsidian-bg border border-obsidian-edge px-4 py-3 text-sm font-mono focus:outline-none focus:border-premium transition-colors text-white appearance-none">
                                    <option value="Engine">Engine</option>
                                    <option value="Exhaust">Exhaust</option>
                                    <option value="Suspension">Suspension</option>
                                    <option value="Bodywork">Bodywork</option>
                                    <option value="Electronics">Electronics</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-[10px] uppercase tracking-[0.2em] text-obsidian-muted mb-2 font-bold">Component Name</label>
                            <input type="text" name="product_name" required placeholder="Titanium Header Gen.3" 
                                class="w-full bg-obsidian-bg border border-obsidian-edge px-4 py-3 text-sm font-bold focus:outline-none focus:border-premium transition-colors text-white uppercase">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[10px] uppercase tracking-[0.2em] text-obsidian-muted mb-2 font-bold">Cost ($)</label>
                                <input type="number" step="0.01" name="cost_price" required placeholder="0.00"
                                    class="w-full bg-obsidian-bg border border-obsidian-edge px-4 py-3 text-sm font-mono focus:outline-none focus:border-premium transition-colors text-white">
                            </div>
                            <div>
                                <label class="block text-[10px] uppercase tracking-[0.2em] text-obsidian-muted mb-2 font-bold">Selling ($)</label>
                                <input type="number" step="0.01" name="selling_price" required placeholder="0.00"
                                    class="w-full bg-obsidian-bg border border-obsidian-edge px-4 py-3 text-sm font-mono focus:outline-none focus:border-premium transition-colors text-white text-premium">
                            </div>
                        </div>

                        <div>
                            <label class="block text-[10px] uppercase tracking-[0.2em] text-obsidian-muted mb-2 font-bold">Initial Stock Qty</label>
                            <input type="number" name="stock_qty" required placeholder="10" 
                                class="w-full bg-obsidian-bg border border-obsidian-edge px-4 py-3 text-sm font-mono focus:outline-none focus:border-premium transition-colors text-white">
                        </div>

                        <div>
                            <label class="block text-[10px] uppercase tracking-[0.2em] text-obsidian-muted mb-2 font-bold">Specifications / Desc</label>
                            <textarea name="product_description" rows="2" 
                                class="w-full bg-obsidian-bg border border-obsidian-edge px-4 py-3 text-xs font-mono focus:outline-none focus:border-premium transition-colors text-white resize-none"></textarea>
                        </div>

                        <button type="submit" class="w-full py-4 bg-premium text-white text-[10px] font-black uppercase tracking-[0.3em] hover:bg-white hover:text-black transition-all duration-300 shadow-[0_0_20px_rgba(225,29,72,0.2)]">
                            Initialize Component
                        </button>
                    </form>
                </div>
            </div>

            <div class="lg:col-span-2 anim-load" style="animation-delay: 0.2s;">
                <div class="bg-obsidian-surface border border-obsidian-edge overflow-hidden h-full flex flex-col">
                    <div class="px-8 py-6 border-b border-obsidian-edge bg-white/[0.02] flex justify-between items-center">
                        <h2 class="text-xl font-black uppercase tracking-tighter">Inventory_Matrix</h2>
                        <span class="text-premium font-mono text-[10px] animate-pulse">[ WAREHOUSE_SYNC_ACTIVE ]</span>
                    </div>

                    <div class="overflow-x-auto flex-grow">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="text-[10px] uppercase tracking-widest text-obsidian-muted border-b border-obsidian-edge">
                                    <th class="px-6 py-4">Component</th>
                                    <th class="px-6 py-4">Pricing</th>
                                    <th class="px-6 py-4">Stock Status</th>
                                    <th class="px-6 py-4 text-right">Operations</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-obsidian-edge">
                                <?php
                                $productQuery = "SELECT * FROM products ORDER BY created_at DESC";
                                $productResult = $mysqli->query($productQuery);

                                if ($productResult->num_rows > 0):
                                    while($product = $productResult->fetch_assoc()):
                                        $qty = (int)$product['stock_qty'];
                                        
                                        // Stock Status Logic
                                        if ($qty == 0) {
                                            $stockColor = "text-red-600";
                                            $stockBg = "bg-red-600";
                                            $stockLabel = "DEPLETED";
                                        } elseif ($qty <= 5) {
                                            $stockColor = "text-yellow-500";
                                            $stockBg = "bg-yellow-500";
                                            $stockLabel = "CRITICAL";
                                        } else {
                                            $stockColor = "text-green-500";
                                            $stockBg = "bg-green-500";
                                            $stockLabel = "OPTIMAL";
                                        }
                                ?>
                                <tr class="group hover:bg-white/[0.01] transition-colors <?= $qty == 0 ? 'opacity-50 grayscale' : '' ?>">
                                    
                                    <td class="px-6 py-5">
                                        <div class="text-[9px] font-mono text-premium uppercase tracking-widest">
                                            <?= htmlspecialchars($product['product_code']) ?>
                                        </div>
                                        <div class="text-sm font-bold uppercase text-white group-hover:text-premium transition-colors">
                                            <?= htmlspecialchars($product['product_name']) ?>
                                        </div>
                                        <div class="text-[9px] font-mono text-obsidian-muted mt-1">
                                            [ TYPE: <?= htmlspecialchars($product['product_type']) ?> ]
                                        </div>
                                    </td>
                                    
                                    <td class="px-6 py-5 font-mono text-xs">
                                        <div class="text-white">SELL: <span class="text-premium font-bold">$<?= number_format($product['selling_price'], 2) ?></span></div>
                                        <div class="text-[9px] text-obsidian-muted">COST: $<?= number_format($product['cost_price'], 2) ?></div>
                                    </td>

                                    <td class="px-6 py-5">
                                        <div class="flex items-center gap-2 text-[9px] font-black uppercase tracking-widest <?= $stockColor ?>">
                                            <span class="relative flex h-1.5 w-1.5">
                                                <?= $qty > 0 ? "<span class='animate-ping absolute inline-flex h-full w-full rounded-full {$stockBg} opacity-75'></span>" : "" ?>
                                                <span class="relative inline-flex rounded-full h-1.5 w-1.5 <?= $stockBg ?>"></span>
                                            </span>
                                            QTY: <?= $qty ?> (<?= $stockLabel ?>)
                                        </div>
                                    </td>

                                    <td class="px-6 py-5 text-right">
                                        <div class="flex justify-end gap-3 opacity-30 group-hover:opacity-100 transition-opacity">
                                            <a href="edit_product.php?id=<?= $product['product_id'] ?>" class="text-[10px] font-black uppercase tracking-widest text-white/50 hover:text-white transition-colors">Edit</a>
                                            <a href="actions/delete_product.php?id=<?= $product['product_id'] ?>" 
                                            onclick="return confirm('Purge this component from the database?')"
                                            class="text-[10px] font-black uppercase tracking-widest text-premium/50 hover:text-premium transition-colors">Purge</a>
                                        </div>
                                    </td>

                                </tr>
                                <?php endwhile; else: ?>
                                <tr>
                                    <td colspan="4" class="px-6 py-10 text-center text-obsidian-muted font-mono text-xs">No components found in warehouse inventory.</td>
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