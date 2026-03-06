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
$hasCustomerProfile = !empty($customerProfile) && !empty($customerProfile['customer_id']);
$isCustomerView = $hasCustomerProfile;
$currencyCode = $_SESSION['currency'] ?? 'USD';
$currencyRates = ['USD' => 1.00, 'THB' => 34.50, 'JPY' => 150.20];
$currencySymbols = ['USD' => '$', 'THB' => 'THB ', 'JPY' => 'JPY '];
$chartRate = $currencyRates[$currencyCode] ?? 1.00;
$chartSymbol = $currencySymbols[$currencyCode] ?? '$';
$chartDecimals = $currencyCode === 'JPY' ? 0 : 2;

// --- FETCH DASHBOARD METRICS ---
if ($isCustomerView) {
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
} else {
    $statsQuery = "
        SELECT 
            (SELECT COUNT(*) FROM sale_orders) AS total_orders,
            (SELECT SUM(total_amount) FROM sale_orders) AS total_spent,
            (SELECT COUNT(*) FROM invoices WHERE payment_status = 'PENDING') AS pending_invoices
    ";
    $stats = $mysqli->query($statsQuery)->fetch_assoc();
}

$total_orders = $stats['total_orders'] ?? 0;
$total_spent = $stats['total_spent'] ?? 0.00;
$pending_invoices = $stats['pending_invoices'] ?? 0;

// --- NEW: FETCH GRAPH DATA (Last 6 Active Months) ---
$chartResult = null;
if ($isCustomerView) {
    $chartQuery = "
        SELECT DATE_FORMAT(order_date, '%b %Y') as month_label, SUM(total_amount) as monthly_total 
        FROM sale_orders 
        WHERE customer_id = ? 
        GROUP BY YEAR(order_date), MONTH(order_date), month_label 
        ORDER BY YEAR(order_date) DESC, MONTH(order_date) DESC 
        LIMIT 6
    ";
    $stmtChart = $mysqli->prepare($chartQuery);
    $stmtChart->bind_param("i", $customer_id);
    $stmtChart->execute();
    $chartResult = $stmtChart->get_result();
} else {
    $chartQuery = "
        SELECT DATE_FORMAT(order_date, '%b %Y') as month_label, SUM(total_amount) as monthly_total 
        FROM sale_orders 
        GROUP BY YEAR(order_date), MONTH(order_date), month_label 
        ORDER BY YEAR(order_date) DESC, MONTH(order_date) DESC 
        LIMIT 6
    ";
    $chartResult = $mysqli->query($chartQuery);
}

$chartLabels = [];
$chartData = [];
while ($row = $chartResult->fetch_assoc()) {
    // We fetch latest months DESC, then unshift to show chart left->right in time.
    array_unshift($chartLabels, strtoupper($row['month_label']));
    array_unshift($chartData, (float)$row['monthly_total']);
}
?>

<!DOCTYPE html>
<html lang="en">
<?php
$pageTitle = "Client Terminal | ARAII MOTO";
$extraHead = '<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>';
include 'partials/head.php';
?>

