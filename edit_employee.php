<?php
session_start();
require_once "config/database.php";

// Security Check
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'employee') {
    header("Location: login.php");
    exit;
}

// Check if ID is provided
if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit;
}

$emp_id = $_GET['id'];

// Fetch current employee data
$stmt = $mysqli->prepare("SELECT employee_code, name, position FROM employees WHERE employee_id = ?");
$stmt->bind_param("i", $emp_id);
$stmt->execute();
$result = $stmt->get_result();
$employee = $result->fetch_assoc();

if (!$employee) {
    header("Location: dashboard.php?status=error");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modify Entity | ARAI MOTO</title>
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
<body class="bg-obsidian-bg text-white font-sans min-h-screen selection:bg-premium selection:text-white flex flex-col">

    <?php include 'partials/nav.php'; ?>

    <main class="flex-grow flex items-center justify-center p-8">

        <div class="w-full max-w-md bg-obsidian-surface border border-obsidian-edge p-8 relative overflow-hidden shadow-2xl">
            <div class="absolute top-0 right-0 p-2 font-mono text-[8px] text-premium/30 uppercase tracking-widest">Sys_Edit: Active</div>
            
            <h2 class="text-2xl font-black uppercase tracking-tighter mb-2 border-l-4 border-premium pl-4">Modify_Entity</h2>
            <p class="text-obsidian-muted font-mono text-xs mb-8 pl-5">Target: <?= htmlspecialchars($employee['employee_code']) ?></p>
            
            <form action="actions/update_employee.php" method="POST" class="space-y-6">
                <input type="hidden" name="employee_id" value="<?= $emp_id ?>">

                <div>
                    <label class="block text-[10px] uppercase tracking-[0.2em] text-obsidian-muted mb-2 font-bold">Identity Update</label>
                    <input type="text" name="name" required value="<?= htmlspecialchars($employee['name']) ?>" 
                           class="w-full bg-obsidian-bg border border-obsidian-edge px-4 py-3 text-sm font-bold focus:outline-none focus:border-premium transition-colors text-white uppercase">
                </div>

                <div>
                    <label class="block text-[10px] uppercase tracking-[0.2em] text-obsidian-muted mb-2 font-bold">Reassign Position</label>
                    <select name="position" class="w-full bg-obsidian-bg border border-obsidian-edge px-4 py-3 text-sm font-mono focus:outline-none focus:border-premium transition-colors text-white appearance-none">
                        <?php 
                        $positions = ["Master Mechanic", "System Architect", "Procurement Lead", "Sales Associate", "UNASSIGNED_UNIT"];
                        foreach ($positions as $pos) {
                            $selected = ($employee['position'] === $pos) ? 'selected' : '';
                            echo "<option value='$pos' $selected>$pos</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="flex gap-4 pt-4">
                    <a href="employees.php" class="w-1/3 text-center py-4 border border-obsidian-edge text-obsidian-muted text-[10px] font-black uppercase tracking-[0.2em] hover:text-white hover:border-white transition-colors">
                        Abort
                    </a>
                    <button type="submit" class="w-2/3 py-4 bg-premium text-white text-[10px] font-black uppercase tracking-[0.3em] hover:bg-white hover:text-black transition-all duration-300 shadow-[0_0_20px_rgba(225,29,72,0.2)]">
                        Execute Update
                    </button>
                </div>
            </form>
        </div>
    </main>

</body>
</html>