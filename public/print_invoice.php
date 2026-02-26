<?php
session_start();
require_once "../config/database.php";

if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'employee') {
    header("Location: ../index.php"); 
    exit;
}

if (!isset($_GET['id'])) {
    die("SYSTEM ERROR: Missing Invoice ID.");
}

$invoice_id = intval($_GET['id']);

// 1. Fetch Master Invoice & Order Data
$query = "
    SELECT i.*, 
           o.po_reference, o.subtotal, o.special_discount, o.membership_discount, 
           c.customer_code, c.contact_name, c.address, c.membership_level,
           e.name AS staff_name
    FROM invoices i
    JOIN sale_orders o ON i.order_id = o.order_id
    JOIN customers c ON i.customer_id = c.customer_id
    LEFT JOIN employees e ON o.employee_id = e.employee_id
    WHERE i.invoice_id = ?";

$stmt = $mysqli->prepare($query);
$stmt->bind_param("i", $invoice_id);
$stmt->execute();
$invoice = $stmt->get_result()->fetch_assoc();

if (!$invoice) {
    die("SYSTEM ERROR: Invoice record not found.");
}

$order_id = $invoice['order_id'];

// 2. Fetch Line Items
$itemQuery = "
    SELECT sod.*, p.product_code, p.product_name 
    FROM sale_order_details sod 
    JOIN products p ON sod.product_id = p.product_id 
    WHERE sod.order_id = ?";
