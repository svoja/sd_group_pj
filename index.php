<?php
// 1. Start session and check login status
session_start();

if (!isset($_SESSION['logged_in'])) {
    header("Location: login.php");
    exit;
}
// 3. Connect to the database
require_once "config/currency.php";
require_once "config/database.php";

$user_id = $_SESSION['user_id'];
$email = $_SESSION['email'];
$role = $_SESSION['role'];
// Fetch Customer Profile
$stmt = $mysqli->prepare("SELECT customer_id, customer_code, contact_name, address, membership_level FROM customers WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$customerProfile = $result->fetch_assoc();

$customer_id = $customerProfile['customer_id'] ?? 0;
$name = $customerProfile['contact_name'] ?? 'Valued Customer';
$level = $customerProfile['membership_level'] ?? 'STANDARD';
$code = $customerProfile['customer_code'] ?? 'N/A';

// --- FETCH DASHBOARD METRICS ---
$statsQuery = "
    SELECT 
        (SELECT COUNT(*) FROM sale_orders WHERE customer_id = ?) AS total_orders,
        (SELECT SUM(total_amount) FROM sale_orders WHERE customer_id = ?) AS total_spent,
        (SELECT COUNT(*) FROM invoices WHERE customer_id = ? AND payment_status = 'PENDING') AS pending_invoices
";
$stmtStats = $mysqli->prepare($statsQuery);
$stmtStats->bind_param("iii", $customer_id, $customer_id, $customer_id);
$stmtStats->execute();
$stats = $stmtStats->get_result()->fetch_assoc();

$total_orders = $stats['total_orders'] ?? 0;
$total_spent = $stats['total_spent'] ?? 0.00;
$pending_invoices = $stats['pending_invoices'] ?? 0;

// --- NEW: FETCH GRAPH DATA (Last 6 Active Months) ---
$chartQuery = "
    SELECT DATE_FORMAT(order_date, '%b %Y') as month_label, SUM(total_amount) as monthly_total 
    FROM sale_orders 
    WHERE customer_id = ? 
    GROUP BY YEAR(order_date), MONTH(order_date), month_label 
    ORDER BY YEAR(order_date) ASC, MONTH(order_date) ASC 
    LIMIT 6
";
$stmtChart = $mysqli->prepare($chartQuery);
$stmtChart->bind_param("i", $customer_id);
$stmtChart->execute();
$chartResult = $stmtChart->get_result();

$chartLabels = [];
$chartData = [];
while ($row = $chartResult->fetch_assoc()) {
    $chartLabels[] = strtoupper($row['month_label']);
    $chartData[] = (float)$row['monthly_total'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Terminal | ARAI MOTO</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;700;900&family=JetBrains+Mono:wght@400;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
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
            background-image: 
                radial-gradient(circle at 0% 0%, rgba(225, 29, 72, 0.12) 0%, transparent 50%),
                radial-gradient(circle at 100% 100%, rgba(225, 29, 72, 0.05) 0%, transparent 50%),
                radial-gradient(rgba(255, 255, 255, 0.04) 1px, transparent 1px);
            background-size: 100% 100%, 100% 100%, 24px 24px;
            background-attachment: fixed;
        }

        .slab::after {
            content: '';
            position: absolute;
            top: 0; right: 0;
            width: 50px; height: 50px;
            background: linear-gradient(225deg, rgba(255,255,255,0.05) 50%, transparent 50%);
        }

        @keyframes tectonicRise {
            from { opacity: 0; transform: translateY(40px) scale(0.98); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }
        .anim-hero { animation: tectonicRise 0.8s forwards ease-out; opacity: 0; }
        .stagger-1 { animation: tectonicRise 0.8s 0.1s forwards cubic-bezier(0.23, 1, 0.32, 1); opacity: 0; }
        .stagger-2 { animation: tectonicRise 0.8s 0.2s forwards cubic-bezier(0.23, 1, 0.32, 1); opacity: 0; }
        .anim-load { animation: tectonicRise 0.8s forwards ease-out; opacity: 0; }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #020202; }
        ::-webkit-scrollbar-thumb { background: #e11d48; border-radius: 10px; }
    </style>
</head>

<body class="bg-obsidian-bg text-white font-sans min-h-screen overflow-x-hidden selection:bg-premium selection:text-white">

    <?php include 'partials/nav.php'; ?>

    <header class="relative h-[75vh] flex items-center justify-center overflow-hidden border-b border-obsidian-edge">
        <div class="absolute inset-0 z-0">
            <img src="assets/images/full-shot-adult-with-equipment-riding-motorcycle.jpg" 
                class="w-full h-full object-cover opacity-40 grayscale hover:grayscale-0 transition-all duration-[3000ms] ease-in-out" 
                alt="Background">
            <div class="absolute inset-0 bg-gradient-to-t from-obsidian-bg via-transparent to-obsidian-bg/80"></div>
            <div class="absolute inset-0 bg-gradient-to-r from-obsidian-bg/90 via-transparent to-obsidian-bg/90"></div>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[400px] bg-premium/20 blur-[150px] rounded-full"></div>
        </div>

        <div class="relative z-10 text-center px-8 anim-hero">
            <p class="text-premium uppercase tracking-[0.8em] text-[10px] mb-6 font-bold drop-shadow-lg">// PRECISION ENGINEERING</p>
            <h1 class="text-7xl md:text-[10rem] font-black tracking-tighter uppercase leading-[0.8] mb-6 drop-shadow-2xl">
                ARAI <span class="font-light text-white/50 italic">MOTO</span>
            </h1>

            <div class="flex flex-col md:flex-row items-center justify-center gap-6 md:gap-12 mt-12">
                <div class="text-right">
                    <p class="text-white text-sm uppercase tracking-[0.3em] font-bold">Client Terminal</p>
                    <p class="text-obsidian-muted text-[10px] uppercase tracking-widest mt-1">Authorized Access Granted</p>
                </div>
                <div class="h-12 w-px bg-premium hidden md:block"></div>
                <div class="text-left font-mono text-xs">
                    <p class="text-premium animate-pulse">[ IDENTITY: <?= htmlspecialchars($name) ?> ]</p>
                    <p class="text-obsidian-muted uppercase mt-1">Clearance: <?= htmlspecialchars($level) ?></p>
                </div>
            </div>
        </div>
    </header>

    <section class="max-w-[1400px] mx-auto px-8 relative z-20 -mt-16 mb-16 stagger-1">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-obsidian-surface border border-obsidian-edge p-6 shadow-2xl shadow-black backdrop-blur-md relative overflow-hidden group hover:border-premium/50 transition-colors">
                <div class="absolute top-0 right-0 w-16 h-16 bg-gradient-to-bl from-white/5 to-transparent"></div>
                <p class="text-[10px] font-mono uppercase tracking-[0.3em] text-obsidian-muted mb-2">Total Operations</p>
                <div class="flex items-end gap-3">
                    <span class="text-4xl font-black tracking-tighter group-hover:text-premium transition-colors"><?= $total_orders ?></span>
                    <span class="text-[10px] font-mono text-obsidian-muted mb-1 uppercase tracking-widest">Orders</span>
                </div>
            </div>

            <div class="bg-obsidian-surface border border-obsidian-edge p-6 shadow-2xl shadow-black backdrop-blur-md relative overflow-hidden group hover:border-premium/50 transition-colors">
                <div class="absolute top-0 right-0 w-16 h-16 bg-gradient-to-bl from-premium/10 to-transparent"></div>
                <p class="text-[10px] font-mono uppercase tracking-[0.3em] text-obsidian-muted mb-2">Fleet Investment</p>
                <div class="flex items-end gap-1">
                    <span class="text-xl font-mono text-premium font-bold mb-1">$</span>
                    <span class="text-4xl font-black tracking-tighter group-hover:text-white transition-colors"><?= number_format($total_spent, 2) ?></span>
                </div>
            </div>

            <div class="bg-obsidian-surface border <?= $pending_invoices > 0 ? 'border-yellow-500/50' : 'border-obsidian-edge' ?> p-6 shadow-2xl shadow-black backdrop-blur-md relative overflow-hidden group transition-colors">
                <div class="absolute top-0 right-0 w-16 h-16 bg-gradient-to-bl from-<?= $pending_invoices > 0 ? 'yellow-500/10' : 'white/5' ?> to-transparent"></div>
                <p class="text-[10px] font-mono uppercase tracking-[0.3em] text-obsidian-muted mb-2">Awaiting Clearing</p>
                <div class="flex items-end gap-3">
                    <span class="text-4xl font-black tracking-tighter <?= $pending_invoices > 0 ? 'text-yellow-500 animate-pulse' : 'text-white' ?>"><?= $pending_invoices ?></span>
                    <span class="text-[10px] font-mono text-obsidian-muted mb-1 uppercase tracking-widest">Pending Invoices</span>
                </div>
            </div>
        </div>
    </section>

    <section class="max-w-[1400px] mx-auto px-8 mb-20 stagger-2">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
            
            <div class="lg:col-span-1 bg-obsidian-surface border border-obsidian-edge p-6 shadow-2xl shadow-black relative flex flex-col h-[400px]">
                <div class="absolute top-0 right-0 p-2 font-mono text-[8px] text-premium/30 uppercase tracking-widest">Telemetry</div>
                <h3 class="text-sm font-black uppercase tracking-widest mb-6 text-premium border-l-2 border-premium pl-3">Investment Trend</h3>
                
                <div class="flex-grow w-full h-full relative">
                    <?php if (count($chartData) > 0): ?>
                        <canvas id="spendChart"></canvas>
                    <?php else: ?>
                        <div class="absolute inset-0 flex items-center justify-center text-obsidian-muted font-mono text-xs uppercase tracking-widest text-center px-4">
                            Insufficient Data: Execute purchase operations to populate telemetry matrix.
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="lg:col-span-2 bg-obsidian-surface border border-obsidian-edge flex flex-col shadow-2xl shadow-black h-[400px]">
                <div class="px-6 py-4 border-b border-obsidian-edge bg-white/[0.02] flex justify-between items-center">
                    <h3 class="text-sm font-black uppercase tracking-widest border-l-2 border-premium pl-3">Recent Operations</h3>
                    <span class="text-premium font-mono text-[10px] animate-pulse">[ LIVE_FEED ]</span>
                </div>

                <div class="overflow-y-auto flex-grow">
                    <table class="w-full text-left border-collapse">
                        <thead class="sticky top-0 bg-obsidian-surface/90 backdrop-blur-sm z-10">
                            <tr class="text-[9px] uppercase tracking-widest text-obsidian-muted border-b border-obsidian-edge">
                                <th class="px-6 py-3">PO Reference</th>
                                <th class="px-6 py-3">Date</th>
                                <th class="px-6 py-3 text-right">Amount</th>
                                <th class="px-6 py-3 text-right">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-obsidian-edge">
                            <?php
                            $orderHistoryQuery = "
                                SELECT o.order_id, o.po_reference, o.order_date, o.total_amount, i.payment_status
                                FROM sale_orders o
                                LEFT JOIN invoices i ON o.order_id = i.order_id
                                WHERE o.customer_id = ?
                                ORDER BY o.order_date DESC
                                LIMIT 10
                            ";
                            $stmtHistory = $mysqli->prepare($orderHistoryQuery);
                            $stmtHistory->bind_param("i", $customer_id);
                            $stmtHistory->execute();
                            $historyResult = $stmtHistory->get_result();

                            if ($historyResult->num_rows > 0):
                                while($order = $historyResult->fetch_assoc()):
                                    $status = $order['payment_status'];
                            ?>
                            <tr class="group hover:bg-white/[0.02] transition-colors cursor-default">
                                <td class="px-6 py-4">
                                    <div class="text-[10px] font-mono font-bold text-white group-hover:text-premium transition-colors">
                                        <?= htmlspecialchars($order['po_reference']) ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-[10px] font-mono text-obsidian-muted">
                                    <?= date('d M Y', strtotime($order['order_date'])) ?>
                                </td>
                                <td class="px-6 py-4 text-[11px] font-mono font-bold text-white text-right">
                                    $<?= number_format($order['total_amount'], 2) ?>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <?php if ($status === 'PAID'): ?>
                                        <span class="text-[9px] font-black uppercase tracking-widest text-green-500">CLEARED</span>
                                    <?php elseif ($status === 'PENDING'): ?>
                                        <span class="text-[9px] font-black uppercase tracking-widest text-yellow-500 animate-pulse">PENDING</span>
                                    <?php else: ?>
                                        <span class="text-[9px] font-black uppercase tracking-widest text-obsidian-muted">PROCESSING</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php 
                                endwhile; 
                            else: 
                            ?>
                            <tr>
                                <td colspan="4" class="px-6 py-10 text-center text-obsidian-muted font-mono text-xs uppercase tracking-[0.2em]">
                                    No transaction history recorded.
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
        </div>
    </section>

    <main class="max-w-[1400px] mx-auto pb-20 px-8">
        <div class="flex items-end justify-between mb-12 stagger-2 border-l-4 border-premium pl-6">
            <div>
                <h2 class="text-4xl font-black tracking-tighter uppercase">Available Components</h2>
                <p class="text-obsidian-muted font-mono text-xs mt-2 uppercase tracking-widest">
                    Database: Verified Genuine Parts Only
                </p>
            </div>
            <a href="products.php" class="group text-xs uppercase tracking-widest flex items-center gap-4 hover:text-premium transition-colors">
                Full Catalog <span class="transition-transform group-hover:translate-x-2">——></span>
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10">
            <?php
            $productQuery = "SELECT product_id, product_name, selling_price, product_description, image_path FROM products ORDER BY product_id DESC LIMIT 6";
            $productResult = $mysqli->query($productQuery);

            if ($productResult && $productResult->num_rows > 0):
                while($product = $productResult->fetch_assoc()):
                    $pName = htmlspecialchars($product['product_name']);
                    $pPrice = number_format($product['selling_price'], 2);
                    $pDesc = htmlspecialchars($product['product_description']);
                    $pId = $product['product_id'];
                    $img_filename = $product['image_path'] ?? ''; 
                    $img_path = "assets/products/" . $img_filename;
                    $hasImage = (!empty($img_filename) && file_exists($img_path));
            ?>
            <div class="slab stagger-2 group relative bg-obsidian-surface border border-obsidian-edge p-3 overflow-hidden transition-all duration-500 hover:border-premium/40 shadow-xl shadow-black">
                <div class="aspect-square bg-obsidian-bg border border-obsidian-edge overflow-hidden relative">
                    <?php if ($hasImage): ?>
                        <img src="<?= $img_path ?>" class="w-full h-full object-cover opacity-80 group-hover:opacity-100 group-hover:scale-110 transition-all duration-700" alt="<?= $pName ?>">
                    <?php else: ?>
                        <div class="absolute inset-0 flex items-center justify-center text-obsidian-muted/20 group-hover:text-premium/20 transition-colors">
                            <svg width="120" height="120" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="0.5">
                                <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path>
                            </svg>
                        </div>
                    <?php endif; ?>
                    <div class="absolute bottom-4 left-4 bg-obsidian-bg/80 backdrop-blur-md px-3 py-1 border border-obsidian-edge">
                        <span class="text-[9px] font-mono tracking-tighter text-obsidian-muted uppercase">Serial: #MOTO-<?= str_pad($pId, 4, '0', STR_PAD_LEFT) ?></span>
                    </div>
                </div>
                
                <div class="p-6">
                    <div class="flex justify-between items-start mb-2">
                        <h3 class="text-xl font-bold uppercase tracking-tight group-hover:text-premium transition-colors"><?= $pName ?></h3>
                        <span class="font-mono text-premium text-lg tracking-tighter font-bold">$<?= $pPrice ?></span>
                    </div>
                    <p class="text-obsidian-muted text-[11px] uppercase tracking-widest mb-8 leading-relaxed line-clamp-2"><?= $pDesc ?></p>
                </div>
            </div>
            <?php 
                endwhile; 
            else:
            ?>
                <div class="col-span-full py-20 text-center border border-dashed border-obsidian-edge">
                    <p class="text-obsidian-muted font-mono uppercase tracking-[0.3em]">Inventory Link Offline: No Parts Found</p>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <?php if (count($chartData) > 0): ?>
    <script>
        const ctx = document.getElementById('spendChart').getContext('2d');
        
        // Gradient for the line area
        let gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(225, 29, 72, 0.4)'); // Premium Red
        gradient.addColorStop(1, 'rgba(225, 29, 72, 0)');

        const spendChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?= json_encode($chartLabels) ?>,
                datasets: [{
                    label: 'Investment ($)',
                    data: <?= json_encode($chartData) ?>,
                    borderColor: '#e11d48',
                    backgroundColor: gradient,
                    borderWidth: 2,
                    pointBackgroundColor: '#e11d48',
                    pointBorderColor: '#0a0a0a',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    fill: true,
                    tension: 0.3 // Smooth curves
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#0a0a0a',
                        titleFont: { family: "'JetBrains Mono', monospace", size: 10 },
                        bodyFont: { family: "'JetBrains Mono', monospace", size: 12, weight: 'bold' },
                        borderColor: 'rgba(225, 29, 72, 0.3)',
                        borderWidth: 1,
                        displayColors: false,
                        callbacks: {
                            label: function(context) {
                                return '$' + context.parsed.y.toLocaleString(undefined, {minimumFractionDigits: 2});
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { color: 'rgba(255, 255, 255, 0.05)', drawBorder: false },
                        ticks: { font: { family: "'JetBrains Mono', monospace", size: 9 }, color: '#666666' }
                    },
                    y: {
                        grid: { color: 'rgba(255, 255, 255, 0.05)', drawBorder: false },
                        ticks: { 
                            font: { family: "'JetBrains Mono', monospace", size: 9 }, 
                            color: '#666666',
                            callback: function(value) { return '$' + value; }
                        }
                    }
                }
            }
        });
    </script>
    <?php endif; ?>

    <script>
        // Performance-focused Tilt Effect
        document.querySelectorAll('.slab').forEach(slab => {
            slab.addEventListener('mousemove', e => {
                const rect = slab.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                const moveX = (x - rect.width / 2) / 40;
                const moveY = (y - rect.height / 2) / 40;
                slab.style.transform = `perspective(1000px) rotateX(${-moveY}deg) rotateY(${moveX}deg) translateY(-8px)`;
            });

            slab.addEventListener('mouseleave', () => {
                slab.style.transform = `perspective(1000px) rotateX(0deg) rotateY(0deg) translateY(0px)`;
            });
        });
    </script>
</body>
</html>