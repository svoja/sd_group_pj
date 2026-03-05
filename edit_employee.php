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
<?php
$pageTitle = 'Modify Entity | ARAII MOTO';
include 'partials/head.php';
?>
<body class="bg-white text-black font-sans min-h-screen selection:bg-premium selection:text-white flex flex-col">

    <?php include 'partials/nav.php'; ?>

    <main class="flex-grow flex items-center justify-center p-8">

        <div class="w-full max-w-md bg-white border border-obsidian-edge p-8 relative overflow-hidden shadow-2xl">
            <div class="absolute top-0 right-0 p-2 font-mono text-sm text-premium/30 uppercase tracking-widest">Sys_Edit: Active</div>
            
            <h2 class="text-2xl font-black uppercase tracking-tighter mb-2 border-l-4 border-premium pl-4">Modify_Entity</h2>
            <p class="text-obsidian-muted font-mono text-sm mb-8 pl-5">Target: <?= htmlspecialchars($employee['employee_code']) ?></p>
            
            <form action="actions/update_employee.php" method="POST" class="space-y-6">
                <input type="hidden" name="employee_id" value="<?= $emp_id ?>">

                <div>
                    <label class="block text-sm uppercase tracking-[0.2em] text-obsidian-muted mb-2 font-bold">Identity Update</label>
                    <input type="text" name="name" required value="<?= htmlspecialchars($employee['name']) ?>" 
                           class="w-full bg-white border border-obsidian-edge px-4 py-3 text-sm font-bold focus:outline-none focus:border-premium transition-colors text-black uppercase">
                </div>

                <div>
                    <label class="block text-sm uppercase tracking-[0.2em] text-obsidian-muted mb-2 font-bold">Reassign Position</label>
                    <select name="position" class="w-full bg-white border border-obsidian-edge px-4 py-3 text-sm font-mono focus:outline-none focus:border-premium transition-colors text-black appearance-none">
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
                    <a href="employees.php" class="w-1/3 text-center py-4 border border-obsidian-edge text-obsidian-muted text-sm font-black uppercase tracking-[0.2em] hover:text-black hover:border-white transition-colors">
                        Abort
                    </a>
                    <button type="submit" class="w-2/3 py-4 bg-premium text-white text-sm font-black uppercase tracking-[0.3em] hover:bg-white hover:text-black transition-all duration-300 shadow-[0_0_20px_rgba(176,0,32,0.22)]">
                        Execute Update
                    </button>
                </div>
            </form>
        </div>
    </main>

</body>
</html>