<nav class="sticky top-0 z-50 h-[70px] flex items-center justify-between px-8 border-b border-obsidian-edge bg-[#050505]/80 backdrop-blur-md">
    <div class="font-black uppercase tracking-[0.2em] text-lg bg-gradient-to-b from-white to-[#444] bg-clip-text text-transparent">
        ARAI
    </div>

    <div class="hidden lg:flex items-center gap-6">
        <a href="index.php" class="text-[10px] uppercase tracking-[0.2em] transition-colors <?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'text-white' : 'text-obsidian-muted hover:text-white' ?>">
            [01] Dashboard
        </a>
        <a href="employees.php" class="text-[10px] uppercase tracking-[0.2em] transition-colors <?= basename($_SERVER['PHP_SELF']) == 'employees.php' ? 'text-white' : 'text-obsidian-muted hover:text-white' ?>">
            [02] Employees
        </a>
        <a href="products.php" class="text-[10px] uppercase tracking-[0.2em] transition-colors <?= basename($_SERVER['PHP_SELF']) == 'products.php' ? 'text-white' : 'text-obsidian-muted hover:text-white' ?>">
            [03] Products
        </a>
        <a href="customers.php" class="text-[10px] uppercase tracking-[0.2em] transition-colors <?= basename($_SERVER['PHP_SELF']) == 'customers.php' ? 'text-white' : 'text-obsidian-muted hover:text-white' ?>">
            [04] Customers
        </a>
        <a href="sales.php" class="text-[10px] uppercase tracking-[0.2em] transition-colors <?= basename($_SERVER['PHP_SELF']) == 'sales.php' ? 'text-white' : 'text-obsidian-muted hover:text-white' ?>">
            [05] Sales
        </a>
        <a href="invoices.php" class="text-[10px] uppercase tracking-[0.2em] transition-colors <?= basename($_SERVER['PHP_SELF']) == 'invoices.php' ? 'text-white' : 'text-obsidian-muted hover:text-white' ?>">
            [06] Invoices
        </a>
    </div>

    <div class="hidden md:flex items-center gap-8 text-xs uppercase tracking-widest">
        <div class="flex flex-col items-end">
            <span class="text-[9px] text-obsidian-muted leading-none">Access_Level</span>
            <span class="text-white font-mono"><?= htmlspecialchars($_SESSION['role'] ?? 'guest') ?></span>
        </div>
        
        <a href="logout.php" class="text-red-500 border border-red-500/20 px-4 py-2 text-[10px] font-bold tracking-tighter transition-all duration-300 hover:bg-red-500 hover:text-white hover:shadow-[0_0_15px_rgba(255,77,77,0.3)]">
            Logout
        </a>
    </div>
</nav>