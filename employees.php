<?php
session_start();
require_once "config/database.php";

// 1. STRICT SECURITY GUARD: Kick out anyone who isn't logged in, OR isn't an employee
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'employee') {
    // Send unauthorized users back to the public homepage or login
    header("Location: ../index.php"); 
    exit;
}

$user_id = $_SESSION['user_id'];

// 2. Fetch EMPLOYEE profile data (NOT customer data)
$stmt = $mysqli->prepare("SELECT employee_code, name, position FROM employees WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$employeeProfile = $result->fetch_assoc();

// 3. Set variables to use in your ARAI MOTO header
$name = $employeeProfile['name'] ?? 'UNIDENTIFIED_STAFF';
$position = $employeeProfile['position'] ?? 'UNASSIGNED_UNIT';
$code = $employeeProfile['employee_code'] ?? 'SYS-0000';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personnel Management | ARAI MOTO</title>
    
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
                            bg: '#020202',
                            surface: '#0a0a0a',
                            edge: 'rgba(255, 0, 0, 0.12)',
                            muted: '#666666'
                        },
                        premium: '#e11d48'
                    }
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
        .anim-load {
            animation: tectonicRise 0.8s forwards ease-out;
            opacity: 0;
        }
    </style>
</head>

<body class="bg-obsidian-bg text-white font-sans min-h-screen overflow-x-hidden selection:bg-premium selection:text-white">

    <?php include 'partials/nav.php'; ?>

    <main class="max-w-[1400px] mx-auto py-20 px-8">

        <?php if (isset($_GET['status'])): ?>
            <div class="mb-6 p-4 bg-obsidian-surface border-l-4 border-premium font-mono text-[10px] uppercase tracking-widest anim-load shadow-lg shadow-black">
                <?php 
                    if ($_GET['status'] == 'onboarded') echo "[ STATUS: NEW_ENTITY_AUTHORIZED ]";
                    if ($_GET['status'] == 'updated') echo "[ STATUS: ENTITY_RECALIBRATED_SUCCESSFULLY ]";
                    if ($_GET['status'] == 'decommissioned') echo "[ STATUS: ENTITY_DECOMMISSIONED ]";
                    if ($_GET['status'] == 'db_error') echo "<span class='text-red-500'>[ ERROR: DATABASE_SYNC_FAILURE ]</span>";
                ?>
            </div>
        <?php endif; ?>
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
            
            <div class="lg:col-span-1 anim-load" style="animation-delay: 0.1s;">
                <div class="bg-obsidian-surface border border-obsidian-edge p-8 relative overflow-hidden shadow-2xl shadow-black">
                    <div class="absolute top-0 right-0 p-2 font-mono text-[8px] text-premium/30 uppercase tracking-widest">System_Access: Alpha</div>
                    
                    <h2 class="text-2xl font-black uppercase tracking-tighter mb-8 border-l-4 border-premium pl-4">Onboard_Staff</h2>
                    
                    <form action="actions/add_employee.php" method="POST" class="space-y-6">
                        <div>
                            <label class="block text-[10px] uppercase tracking-[0.2em] text-obsidian-muted mb-2 font-bold">Full Identity</label>
                            <input type="text" name="emp_name" required placeholder="e.g. KENJI ARAI" 
                                class="w-full bg-obsidian-bg border border-obsidian-edge px-4 py-3 text-sm font-mono focus:outline-none focus:border-premium transition-colors text-white">
                        </div>

                        <div>
                            <label class="block text-[10px] uppercase tracking-[0.2em] text-obsidian-muted mb-2 font-bold">Tactical Position</label>
                            <select name="position" class="w-full bg-obsidian-bg border border-obsidian-edge px-4 py-3 text-sm font-mono focus:outline-none focus:border-premium transition-colors text-white appearance-none">
                                <option value="Master Mechanic">Master Mechanic</option>
                                <option value="System Architect">System Architect</option>
                                <option value="Procurement Lead">Procurement Lead</option>
                                <option value="Sales Associate">Sales Associate</option>
                            </select>
                        </div>

                        <button type="submit" class="w-full py-4 bg-premium text-white text-[10px] font-black uppercase tracking-[0.3em] hover:bg-white hover:text-black transition-all duration-300 shadow-[0_0_20px_rgba(225,29,72,0.2)]">
                            Authorize Access
                        </button>
                    </form>
                </div>
            </div>

            <div class="lg:col-span-2 anim-load" style="animation-delay: 0.2s;">
                <div class="bg-obsidian-surface border border-obsidian-edge overflow-hidden shadow-2xl shadow-black">
                    <div class="px-8 py-6 border-b border-obsidian-edge bg-white/[0.02] flex justify-between items-center">
                        <h2 class="text-xl font-black uppercase tracking-tighter">Personnel_Registry</h2>
                        <span class="text-premium font-mono text-[10px] animate-pulse">[ DATA_SECURE ]</span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="text-[10px] uppercase tracking-widest text-obsidian-muted border-b border-obsidian-edge">
                                    <th class="px-8 py-4">Identity</th>
                                    <th class="px-8 py-4">Position</th>
                                    <th class="px-8 py-4 text-right">Operations</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-obsidian-edge">
                                <?php
                                $empQuery = "SELECT * FROM employees ORDER BY is_active DESC";
                                $empResult = $mysqli->query($empQuery);

                                if ($empResult->num_rows > 0):
                                    while($emp = $empResult->fetch_assoc()):
                                        $isActive = (int)$emp['is_active'] === 1;
                                        $statusText = $isActive ? 'OPERATIONAL' : 'OFFLINE';
                                ?>
                                <tr class="group hover:bg-white/[0.01] transition-colors">
                                    <td class="px-8 py-5">
                                        <div class="text-[9px] font-mono text-premium uppercase tracking-widest">
                                            EMP: <?= htmlspecialchars($emp['employee_code']) ?>
                                        </div>
                                        <div class="text-sm font-bold uppercase text-white group-hover:text-premium transition-colors">
                                            <?= htmlspecialchars($emp['name']) ?>
                                        </div>
                                        <div class="text-[9px] font-mono text-obsidian-muted">UID: #<?= str_pad($emp['employee_id'], 3, '0', STR_PAD_LEFT) ?></div>
                                    </td>
                                    <td class="px-8 py-5 text-[10px] font-mono uppercase text-obsidian-muted">
                                        [ <?= htmlspecialchars($emp['position']) ?> ]
                                    </td>

                                    <td class="px-8 py-5">
                                    <?php if($isActive): ?>
                                        <div class="flex items-center gap-2 text-[9px] font-black uppercase tracking-widest text-green-500">
                                            <span class="relative flex h-1.5 w-1.5">
                                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                                                <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-green-500"></span>
                                            </span>
                                            <?= $statusText ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="flex items-center gap-2 text-[9px] font-black uppercase tracking-widest text-red-600">
                                            <span class="h-1.5 w-1.5 rounded-full bg-red-600"></span>
                                            <?= $statusText ?>
                                        </div>
                                    <?php endif; ?>
                                    </td>

                                    <td class="px-8 py-5 text-right">
                                        <div class="flex justify-end gap-4">
                                            <?php if($isActive): ?>
                                                <a href="edit_employee.php?id=<?= $emp['employee_id'] ?>" class="text-[10px] font-black uppercase tracking-widest text-white/50 hover:text-white transition-colors">Edit</a>
                                                <a href="actions/soft_delete.php?id=<?= $emp['employee_id'] ?>" 
                                                onclick="return confirm('Decommission this entity?')"
                                                class="text-[10px] font-black uppercase tracking-widest text-premium hover:underline transition-colors">Drop</a>
                                            <?php else: ?>
                                                <span class="text-[10px] font-black uppercase tracking-widest text-obsidian-muted italic">System_Locked</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>

                                </tr>
                                <?php endwhile; else: ?>
                                <tr>
                                    <td colspan="3" class="px-8 py-10 text-center text-obsidian-muted font-mono text-xs">No active personnel found.</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

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
</html>