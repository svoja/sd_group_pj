<?php
session_start();
require_once "config/database.php";

// Security Check
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'employee') {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: customers.php");
    exit;
}

$customer_id = intval($_GET['id']);

// Fetch current client data
$stmt = $mysqli->prepare("SELECT * FROM customers WHERE customer_id = ?");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();
$customer = $result->fetch_assoc();

if (!$customer) {
    header("Location: customers.php?status=db_error");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<?php
$pageTitle = 'Recalibrate Client | ARAII MOTO';
include 'partials/head.php';
?>
<body class="bg-white text-black font-sans min-h-screen flex flex-col selection:bg-premium selection:text-white">

    <?php include 'partials/nav.php'; ?>

    <main class="flex-grow flex items-center justify-center p-8">
        <div class="w-full max-w-xl bg-white border border-obsidian-edge p-8 relative shadow-2xl">
            <div class="absolute top-0 right-0 p-2 font-mono text-sm text-premium/30 uppercase tracking-widest">Sys_Edit: Active</div>
            
            <h2 class="text-2xl font-black uppercase tracking-tighter mb-2 border-l-4 border-premium pl-4">Recalibrate_Client</h2>
            <p class="text-obsidian-muted font-mono text-sm mb-8 pl-5">Target Code: <span class="text-premium"><?= htmlspecialchars($customer['customer_code']) ?></span></p>
            
            <form action="actions/update_customer.php" method="POST" class="space-y-6">
                <input type="hidden" name="customer_id" value="<?= $customer_id ?>">

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm uppercase tracking-[0.2em] text-obsidian-muted mb-2 font-bold">Client Identity</label>
                        <input type="text" name="contact_name" required value="<?= htmlspecialchars($customer['contact_name']) ?>" 
                               class="w-full bg-white border border-obsidian-edge px-4 py-3 text-sm font-bold focus:outline-none focus:border-premium transition-colors text-black uppercase">
                    </div>
                    <div>
                        <label class="block text-sm uppercase tracking-[0.2em] text-obsidian-muted mb-2 font-bold">Membership Tier</label>
                        <select name="membership_level" class="w-full bg-white border border-obsidian-edge px-4 py-3 text-sm font-mono focus:outline-none focus:border-premium transition-colors text-black appearance-none">
                            <?php 
                            $tiers = ["STANDARD", "PREMIUM"];
                            foreach ($tiers as $tier) {
                                $selected = ($customer['membership_level'] === $tier) ? 'selected' : '';
                                echo "<option value='$tier' $selected>$tier</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm uppercase tracking-[0.2em] text-obsidian-muted mb-2 font-bold">Base Location / Address</label>
                    <textarea name="address" rows="3" required 
                              class="w-full bg-white border border-obsidian-edge px-4 py-3 text-sm font-mono focus:outline-none focus:border-premium transition-colors text-black resize-none"><?= htmlspecialchars($customer['address']) ?></textarea>
                </div>

                <div class="flex gap-4 pt-4">
                    <a href="customers.php" class="w-1/3 text-center py-4 border border-obsidian-edge text-obsidian-muted text-sm font-black uppercase tracking-[0.2em] hover:text-black hover:border-white transition-colors">
                        Abort
                    </a>
                    <button type="submit" class="w-2/3 py-4 bg-premium text-white text-sm font-black uppercase tracking-[0.3em] hover:bg-white hover:text-black transition-all duration-300 shadow-[0_0_20px_rgba(176,0,32,0.22)]">
                        Execute Recalibration
                    </button>
                </div>
            </form>
        </div>
    </main>
</body>
</html>
