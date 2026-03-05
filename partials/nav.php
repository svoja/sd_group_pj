<?php
$uiTheme = $_SESSION['ui_theme'] ?? 'light';
$uiBtnSize = $_SESSION['ui_btn_size'] ?? 'md';
$themeOptions = ['light', 'dark', 'mono'];
$sizeOptions = ['sm', 'md', 'lg'];

if (!in_array($uiTheme, $themeOptions, true)) {
    $uiTheme = 'light';
}
if (!in_array($uiBtnSize, $sizeOptions, true)) {
    $uiBtnSize = 'md';
}
?>

<script>
    document.body.setAttribute('data-theme', '<?= htmlspecialchars($uiTheme, ENT_QUOTES) ?>');
    document.body.setAttribute('data-btn-size', '<?= htmlspecialchars($uiBtnSize, ENT_QUOTES) ?>');
</script>

<nav class="sticky top-0 z-50 min-h-[56px] flex items-center justify-between gap-3 px-3 xl:px-6 py-2 border-b border-obsidian-edge bg-white/95 backdrop-blur-md">
    <div class="font-black uppercase tracking-[0.18em] text-base text-black">
        ARAII
    </div>

    <div class="hidden xl:flex items-center gap-4">
        <a href="index.php" class="text-xs uppercase tracking-[0.18em] transition-colors <?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'text-black' : 'text-obsidian-muted hover:text-black' ?>">
            [01] Dashboard
        </a>
        <a href="employees.php" class="text-xs uppercase tracking-[0.18em] transition-colors <?= basename($_SERVER['PHP_SELF']) == 'employees.php' ? 'text-black' : 'text-obsidian-muted hover:text-black' ?>">
            [02] Employees
        </a>
        <a href="products.php" class="text-xs uppercase tracking-[0.18em] transition-colors <?= basename($_SERVER['PHP_SELF']) == 'products.php' ? 'text-black' : 'text-obsidian-muted hover:text-black' ?>">
            [03] Products
        </a>
        <a href="customers.php" class="text-xs uppercase tracking-[0.18em] transition-colors <?= basename($_SERVER['PHP_SELF']) == 'customers.php' ? 'text-black' : 'text-obsidian-muted hover:text-black' ?>">
            [04] Customers
        </a>
        <a href="sales.php" class="text-xs uppercase tracking-[0.18em] transition-colors <?= basename($_SERVER['PHP_SELF']) == 'sales.php' ? 'text-black' : 'text-obsidian-muted hover:text-black' ?>">
            [05] Sales
        </a>
        <a href="invoices.php" class="text-xs uppercase tracking-[0.18em] transition-colors <?= basename($_SERVER['PHP_SELF']) == 'invoices.php' ? 'text-black' : 'text-obsidian-muted hover:text-black' ?>">
            [06] Invoices
        </a>
    </div>

    <div class="hidden lg:flex items-center gap-5 text-xs uppercase tracking-widest">
        <form action="actions/set_currency.php" method="POST" class="flex items-center gap-2">
            <span class="text-xs text-obsidian-muted font-mono uppercase tracking-widest">Sys_Currency:</span>
            <select name="currency" onchange="this.form.submit()" class="bg-white border border-obsidian-edge text-xs font-mono text-premium uppercase tracking-widest px-2 py-1 focus:outline-none focus:border-black transition-colors cursor-pointer">
                <option value="USD" <?= $_SESSION['currency'] == 'USD' ? 'selected' : '' ?>>USD ($)</option>
                <option value="THB" <?= $_SESSION['currency'] == 'THB' ? 'selected' : '' ?>>THB (฿)</option>
                <option value="JPY" <?= $_SESSION['currency'] == 'JPY' ? 'selected' : '' ?>>JPY (¥)</option>
            </select>
            <input type="hidden" name="return_url" value="<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>">
        </form>

        <div class="flex flex-col items-end">
            <span class="text-xs text-obsidian-muted leading-none">Access_Level</span>
            <span class="text-black text-xs font-mono"><?= htmlspecialchars($_SESSION['role'] ?? 'guest') ?></span>
        </div>

        <a href="logout.php" class="text-red-500 border border-red-500/20 px-3 py-1.5 text-xs font-bold tracking-tighter transition-all duration-300 hover:bg-red-500 hover:text-black hover:shadow-[0_0_15px_rgba(255,77,77,0.3)]">
            Logout
        </a>
    </div>
</nav>
