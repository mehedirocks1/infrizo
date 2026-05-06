<?php
// Fetch products joined with their category names
$stmt = $pdo->query("
    SELECT p.id, p.sku, p.name, p.price_display, p.status, p.image, c.name as category_name 
    FROM products p 
    JOIN categories c ON p.category_id = c.id 
    ORDER BY p.id DESC
");
$products = $stmt->fetchAll();
?>

<div class="flex justify-between items-end mb-8">
    <h1 class="text-4xl font-robot font-bold text-slate-900 uppercase tracking-tight">Product Database.</h1>
    <a href="?page=add_product" class="btn-cyber btn-cyber-solid px-6 py-2 text-xs font-bold tracking-widest">+ DEPLOY NEW ASSET</a>
</div>

<div class="sci-fi-card p-6 bg-white/80 backdrop-blur-md">
    <table class="w-full text-left admin-table">
        <thead>
            <tr class="border-b border-cyan-100">
                <th class="py-4 px-4 text-[10px] uppercase tracking-[0.2em] text-cyan-700">Preview</th>
                <th class="py-4 px-4 text-[10px] uppercase tracking-[0.2em] text-cyan-700">SKU</th>
                <th class="py-4 px-4 text-[10px] uppercase tracking-[0.2em] text-cyan-700">Designation</th>
                <th class="py-4 px-4 text-[10px] uppercase tracking-[0.2em] text-cyan-700">Category</th>
                <th class="py-4 px-4 text-[10px] uppercase tracking-[0.2em] text-cyan-700">Value</th>
                <th class="py-4 px-4 text-[10px] uppercase tracking-[0.2em] text-cyan-700">Status</th>
                <th class="py-4 px-4 text-[10px] uppercase tracking-[0.2em] text-cyan-700 text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="text-sm font-bold text-slate-600">
            <?php if (empty($products)): ?>
                <tr>
                    <td colspan="7" class="py-10 text-center text-slate-400 italic tracking-widest">
                        [ NO ASSETS DETECTED IN LOCAL MATRIX ]
                    </td>
                </tr>
            <?php endif; ?>

            <?php foreach($products as $p): ?>
            <tr class="border-b border-slate-100 hover:bg-cyan-50/30 transition-colors group">
                <!-- Image Preview -->
                <td class="py-3 px-4">
                    <div class="w-12 h-12 bg-slate-100 border border-slate-200 overflow-hidden flex items-center justify-center">
                        <?php if($p['image']): ?>
                            <img src="../uploads/<?= htmlspecialchars($p['image']) ?>" alt="Asset" class="w-full h-full object-cover">
                        <?php else: ?>
                            <span class="text-[10px] text-slate-400 font-mono">NO_IMG</span>
                        <?php endif; ?>
                    </div>
                </td>

                <td class="py-3 px-4 text-cyan-600 font-mono tracking-tighter text-xs">
                    <?= htmlspecialchars($p['sku']) ?>
                </td>

                <td class="py-3 px-4">
                    <div class="font-robot text-lg text-slate-900 group-hover:text-cyan-600 transition-colors">
                        <?= htmlspecialchars($p['name']) ?>
                    </div>
                </td>

                <td class="py-3 px-4">
                    <span class="text-[10px] px-2 py-0.5 bg-slate-100 text-slate-500 uppercase tracking-widest font-bold">
                        <?= htmlspecialchars($p['category_name']) ?>
                    </span>
                </td>

                <td class="py-3 px-4 font-mono text-slate-900">
                    <?= htmlspecialchars($p['price_display']) ?>
                </td>

                <td class="py-3 px-4">
                    <?php 
                        $statusClass = "text-cyan-600 bg-cyan-50 border-cyan-200";
                        if($p['status'] == 'Out of Stock') $statusClass = "text-red-600 bg-red-50 border-red-200";
                        if($p['status'] == 'Low Stock') $statusClass = "text-orange-600 bg-orange-50 border-orange-200";
                        if($p['status'] == 'Hidden') $statusClass = "text-slate-400 bg-slate-100 border-slate-200";
                    ?>
                    <span class="px-2 py-1 text-[9px] tracking-widest border font-bold uppercase <?= $statusClass ?>">
                        <?= htmlspecialchars($p['status']) ?>
                    </span>
                </td>

                <td class="py-3 px-4 text-right">
                    <div class="flex justify-end gap-3">
                        <a href="?page=edit_product&id=<?= $p['id'] ?>" class="text-slate-400 hover:text-cyan-600 transition-colors text-xs uppercase tracking-tighter">
                            [ Edit ]
                        </a>
                        <a href="?page=delete_product&id=<?= $p['id'] ?>" 
                           onclick="return confirm('CRITICAL: Purge this asset from the matrix permanently?');" 
                           class="text-slate-400 hover:text-red-600 transition-colors text-xs uppercase tracking-tighter">
                            [ Del ]
                        </a>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="mt-6 text-[10px] text-slate-400 tracking-[0.3em] font-bold uppercase">
    Total Active Nodes: <?= count($products) ?>
</div>