$stmt = $mysqli->prepare($itemQuery);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$items = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice <?= htmlspecialchars($invoice['invoice_reference']) ?> | ARAI MOTO</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&family=JetBrains+Mono:wght@400;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: { fontFamily: { sans: ['Inter', 'sans-serif'], mono: ['JetBrains Mono', 'monospace'] }, colors: { premium: '#e11d48' } }
            }
        }
    </script>
    <style>
        body { background-color: #f3f4f6; color: #000; }
        .invoice-box { background-color: #fff; max-w: 800px; margin: 40px auto; padding: 40px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        
        /* Hides the print button and page background when printing */
        @media print {
            body { background-color: #fff; }
            .invoice-box { box-shadow: none; margin: 0; padding: 0; max-w: 100%; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body class="font-sans antialiased">

    <div class="no-print text-center py-6 bg-[#0a0a0a] border-b border-[#e11d48]/30">
        <button onclick="window.print()" class="bg-[#e11d48] text-white px-8 py-3 text-xs font-black uppercase tracking-[0.3em] hover:bg-white hover:text-black transition-colors">
            [ Print Document ]
        </button>
        <button onclick="window.close()" class="ml-4 border border-white/20 text-white/50 px-8 py-3 text-xs font-black uppercase tracking-[0.3em] hover:text-white transition-colors">
            Close View
        </button>
    </div>

    <div class="invoice-box border border-black/10">
        
        <div class="flex justify-between items-start border-b-4 border-black pb-8 mb-8">
            <div>
                <h1 class="text-4xl font-black uppercase tracking-tighter leading-none mb-1">ARAI <span class="text-premium">MOTO</span></h1>
                <p class="font-mono text-xs uppercase tracking-widest text-gray-500">Performance Division</p>
                <div class="mt-4 font-mono text-xs text-gray-600 space-y-1">
                    <p>104 Obsidian Way</p>
                    <p>Neo-Tokyo Sector, 88492</p>
                    <p>SYS: ARAI-MOTO-SECURE</p>
                </div>
            </div>
            <div class="text-right">
                <h2 class="text-3xl font-black uppercase tracking-tighter text-gray-200">INVOICE</h2>
                <div class="mt-4 font-mono text-sm space-y-1">
                    <p class="font-bold"><?= htmlspecialchars($invoice['invoice_reference']) ?></p>
                    <p class="text-gray-500">DATE: <?= date('d M Y', strtotime($invoice['invoice_date'])) ?></p>
                    <p class="text-gray-500">PO REF: <?= htmlspecialchars($invoice['po_reference']) ?></p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-10 mb-8 font-mono text-sm">
            <div>
                <p class="text-[10px] uppercase tracking-widest text-gray-400 mb-2 border-b border-gray-200 pb-1">Billed Entity</p>
                <p class="font-bold uppercase text-base"><?= htmlspecialchars($invoice['contact_name']) ?></p>
                <p class="text-gray-500 mt-1"><?= htmlspecialchars($invoice['customer_code']) ?> [<?= htmlspecialchars($invoice['membership_level']) ?>]</p>
                <p class="text-gray-600 mt-2 whitespace-pre-line"><?= htmlspecialchars($invoice['address']) ?></p>
            </div>
            <div>
                <p class="text-[10px] uppercase tracking-widest text-gray-400 mb-2 border-b border-gray-200 pb-1">Transaction Details</p>
                <div class="grid grid-cols-2 gap-2 mt-2">
                    <span class="text-gray-500">Method:</span>
                    <span class="font-bold text-right uppercase"><?= htmlspecialchars($invoice['payment_method']) ?></span>
                    
                    <span class="text-gray-500">Status:</span>
                    <span class="font-bold text-right uppercase <?= $invoice['payment_status'] === 'PAID' ? 'text-green-600' : 'text-gray-800' ?>">
                        <?= htmlspecialchars($invoice['payment_status']) ?>
                    </span>
                    
                    <span class="text-gray-500">Authorized By:</span>
                    <span class="text-right uppercase truncate"><?= htmlspecialchars($invoice['staff_name']) ?></span>
                </div>
            </div>
        </div>

        <table class="w-full text-left border-collapse mb-8 font-mono text-sm">
            <thead>
                <tr class="text-[10px] uppercase tracking-widest text-gray-400 border-y-2 border-black">
                    <th class="py-3 px-2">Component / Serial</th>
                    <th class="py-3 px-2 text-center">Qty</th>
                    <th class="py-3 px-2 text-right">Unit Price</th>
                    <th class="py-3 px-2 text-right">Total</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php while($item = $items->fetch_assoc()): ?>
                <tr>
                    <td class="py-4 px-2">
                        <div class="font-bold uppercase text-black"><?= htmlspecialchars($item['product_name']) ?></div>
                        <div class="text-[10px] text-gray-500 tracking-widest mt-1"><?= htmlspecialchars($item['product_code']) ?></div>
                    </td>
                    <td class="py-4 px-2 text-center"><?= $item['quantity'] ?></td>
                    <td class="py-4 px-2 text-right text-gray-600">$<?= number_format($item['unit_price'], 2) ?></td>
                    <td class="py-4 px-2 text-right font-bold">$<?= number_format($item['total_price'], 2) ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <div class="flex justify-end border-t-2 border-black pt-4 font-mono">
            <div class="w-64 space-y-2">
                <div class="flex justify-between text-sm text-gray-600">
                    <span>SUBTOTAL:</span>
                    <span>$<?= number_format($invoice['subtotal'], 2) ?></span>
                </div>
                
                <?php 
                $total_discount = $invoice['special_discount'] + $invoice['membership_discount'];
                if ($total_discount > 0): 
                ?>
                <div class="flex justify-between text-sm text-premium">
                    <span>DISCOUNT:</span>
                    <span>-$<?= number_format($total_discount, 2) ?></span>
                </div>
                <?php endif; ?>
                
                <div class="flex justify-between text-xl font-black tracking-tighter pt-2 border-t border-gray-200">
                    <span>TOTAL:</span>
                    <span>$<?= number_format($invoice['total_amount'], 2) ?></span>
                </div>
            </div>
        </div>

        <div class="mt-16 text-center border-t border-gray-200 pt-8 font-mono text-[10px] uppercase tracking-widest text-gray-400">
            <p>Thank you for choosing Arai Moto. All parts carry a standard 1-year warranty unless otherwise stated.</p>
            <p class="mt-2">[ END OF TRANSMISSION ]</p>
        </div>

    </div>

    <script>
        window.onload = function() {
            setTimeout(function() {
                // window.print(); // Uncomment this line if you want the print box to open immediately when the page loads
            }, 500);
        }
    </script>
</body>
</html>