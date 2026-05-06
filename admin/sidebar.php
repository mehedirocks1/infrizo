<!-- ADMIN SIDEBAR -->
<aside class="admin-sidebar w-64 h-full relative z-20 flex flex-col pt-8">
  <div class="px-6 mb-12">
      <div class="font-robot font-bold text-3xl tracking-widest text-white flex items-center gap-2">
          <span class="text-cyan-500">⟨</span>CMD<span class="text-cyan-500">⟩</span>
      </div>
      <div class="text-[10px] text-cyan-600 mt-1 tracking-[0.2em] font-bold animate-pulse">
          USER: <?= htmlspecialchars($_SESSION['admin_username']) ?>
      </div>
  </div>

  <nav class="flex-1 flex flex-col gap-2 px-4 py-6">
    <!-- SYSTEM OVERVIEW -->
    <a href="?page=dashboard" class="<?= $page == 'dashboard' ? 'bg-cyan-900/40 border-l-2 border-cyan-500' : 'hover:bg-slate-800' ?> text-slate-300 px-4 py-3 text-sm tracking-widest font-bold uppercase transition-all group">
        <span class="text-cyan-500 mr-2 group-hover:animate-pulse">[~]</span> System Status
    </a>

    <!-- DIRECTORIES (Categories) -->
    <a href="?page=categories" class="<?= $page == 'categories' ? 'bg-purple-900/40 border-l-2 border-purple-500' : 'hover:bg-slate-800' ?> text-slate-300 px-4 py-3 text-sm tracking-widest font-bold uppercase transition-all group">
        <span class="text-purple-500 mr-2 group-hover:rotate-90 transition-transform inline-block">[#]</span> Directories
    </a>

    <!-- ASSET MANAGEMENT -->
    <a href="?page=products" class="<?= $page == 'products' ? 'bg-cyan-900/40 border-l-2 border-cyan-500' : 'hover:bg-slate-800' ?> text-slate-300 px-4 py-3 text-sm tracking-widest font-bold uppercase transition-all group">
        <span class="text-cyan-500 mr-2">[*]</span> Asset Database
    </a>

    <a href="?page=add_product" class="<?= $page == 'add_product' ? 'bg-cyan-900/40 border-l-2 border-cyan-500' : 'hover:bg-slate-800' ?> text-slate-300 px-4 py-3 text-sm tracking-widest font-bold uppercase transition-all group">
        <span class="text-cyan-500 mr-2">[+]</span> Deploy Asset
    </a>

    <!-- TRANSACTION MATRIX -->
    <?php
        // Optional: Quick count for the badge
        $newOrders = $pdo->query("SELECT COUNT(*) FROM orders WHERE order_status = 'Processing'")->fetchColumn();
    ?>
    <a href="?page=orders" class="<?= $page == 'orders' ? 'bg-green-900/40 border-l-2 border-green-500' : 'hover:bg-slate-800' ?> text-slate-300 px-4 py-3 text-sm tracking-widest font-bold uppercase transition-all flex justify-between items-center group">
        <span>
            <span class="text-green-500 mr-2">[!]</span> Order Matrix
        </span>
        <?php if($newOrders > 0): ?>
            <span class="bg-green-600 text-slate-900 text-[9px] px-1.5 py-0.5 animate-pulse font-mono"><?= $newOrders ?>_NEW</span>
        <?php endif; ?>
    </a>

    <!-- INCOMING COMMS (Inquiries) -->
    <a href="?page=inquiries" class="<?= $page == 'inquiries' ? 'bg-orange-900/40 border-l-2 border-orange-500' : 'hover:bg-slate-800' ?> text-slate-300 px-4 py-3 text-sm tracking-widest font-bold uppercase transition-all group">
        <span class="text-orange-500 mr-2">[?]</span> Incoming Comms
    </a>

    <div class="mt-auto pt-10">
        <a href="logout.php" class="text-red-500/60 hover:text-red-500 px-4 py-3 text-[10px] tracking-[0.3em] font-bold uppercase transition-all flex items-center gap-2 border-t border-slate-800">
            <span class="animate-pulse">●</span> Terminate_Session
        </a>
    </div>
</nav>

  <div class="p-4 border-t border-slate-700 text-[10px] text-slate-500 tracking-widest font-bold">
      <a href="logout.php" class="text-red-500 hover:text-red-400 block mb-2 transition-colors">TERMINATE_SESSION (Logout)</a>
      <a href="../index.php" target="_blank" class="hover:text-cyan-400 block mb-2">↗ VIEW FRONTEND</a>
  </div>
</aside>