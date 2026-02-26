<?php
session_start();
require_once "../config/currency.php"; // <--- ADD THIS LINE!
require_once "../config/database.php";

// Security Check
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'employee') {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: products.php");
    exit;
}

$product_id = intval($_GET['id']);

// Fetch current product data
$stmt = $mysqli->prepare("SELECT * FROM products WHERE product_id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    header("Location: products.php?status=db_error");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recalibrate Component | ARAI MOTO</title>
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
</head>
<body class="bg-obsidian-bg text-white font-sans min-h-screen flex flex-col selection:bg-premium selection:text-white">

    <?php include 'partials/nav.php'; ?>

    <main class="flex-grow flex items-center justify-center p-8">
        <div class="w-full max-w-2xl bg-obsidian-surface border border-obsidian-edge p-8 relative shadow-2xl">
            <div class="absolute top-0 right-0 p-2 font-mono text-[8px] text-premium/30 uppercase tracking-widest">Sys_Edit: Active</div>
            
            <h2 class="text-2xl font-black uppercase tracking-tighter mb-2 border-l-4 border-premium pl-4">Recalibrate_Component</h2>
            <p class="text-obsidian-muted font-mono text-xs mb-8 pl-5">Target Serial: <span class="text-premium"><?= htmlspecialchars($product['product_code']) ?></span></p>
            
            <form action="actions/update_product.php" method="POST" class="space-y-6">
                <input type="hidden" name="product_id" value="<?= $product_id ?>">

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] uppercase tracking-[0.2em] text-obsidian-muted mb-2 font-bold">Component Name</label>
                        <input type="text" name="product_name" required value="<?= htmlspecialchars($product['product_name']) ?>" 
                               class="w-full bg-obsidian-bg border border-obsidian-edge px-4 py-3 text-sm font-bold focus:outline-none focus:border-premium transition-colors text-white uppercase">
                    </div>
                    <div>
                        <label class="block text-[10px] uppercase tracking-[0.2em] text-obsidian-muted mb-2 font-bold">Category</label>
                        <select name="product_type" class="w-full bg-obsidian-bg border border-obsidian-edge px-4 py-3 text-sm font-mono focus:outline-none focus:border-premium transition-colors text-white appearance-none">
                            <?php 
                            $categories = ["Engine", "Exhaust", "Suspension", "Bodywork", "Electronics"];
                            foreach ($categories as $cat) {
                                $selected = ($product['product_type'] === $cat) ? 'selected' : '';
                                echo "<option value='$cat' $selected>$cat</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <div class="flex flex-col lg:flex-row lg:justify-between lg:items-end mb-2 gap-1">
                            <label class="block text-[10px] uppercase tracking-[0.2em] text-obsidian-muted font-bold">Cost (USD Base)</label>
                            <span class="text-[9px] font-mono text-obsidian-muted uppercase tracking-widest">
                                Local: <span class="text-white"><?= formatCurrency($product['cost_price']) ?></span>
                            </span>
                        </div>
                        <input type="number" step="0.01" name="cost_price" required value="<?= htmlspecialchars($product['cost_price']) ?>"
                            class="w-full bg-obsidian-bg border border-obsidian-edge px-4 py-3 text-sm font-mono focus:outline-none focus:border-premium transition-colors text-white">
                    </div>

                    <div>
                        <div class="flex flex-col lg:flex-row lg:justify-between lg:items-end mb-2 gap-1">
                            <label class="block text-[10px] uppercase tracking-[0.2em] text-obsidian-muted font-bold">Selling (USD Base)</label>
                            <span class="text-[9px] font-mono text-obsidian-muted uppercase tracking-widest">
                                Local: <span class="text-premium font-bold"><?= formatCurrency($product['selling_price']) ?></span>
                            </span>
                        </div>
                        <input type="number" step="0.01" name="selling_price" required value="<?= htmlspecialchars($product['selling_price']) ?>"
                            class="w-full bg-obsidian-bg border border-obsidian-edge px-4 py-3 text-sm font-mono focus:outline-none focus:border-premium transition-colors text-premium font-bold">
                    </div>
                </div>

                <div class="mb-6 w-full md:w-1/2 md:pr-3">
                    <label class="block text-[10px] uppercase tracking-[0.2em] text-obsidian-muted mb-2 font-bold">Stock Level</label>
                    <input type="number" name="stock_qty" required value="<?= htmlspecialchars($product['stock_qty']) ?>"
                        class="w-full bg-obsidian-bg border border-obsidian-edge px-4 py-3 text-sm font-bold focus:outline-none focus:border-premium transition-colors text-white">
                </div>

                <div>
                    <label class="block text-[10px] uppercase tracking-[0.2em] text-obsidian-muted mb-2 font-bold">Specifications</label>
                    <textarea name="product_description" rows="3" 
                              class="w-full bg-obsidian-bg border border-obsidian-edge px-4 py-3 text-xs font-mono focus:outline-none focus:border-premium transition-colors text-white resize-none"><?= htmlspecialchars($product['product_description']) ?></textarea>
                </div>

                <div class="flex gap-4 pt-4">
                    <a href="products.php" class="w-1/3 text-center py-4 border border-obsidian-edge text-obsidian-muted text-[10px] font-black uppercase tracking-[0.2em] hover:text-white hover:border-white transition-colors">
                        Abort
                    </a>
                    <button type="submit" class="w-2/3 py-4 bg-premium text-white text-[10px] font-black uppercase tracking-[0.3em] hover:bg-white hover:text-black transition-all duration-300 shadow-[0_0_20px_rgba(225,29,72,0.2)]">
                        Execute Recalibration
                    </button>
                </div>
            </form>
        </div>
    </main>
</body>
</html>