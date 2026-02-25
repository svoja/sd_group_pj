<?php
session_start();

// 1. Auth Guard
if (!isset($_SESSION['logged_in'])) { header("Location: login.php"); exit; }
if ($_SESSION['role'] !== 'employee') { header("Location: index.php"); exit; }

require_once "../config/database.php";

$query = "SELECT c.customer_id, c.customer_code, c.contact_name, c.membership_level, c.created_at, u.email 
          FROM customers c
          JOIN users u ON c.user_id = u.user_id
          ORDER BY c.created_at DESC";

$result = $mysqli->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entity Registry | ARAI MOTO</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'obsidian-bg': '#050505',
                        'obsidian-surface': '#0a0a0a',
                        'obsidian-edge': '#1a1a1a',
                        'obsidian-muted': '#555',
                        'premium': '#a855f7',
                    }
                }
            }
        }
    </script>
    <style>
        /* Custom animation for the "Boot" effect */
        @keyframes slideUpFade {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .anim-load {
            animation: slideUpFade 0.8s ease-out forwards;
        }

        /* Delaying the table body rows for a staggered entrance */
        .stagger-row {
            opacity: 0;
            animation: slideUpFade 0.5s ease-out forwards;
        }
    </style>
</head>
<body class="bg-obsidian-bg text-white font-sans min-h-screen">

    <?php include 'partials/nav.php'; ?>

    <main class="max-w-[1400px] mx-auto py-16 px-8">
        
        <header class="mb-12 flex flex-col md:flex-row md:items-end justify-between gap-6 anim-load" style="animation-delay: 0.1s;">
            <div class="border-l-2 border-premium pl-6">
                <p class="text-premium uppercase tracking-[0.4em] text-[10px] mb-2 font-bold">// DATABASE_MANAGEMENT</p>
                <h1 class="text-4xl font-black tracking-tighter uppercase">Entity Registry</h1>
            </div>
            
            <button class="px-8 py-4 bg-white text-black text-[10px] font-black uppercase tracking-[0.2em] hover:bg-premium hover:text-white transition-colors">
                + Add New Entry
            </button>
        </header>

        <div class="mb-6 flex gap-4 anim-load" style="animation-delay: 0.2s;">
            <div class="relative flex-1">
                <input type="text" placeholder="SEARCH_BY_IDENTITY..." class="w-full bg-obsidian-surface border border-obsidian-edge px-4 py-3 text-xs font-mono focus:outline-none focus:border-premium transition-colors">
            </div>
            <select class="bg-obsidian-surface border border-obsidian-edge px-4 py-3 text-[10px] uppercase font-bold text-obsidian-muted focus:outline-none focus:border-premium">
                <option>All Clearance Levels</option>
                <option>Premium</option>
                <option>Standard</option>
            </select>
        </div>

        <div class="bg-obsidian-surface border border-obsidian-edge shadow-2xl anim-load" style="animation-delay: 0.3s;">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-white/[0.03] border-b border-obsidian-edge">
                            <th class="px-6 py-4 text-[10px] uppercase tracking-widest text-obsidian-muted">Registry_ID</th>
                            <th class="px-6 py-4 text-[10px] uppercase tracking-widest text-obsidian-muted">Entity_Name</th>
                            <th class="px-6 py-4 text-[10px] uppercase tracking-widest text-obsidian-muted">Network_Email</th>
                            <th class="px-6 py-4 text-[10px] uppercase tracking-widest text-obsidian-muted">Membership_Tier</th>
                            <th class="px-6 py-4 text-[10px] uppercase tracking-widest text-obsidian-muted">Created_At</th>
                            <th class="px-6 py-4 text-[10px] uppercase tracking-widest text-obsidian-muted text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-obsidian-edge">
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): 
                                $date = date("Y-m-d", strtotime($row['created_at']));
                                $i = 0; // Row index for staggering
                                $i++;
                                // This adds a tiny delay to every row so they "pop" in one by one
                                $delay = 0.3 + ($i * 0.05);
                            ?>
                            <tr class="hover:bg-white/[0.02] transition-colors group stagger-row" style="animation-delay: <?= $delay ?>s;">
                                <td class="px-6 py-5 font-mono text-xs text-premium tracking-tighter">
                                    [#<?= htmlspecialchars($row['customer_code']) ?>]
                                </td>
                                <td class="px-6 py-5 text-sm font-bold uppercase tracking-tight text-white">
                                    <?= htmlspecialchars($row['contact_name']) ?>
                                </td>
                                <td class="px-6 py-5 text-xs text-obsidian-muted font-mono">
                                    <?= htmlspecialchars($row['email']) ?>
                                </td>
                                <td class="px-6 py-5">
                                    <?php if($row['membership_level'] === 'PREMIUM'): ?>
                                        <div class="inline-block px-2 py-1 bg-premium/10 border border-premium/30 text-premium text-[9px] font-black uppercase tracking-widest">
                                            Premium
                                        </div>
                                    <?php else: ?>
                                        <div class="inline-block px-2 py-1 bg-white/5 border border-white/10 text-obsidian-muted text-[9px] font-black uppercase tracking-widest">
                                            Standard
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-5 font-mono text-[11px] text-obsidian-muted italic">
                                    <?= $date ?>
                                </td>
                                <td class="px-6 py-5 text-right">
                                    <div class="flex justify-end gap-2 opacity-30 group-hover:opacity-100 transition-opacity">
                                        <button class="px-3 py-1 border border-obsidian-edge hover:border-white text-[9px] uppercase font-bold transition-colors">Edit</button>
                                        <button class="px-3 py-1 border border-obsidian-edge hover:border-red-500 hover:text-red-500 text-[9px] uppercase font-bold transition-colors">Drop</button>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="mt-4 flex justify-between items-center text-[10px] font-mono text-obsidian-muted uppercase tracking-[0.2em] anim-load" style="animation-delay: 0.4s;">
            <span>Total Entities: <?= $result->num_rows ?></span>
            <span>Last Sync: <?= date('H:i:s') ?></span>
        </div>
    </main>

</body>
</html>