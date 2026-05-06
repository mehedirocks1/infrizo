<?php
// Ensure the database configuration is loaded and the variable is accessible
require_once '../includes/config.php';
global $pdo; 

// Fetch the 5 most recent inquiries (with the specific software product they asked about)
$recent_inquiries = $pdo->query("
    SELECT i.*, p.name as product_name
    FROM inquiries i
    LEFT JOIN products p ON i.product_id = p.id
    ORDER BY i.created_at DESC
    LIMIT 5
")->fetchAll();
?>

<div class="flex justify-between items-end mb-8">
    <h1 class="text-4xl font-robot font-bold text-slate-900">SYSTEM OVERVIEW.</h1>
    <div class="text-[10px] font-bold text-cyan-600 tracking-[0.2em] animate-pulse">
        [ LIVE DATA FEED ACTIVE ]
    </div>
</div>

<!-- STATS HUD GRID -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
    
    <!-- Card 1: Assets (Products) -->
    <div class="sci-fi-card p-6" style="border-color: rgba(2, 132, 199, 0.4)">
        <div class="text-[10px] text-cyan-600 font-bold tracking-widest mb-2 flex items-center gap-2">
            <span class="w-1.5 h-1.5 bg-cyan-500 rounded-full"></span> ACTIVE ASSETS
        </div>
        <div class="text-4xl font-robot font-bold text-slate-900">
            <?= $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn(); ?>
        </div>
    </div>
    
    <!-- Card 2: Directories (Categories) -->
    <div class="sci-fi-card p-6" style="border-color: rgba(124, 58, 237, 0.4)">
        <div class="text-[10px] text-purple-600 font-bold tracking-widest mb-2 flex items-center gap-2">
            <span class="w-1.5 h-1.5 bg-purple-500 rounded-full"></span> DIRECTORIES
        </div>
        <div class="text-4xl font-robot font-bold text-slate-900">
            <?= $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn(); ?>
        </div>
    </div>
    
    <!-- Card 3: Inquiries (Contact Forms) -->
    <div class="sci-fi-card p-6" style="border-color: rgba(225, 29, 72, 0.4)">
        <div class="text-[10px] text-red-600 font-bold tracking-widest mb-2 flex items-center gap-2">
            <span class="w-1.5 h-1.5 bg-red-500 rounded-full animate-pulse"></span> UNREAD COMM_LINKS
        </div>
        <div class="text-4xl font-robot font-bold text-slate-900">
            <?= $pdo->query("SELECT COUNT(*) FROM inquiries WHERE status = 'Unread'")->fetchColumn(); ?>
        </div>
    </div>

    <!-- Card 4: Orders (Checkout) -->
    <div class="sci-fi-card p-6" style="border-color: rgba(16, 185, 129, 0.4)">
        <div class="text-[10px] text-emerald-600 font-bold tracking-widest mb-2 flex items-center gap-2">
            <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full"></span> SECURE TRANSACTIONS
        </div>
        <div class="text-4xl font-robot font-bold text-slate-900">
            <?= $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn(); ?>
        </div>
    </div>

</div>

<!-- RECENT ACTIVITY LOG -->
<div class="sci-fi-card p-6 bg-white/80" style="border-color: rgba(2, 132, 199, 0.2)">
    <div class="flex justify-between items-center mb-6 pb-2 border-b border-cyan-100">
        <h3 class="font-robot font-bold text-2xl text-slate-900">RECENT COMMUNICATIONS LOG</h3>
        <a href="#" class="text-[10px] text-cyan-600 hover:text-cyan-400 font-bold tracking-widest uppercase">VIEW ALL ↗</a>
    </div>

    <table class="w-full text-left admin-table">
        <thead>
            <tr>
                <th class="py-3 px-4 text-xs tracking-widest">Timestamp</th>
                <th class="py-3 px-4 text-xs tracking-widest">Client Designation</th>
                <th class="py-3 px-4 text-xs tracking-widest">Target Module</th>
                <th class="py-3 px-4 text-xs tracking-widest text-right">Status</th>
            </tr>
        </thead>
        <tbody class="text-sm font-bold text-slate-600">
            <?php if(empty($recent_inquiries)): ?>
                <tr>
                    <td colspan="4" class="py-8 text-center text-slate-400 font-normal tracking-widest text-xs">
                        [ NO INCOMING TRANSMISSIONS DETECTED ]
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach($recent_inquiries as $inq): ?>
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="py-4 px-4 text-slate-500 font-normal text-[11px] tracking-wider">
                        <?= date('Y-m-d H:i', strtotime($inq['created_at'])) ?>
                    </td>
                    <td class="py-4 px-4 text-slate-900">
                        <?= htmlspecialchars($inq['client_name']) ?>
                        <div class="text-[10px] text-slate-400 font-normal mt-0.5"><?= htmlspecialchars($inq['company_name'] ?? 'Independent') ?></div>
                    </td>
                    <td class="py-4 px-4 text-cyan-700">
                        <?= htmlspecialchars($inq['product_name'] ?? 'General Inquiry') ?>
                    </td>
                    <td class="py-4 px-4 text-right">
                        <?php if($inq['status'] == 'Unread'): ?>
                            <span class="px-2 py-1 bg-red-50 text-red-600 text-[9px] tracking-[0.2em] border border-red-200 animate-pulse">
                                UNREAD
                            </span>
                        <?php else: ?>
                            <span class="px-2 py-1 bg-slate-100 text-slate-500 text-[9px] tracking-[0.2em] border border-slate-200">
                                <?= strtoupper($inq['status']) ?>
                            </span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>