<body class="page-bg-soft bg-white text-black font-sans min-h-screen overflow-x-hidden selection:bg-premium selection:text-white">

    <?php include 'partials/nav.php'; ?>

    <header class="relative h-[75vh] flex items-center justify-center overflow-hidden border-b border-obsidian-edge">
        <div class="absolute inset-0 z-0">
            <img src="assets/images/full-shot-adult-with-equipment-riding-motorcycle.jpg" 
                class="w-full h-full object-cover opacity-40 grayscale hover:grayscale-0 transition-all duration-[3000ms] ease-in-out" 
                alt="Background">
            <div class="absolute inset-0 bg-gradient-to-t from-white/80 via-white/20 to-white/90"></div>
            <div class="absolute inset-0 bg-gradient-to-r from-white/90 via-white/25 to-white/90"></div>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[400px] bg-white/40 blur-[150px] rounded-full"></div>
        </div>

        <div class="relative z-10 text-center px-8 anim-hero">
            <p class="text-premium uppercase tracking-[0.8em] text-sm mb-6 font-bold drop-shadow-lg">// PRECISION ENGINEERING</p>
            <h1 class="text-7xl md:text-[10rem] font-black tracking-tighter uppercase leading-[0.8] mb-6 text-black drop-shadow-2xl">
                ARAII <span class="font-light text-black/80 italic">MOTO</span>
            </h1>

            <div class="flex flex-col md:flex-row items-center justify-center gap-6 md:gap-12 mt-12">
                <div class="text-right">
                    <p class="text-black text-sm uppercase tracking-[0.3em] font-bold">Client Terminal</p>
                    <p class="text-black/80 text-sm uppercase tracking-widest mt-1">Authorized Access Granted</p>
                </div>
                <div class="h-12 w-px bg-premium hidden md:block"></div>
                <div class="text-left font-mono text-sm">
                    <p class="text-black animate-pulse">[ IDENTITY: <?= htmlspecialchars($name) ?> ]</p>
                    <p class="text-black/80 uppercase mt-1">Clearance: <?= htmlspecialchars($level) ?></p>
                </div>
            </div>
        </div>
    </header>

    <section class="max-w-[1400px] mx-auto px-8 relative z-20 -mt-16 mb-16 stagger-1">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white border border-obsidian-edge p-6 shadow-2xl shadow-black/10 backdrop-blur-md relative overflow-hidden group hover:border-premium/50 transition-colors">
                <div class="absolute top-0 right-0 w-16 h-16 bg-gradient-to-bl from-white/5 to-transparent"></div>
                <p class="text-sm font-mono uppercase tracking-[0.3em] text-obsidian-muted mb-2">Total Operations</p>
                <div class="flex items-end gap-3">
                    <span class="text-4xl font-black tracking-tighter group-hover:text-premium transition-colors"><?= $total_orders ?></span>
                    <span class="text-sm font-mono text-obsidian-muted mb-1 uppercase tracking-widest">Orders</span>
                </div>
            </div>

            <div class="bg-white border border-obsidian-edge p-6 shadow-2xl shadow-black/10 backdrop-blur-md relative overflow-hidden group hover:border-premium/50 transition-colors">
                <div class="absolute top-0 right-0 w-16 h-16 bg-gradient-to-bl from-premium/10 to-transparent"></div>
                <p class="text-sm font-mono uppercase tracking-[0.3em] text-obsidian-muted mb-2">Fleet Investment</p>
                <div class="text-4xl font-black tracking-tighter group-hover:text-black transition-colors"><?= formatCurrency($total_spent) ?></div>
            </div>

            <div class="bg-white border <?= $pending_invoices > 0 ? 'border-premium/50' : 'border-obsidian-edge' ?> p-6 shadow-2xl shadow-black/10 backdrop-blur-md relative overflow-hidden group transition-colors">
                <div class="absolute top-0 right-0 w-16 h-16 bg-gradient-to-bl from-<?= $pending_invoices > 0 ? 'premium/10' : 'white/5' ?> to-transparent"></div>
                <p class="text-sm font-mono uppercase tracking-[0.3em] text-obsidian-muted mb-2">Awaiting Clearing</p>
                <div class="flex items-end gap-3">
                    <span class="text-4xl font-black tracking-tighter <?= $pending_invoices > 0 ? 'text-premium animate-pulse' : 'text-black' ?>"><?= $pending_invoices ?></span>
                    <span class="text-sm font-mono text-obsidian-muted mb-1 uppercase tracking-widest">Pending Invoices</span>
                </div>
            </div>
        </div>
    </section>

    <section class="max-w-[1400px] mx-auto px-8 mb-20 stagger-2">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
            
            <div class="lg:col-span-1 bg-white border border-obsidian-edge p-6 shadow-2xl shadow-black/10 relative flex flex-col h-[400px]">
                <div class="absolute top-0 right-0 p-2 font-mono text-sm text-premium/30 uppercase tracking-widest">Telemetry</div>
                <h3 class="text-sm font-black uppercase tracking-widest mb-6 text-premium border-l-2 border-premium pl-3">Investment Trend</h3>
                
                <div class="flex-grow w-full h-full relative">
                    <?php if (count($chartData) > 0): ?>
                        <canvas id="spendChart"></canvas>
                    <?php else: ?>
                        <div class="absolute inset-0 flex items-center justify-center text-obsidian-muted font-mono text-sm uppercase tracking-widest text-center px-4">
                            Insufficient Data: Execute purchase operations to populate telemetry matrix.
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="lg:col-span-2 bg-white border border-obsidian-edge flex flex-col shadow-2xl shadow-black/10 h-[400px]">
                <div class="px-6 py-4 border-b border-obsidian-edge bg-white/[0.02] flex justify-between items-center">
                    <h3 class="text-sm font-black uppercase tracking-widest border-l-2 border-premium pl-3">Recent Operations</h3>
                    <span class="text-premium font-mono text-sm animate-pulse">[ LIVE_FEED ]</span>
                </div>

                <div class="overflow-y-auto flex-grow">
                    <table class="w-full text-left border-collapse">
                        <thead class="sticky top-0 bg-white/90 backdrop-blur-sm z-10">
                            <tr class="text-sm uppercase tracking-widest text-obsidian-muted border-b border-obsidian-edge">
                                <th class="px-6 py-3">PO Reference</th>
                                <th class="px-6 py-3">Date</th>
                                <th class="px-6 py-3 text-right">Amount</th>
                                <th class="px-6 py-3 text-right">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-obsidian-edge">
                            <?php
                            if ($isCustomerView) {
                                $orderHistoryQuery = "
                                    SELECT o.order_id, o.po_reference, o.order_date, o.total_amount, i.payment_status
                                    FROM sale_orders o
                                    LEFT JOIN invoices i ON o.order_id = i.order_id
                                    WHERE o.customer_id = ?
                                    ORDER BY o.order_date DESC, o.order_id DESC
                                    LIMIT 10
                                ";
                                $stmtHistory = $mysqli->prepare($orderHistoryQuery);
                                $stmtHistory->bind_param("i", $customer_id);
                                $stmtHistory->execute();
                                $historyResult = $stmtHistory->get_result();
                            } else {
                                $orderHistoryQuery = "
                                    SELECT o.order_id, o.po_reference, o.order_date, o.total_amount, i.payment_status
                                    FROM sale_orders o
                                    LEFT JOIN invoices i ON o.order_id = i.order_id
                                    ORDER BY o.order_date DESC, o.order_id DESC
                                    LIMIT 10
                                ";
                                $historyResult = $mysqli->query($orderHistoryQuery);
                            }

                            if ($historyResult->num_rows > 0):
                                while($order = $historyResult->fetch_assoc()):
                                    $status = $order['payment_status'];
                            ?>
                            <tr class="group hover:bg-white/[0.02] transition-colors cursor-default">
                                <td class="px-6 py-4">
                                    <div class="text-sm font-mono font-bold text-black group-hover:text-premium transition-colors">
                                        <?= htmlspecialchars($order['po_reference']) ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm font-mono text-obsidian-muted">
                                    <?= date('d M Y', strtotime($order['order_date'])) ?>
                                </td>
                                <td class="px-6 py-4 text-sm font-mono font-bold text-black text-right">
                                    <?= formatCurrency($order['total_amount']) ?>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <?php if ($status === 'PAID'): ?>
                                        <span class="text-sm font-black uppercase tracking-widest text-premium">CLEARED</span>
                                    <?php elseif ($status === 'PENDING'): ?>
                                        <span class="text-sm font-black uppercase tracking-widest text-premium animate-pulse">PENDING</span>
                                    <?php else: ?>
                                        <span class="text-sm font-black uppercase tracking-widest text-obsidian-muted">PROCESSING</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php 
                                endwhile; 
                            else: 
                            ?>
                            <tr>
                                <td colspan="4" class="px-6 py-10 text-center text-obsidian-muted font-mono text-sm uppercase tracking-[0.2em]">
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
                <p class="text-obsidian-muted font-mono text-sm mt-2 uppercase tracking-widest">
                    Database: Verified Genuine Parts Only
                </p>
            </div>
            <a href="products.php" class="group text-sm uppercase tracking-widest flex items-center gap-4 hover:text-premium transition-colors">
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
                    $img_filename = trim((string)($product['image_path'] ?? ''));
                    $img_path = '';
                    $fallback_img = 'assets/images/herostrike.jpg';
                    $hasImage = false;

                    if ($img_filename !== '') {
                        // Accept both raw filenames and legacy stored relative paths.
                        $normalizedImg = ltrim(str_replace('\\', '/', $img_filename), '/');
                        if (strpos($normalizedImg, 'assets/') === 0) {
                            $img_path = $normalizedImg;
                        } elseif (strpos($normalizedImg, 'images/products/') === 0) {
                            $img_path = 'assets/' . $normalizedImg;
                        } else {
                            $img_path = 'assets/images/products/' . $normalizedImg;
                        }
                        $hasImage = file_exists($img_path);
                    }
            ?>
            <div class="slab stagger-2 group relative bg-white border border-obsidian-edge p-3 overflow-hidden transition-all duration-500 hover:border-premium/40 shadow-xl shadow-black/10">
                <div class="aspect-square bg-white border border-obsidian-edge overflow-hidden relative">
                    <?php if ($hasImage): ?>
                        <img src="<?= $img_path ?>" class="w-full h-full object-cover opacity-80 group-hover:opacity-100 group-hover:scale-110 transition-all duration-700" alt="<?= $pName ?>">
                    <?php else: ?>
                        <img src="<?= $fallback_img ?>" class="w-full h-full object-cover opacity-60 grayscale group-hover:opacity-85 group-hover:grayscale-0 group-hover:scale-110 transition-all duration-700" alt="Default product image">
                    <?php endif; ?>
                    <div class="absolute bottom-4 left-4 bg-white/80 backdrop-blur-md px-3 py-1 border border-obsidian-edge">
                        <span class="text-sm font-mono tracking-tighter text-obsidian-muted uppercase">Serial: #MOTO-<?= str_pad($pId, 4, '0', STR_PAD_LEFT) ?></span>
                    </div>
                </div>
                
                <div class="p-6">
                    <div class="flex justify-between items-start mb-2">
                        <h3 class="text-xl font-bold uppercase tracking-tight group-hover:text-premium transition-colors"><?= $pName ?></h3>
                        <span class="font-mono text-premium text-lg tracking-tighter font-bold">$<?= $pPrice ?></span>
                    </div>
                    <p class="text-obsidian-muted text-sm uppercase tracking-widest mb-8 leading-relaxed line-clamp-2"><?= $pDesc ?></p>
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
                    data: <?= json_encode($chartData) ?>.map(v => v * <?= json_encode($chartRate) ?>),
                    borderColor: '#b00020',
                    backgroundColor: gradient,
                    borderWidth: 2,
                    pointBackgroundColor: '#b00020',
                    pointBorderColor: '#ffffff',
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
                        backgroundColor: '#ffffff',
                        titleColor: '#111827',
                        bodyColor: '#111827',
                        titleFont: { family: "'JetBrains Mono', monospace", size: 10 },
                        bodyFont: { family: "'JetBrains Mono', monospace", size: 12, weight: 'bold' },
                        borderColor: 'rgba(225, 29, 72, 0.3)',
                        borderWidth: 1,
                        padding: 10,
                        displayColors: false,
                        callbacks: {
                            label: function(context) {
                                return <?= json_encode($chartSymbol) ?> + context.parsed.y.toLocaleString(undefined, {
                                    minimumFractionDigits: <?= json_encode($chartDecimals) ?>,
                                    maximumFractionDigits: <?= json_encode($chartDecimals) ?>
                                });
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { color: 'rgba(255, 255, 255, 0.05)', drawBorder: false },
                        ticks: { font: { family: "'JetBrains Mono', monospace", size: 9 }, color: '#374151' }
                    },
                    y: {
                        grid: { color: 'rgba(255, 255, 255, 0.05)', drawBorder: false },
                        ticks: { 
                            font: { family: "'JetBrains Mono', monospace", size: 9 }, 
                            color: '#374151',
                            callback: function(value) {
                                return <?= json_encode($chartSymbol) ?> + Number(value).toLocaleString(undefined, {
                                    minimumFractionDigits: 0,
                                    maximumFractionDigits: <?= json_encode($chartDecimals) ?>
                                });
                            }
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
