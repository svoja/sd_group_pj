<?php
// 1. Start session and check login status
session_start();

if (!isset($_SESSION['logged_in'])) {
    header("Location: login.php");
    exit;
}
// 3. Connect to the database
require_once "../config/database.php";

$user_id = $_SESSION['user_id'];
$email = $_SESSION['email'];

$stmt = $mysqli->prepare("SELECT customer_code, contact_name, address, membership_level FROM customers WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$customerProfile = $result->fetch_assoc();

$name = $customerProfile['contact_name'] ?? 'Valued Customer';
$level = $customerProfile['membership_level'] ?? 'STANDARD';
$code = $customerProfile['customer_code'] ?? 'N/A';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard | ARAI MOTO</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;700;900&family=JetBrains+Mono:wght@400;700&display=swap" rel="stylesheet">
    
    <script src="https://cdn.tailwindcss.com"></script>
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        mono: ['JetBrains Mono', 'monospace'],
                    },
                    colors: {
                        obsidian: {
                            bg: '#050505',
                            surface: '#0a0a0a',
                            edge: 'rgba(255, 255, 255, 0.08)',
                            muted: '#555555'
                        },
                        premium: '#a855f7'
                    }
                }
            }
        }
    </script>

    <style>
        body {
            background-image: 
                radial-gradient(circle at 20% 30%, rgba(168, 85, 247, 0.03) 0%, transparent 40%),
                radial-gradient(circle at 80% 70%, rgba(59, 130, 246, 0.03) 0%, transparent 40%);
        }

        /* Sharp geometric corner accent */
        .slab::after {
            content: '';
            position: absolute;
            top: 0; right: 0;
            width: 50px; height: 50px;
            background: linear-gradient(225deg, rgba(255,255,255,0.05) 50%, transparent 50%);
        }

        /* Custom Badge Shape */
        .clip-badge {
            clip-path: polygon(0 0, 90% 0, 100% 30%, 100% 100%, 10% 100%, 0 70%);
        }

        /* Tectonic Rise Animations */
        @keyframes tectonicRise {
            from { opacity: 0; transform: translateY(40px) scale(0.98); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }
        .anim-hero { animation: tectonicRise 0.8s forwards ease-out; opacity: 0; }
        .stagger-1 { animation: tectonicRise 0.8s 0.1s forwards cubic-bezier(0.23, 1, 0.32, 1); opacity: 0; }
        .stagger-2 { animation: tectonicRise 0.8s 0.2s forwards cubic-bezier(0.23, 1, 0.32, 1); opacity: 0; }
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
                    <p class="text-white text-sm uppercase tracking-[0.3em] font-bold">Official Supplier</p>
                    <p class="text-obsidian-muted text-[10px] uppercase tracking-widest mt-1">Obsidian Performance Division</p>
                </div>
                
                <div class="h-12 w-px bg-premium hidden md:block"></div>
                
                <div class="text-left font-mono text-xs">
                    <p class="text-premium animate-pulse">[ AUTH_SESSION: ACTIVE ]</p>
                    <p class="text-obsidian-muted uppercase mt-1">Clearance: <?= htmlspecialchars($level) ?></p>
                </div>
            </div>
        </div>

        <div class="absolute top-10 left-10 w-20 h-20 border-t border-l border-white/10"></div>
        <div class="absolute bottom-10 right-10 w-20 h-20 border-b border-r border-white/10"></div>
    </header>

    <main class="max-w-[1400px] mx-auto py-20 px-8">
        
        <div class="flex items-end justify-between mb-12 stagger-1 border-l-4 border-premium pl-6">
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
            // 1. Fetch products from database
            $productQuery = "SELECT product_id, product_name, selling_price, product_description, image_path FROM products ORDER BY product_id DESC LIMIT 6";
            $productResult = $mysqli->query($productQuery);

            if ($productResult && $productResult->num_rows > 0):
                while($product = $productResult->fetch_assoc()):
                    // 2. Prepare product data
                    $pName = htmlspecialchars($product['product_name']);
                    $pPrice = number_format($product['selling_price'], 2);
                    $pDesc = htmlspecialchars($product['product_description']);
                    $pId = $product['product_id'];
                    
                    // 3. Image Logic
                    $img_filename = $product['image_path']; 
                    $img_path = "assets/products/" . $img_filename;
                    $hasImage = (!empty($img_filename) && file_exists($img_path));
            ?>
            
            <div class="slab stagger-2 group relative bg-obsidian-surface border border-obsidian-edge p-3 overflow-hidden transition-all duration-500 hover:border-premium/40">
                
                <div class="aspect-square bg-obsidian-bg border border-obsidian-edge overflow-hidden relative">
                    <?php if ($hasImage): ?>
                        <img src="<?= $img_path ?>" 
                            class="w-full h-full object-cover opacity-80 group-hover:opacity-100 group-hover:scale-110 transition-all duration-700"
                            alt="<?= $pName ?>">
                    <?php else: ?>
                        <div class="absolute inset-0 flex items-center justify-center text-obsidian-muted/20 group-hover:text-premium/20 transition-colors">
                            <svg width="120" height="120" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="0.5">
                                <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path>
                            </svg>
                        </div>
                    <?php endif; ?>

                    <div class="absolute bottom-4 left-4 bg-obsidian-bg/80 backdrop-blur-md px-3 py-1 border border-obsidian-edge">
                        <span class="text-[9px] font-mono tracking-tighter text-obsidian-muted uppercase">
                            Serial: #MOTO-<?= str_pad($pId, 4, '0', STR_PAD_LEFT) ?>
                        </span>
                    </div>
                </div>
                
                <div class="p-6">
                    <div class="flex justify-between items-start mb-2">
                        <h3 class="text-xl font-bold uppercase tracking-tight group-hover:text-premium transition-colors">
                            <?= $pName ?>
                        </h3>
                        <span class="font-mono text-premium text-lg tracking-tighter font-bold">
                            $<?= $pPrice ?>
                        </span>
                    </div>
                    <p class="text-obsidian-muted text-[11px] uppercase tracking-widest mb-8 leading-relaxed line-clamp-2">
                        <?= $pDesc ?>
                    </p>
                    
                    <button class="w-full group/btn relative overflow-hidden bg-white py-4 transition-all duration-300 hover:bg-premium">
                        <span class="relative z-10 text-black text-[10px] font-black uppercase tracking-[0.3em] group-hover/btn:text-white">
                            Add to Build
                        </span>
                    </button>
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

    <style>
        @keyframes spin-slow {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        .animate-spin-slow {
            animation: spin-slow 20s linear infinite;
        }
    </style>

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