<?php
// --- 1. BIG DATA LOGIC: PAGINATION & FILTERS ---
$limit = 20; 
$page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$offset = ($page - 1) * $limit;

$search = $_GET['search'] ?? '';
$cat_filter = $_GET['category'] ?? '';

// Build Base Query - Using unique placeholders
$whereClauses = ["(p.name LIKE :s1 OR p.sku LIKE :s2)"];
$params = [
    ':s1' => "%$search%",
    ':s2' => "%$search%"
];

if ($cat_filter) {
    $whereClauses[] = "p.category_id = :cat";
    $params[':cat'] = $cat_filter;
}

$whereSQL = "WHERE " . implode(" AND ", $whereClauses);

// Get Total Count for Pagination
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM products p $whereSQL");
$countStmt->execute($params);
$total_products = $countStmt->fetchColumn();
$total_pages = ceil($total_products / $limit);

// Fetch Main Data
$stmt = $pdo->prepare("
    SELECT p.id, p.sku, p.name, p.price_display, p.status, p.image, c.name as category_name 
    FROM products p 
    JOIN categories c ON p.category_id = c.id 
    $whereSQL 
    ORDER BY p.id DESC 
    LIMIT $limit OFFSET $offset
");
$stmt->execute($params);
$products = $stmt->fetchAll();

// Fetch Categories for Filter Dropdown
$categories = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC")->fetchAll();

// --- 2. STATS HUD DATA ---
$stats = $pdo->query("SELECT 
    COUNT(*) as total, 
    SUM(CASE WHEN status = 'Out of Stock' THEN 1 ELSE 0 END) as out_of_stock,
    SUM(CASE WHEN status = 'Low Stock' THEN 1 ELSE 0 END) as low_stock
    FROM products")->fetch();
?>

<!-- HUD HEADER -->
<div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6 mb-10">
    <div>
        <h1 class="text-5xl font-robot font-bold text-slate-900 uppercase tracking-tighter">Product Database<span class="text-cyan-600">.</span></h1>
        <p class="text-[10px] text-slate-400 font-bold tracking-[0.3em] uppercase mt-2">Asset Management Matrix</p>
    </div>
    
    <div class="flex gap-4 items-center">
        <!-- Mini Stats -->
        <div class="hidden md:flex gap-4 mr-4">
            <div class="text-right">
                <div class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Total Nodes</div>
                <div class="text-lg font-bold text-slate-900 leading-none"><?= $stats['total'] ?></div>
            </div>
            <div class="text-right border-l border-slate-200 pl-4">
                <div class="text-[9px] font-bold text-orange-500 uppercase tracking-widest">Low Stock</div>
                <div class="text-lg font-bold text-orange-600 leading-none"><?= $stats['low_stock'] ?? 0 ?></div>
            </div>
            <div class="text-right border-l border-slate-200 pl-4">
                <div class="text-[9px] font-bold text-red-500 uppercase tracking-widest">Critical (OOS)</div>
                <div class="text-lg font-bold text-red-600 leading-none"><?= $stats['out_of_stock'] ?? 0 ?></div>
            </div>
        </div>
        <a href="?page=add_product" class="btn-cyber btn-cyber-solid px-6 py-3 text-xs font-bold tracking-widest shadow-lg shadow-cyan-500/30">+ DEPLOY NEW ASSET</a>
    </div>
</div>

<!-- SEARCH & FILTER TERMINAL -->
<div class="sci-fi-card p-4 bg-white/50 border border-slate-200 mb-8 shadow-sm">
    <form method="GET" class="flex flex-wrap gap-4">
        <input type="hidden" name="page" value="products">
        <div class="flex-1 min-w-[250px]">
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="SEARCH DESIGNATION OR SKU..." class="input-cyber w-full bg-white border-slate-200 px-4 py-3 text-xs focus:border-cyan-500">
        </div>
        <select name="category" class="input-cyber bg-white border-slate-200 px-6 py-3 text-xs font-bold uppercase cursor-pointer">
            <option value="">ALL_DIRECTORIES</option>
            <?php foreach($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>" <?= $cat_filter == $cat['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn-cyber btn-cyber-solid px-10 py-3 text-[10px] font-bold tracking-widest">SCAN_MATRIX</button>
    </form>
</div>

<!-- MAIN DATA TABLE -->
<div class="sci-fi-card p-0 bg-white/80 backdrop-blur-md overflow-hidden shadow-sm">
    <table class="w-full text-left admin-table">
        <thead>
            <tr class="bg-slate-50 border-b border-slate-200">
                <th class="py-4 px-6 text-[10px] uppercase tracking-[0.2em] text-slate-500 font-bold">Preview</th>
                <th class="py-4 px-6 text-[10px] uppercase tracking-[0.2em] text-slate-500 font-bold">SKU</th>
                <th class="py-4 px-6 text-[10px] uppercase tracking-[0.2em] text-slate-500 font-bold">Designation</th>
                <th class="py-4 px-6 text-[10px] uppercase tracking-[0.2em] text-slate-500 font-bold">Directory</th>
                <th class="py-4 px-6 text-[10px] uppercase tracking-[0.2em] text-slate-500 font-bold">Value</th>
                <th class="py-4 px-6 text-[10px] uppercase tracking-[0.2em] text-slate-500 font-bold">Status</th>
                <th class="py-4 px-6 text-[10px] uppercase tracking-[0.2em] text-slate-500 font-bold text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="text-sm font-bold text-slate-600">
            <?php if (empty($products)): ?>
                <tr>
                    <td colspan="7" class="py-16 text-center text-slate-400 font-mono text-xs tracking-widest uppercase bg-white">
                        [ NO ASSETS DETECTED MATCHING PARAMETERS ]
                    </td>
                </tr>
            <?php endif; ?>

            <?php foreach($products as $p): ?>
            <tr class="border-b border-slate-100 hover:bg-cyan-50/30 transition-colors group bg-white">
                <!-- Image Preview -->
                <td class="py-3 px-6">
                    <div class="w-12 h-12 bg-slate-50 border border-slate-200 overflow-hidden flex items-center justify-center">
                        <?php if($p['image']): ?>
                            <img src="../uploads/<?= htmlspecialchars($p['image']) ?>" alt="Asset" class="w-full h-full object-cover">
                        <?php else: ?>
                            <span class="text-[9px] text-slate-400 font-mono">NO_IMG</span>
                        <?php endif; ?>
                    </div>
                </td>

                <td class="py-3 px-6 text-cyan-600 font-mono tracking-tighter text-xs">
                    <?= htmlspecialchars($p['sku']) ?>
                </td>

                <td class="py-3 px-6">
                    <div class="font-robot text-lg text-slate-900 group-hover:text-cyan-600 transition-colors tracking-tight uppercase">
                        <?= htmlspecialchars($p['name']) ?>
                    </div>
                </td>

                <td class="py-3 px-6">
                    <span class="text-[9px] px-2 py-1 bg-slate-100 text-slate-500 uppercase tracking-widest font-bold">
                        <?= htmlspecialchars($p['category_name']) ?>
                    </span>
                </td>

                <td class="py-3 px-6 font-mono text-slate-900 font-bold text-base">
                    <?= htmlspecialchars($p['price_display']) ?>
                </td>

                <td class="py-3 px-6">
                    <?php 
                        $statusClass = "text-cyan-600 bg-cyan-50 border-cyan-200";
                        if($p['status'] == 'Out of Stock') $statusClass = "text-red-600 bg-red-50 border-red-200";
                        if($p['status'] == 'Low Stock') $statusClass = "text-orange-600 bg-orange-50 border-orange-200";
                        if($p['status'] == 'Hidden') $statusClass = "text-slate-400 bg-slate-100 border-slate-200";
                    ?>
                    <span class="px-2 py-1 text-[9px] tracking-widest border font-black uppercase <?= $statusClass ?>">
                        <?= htmlspecialchars($p['status']) ?>
                    </span>
                </td>

                <td class="py-3 px-6 text-right">
                    <div class="flex justify-end gap-3">
                        <a href="?page=edit_product&id=<?= $p['id'] ?>" class="text-slate-400 hover:text-cyan-600 transition-colors text-[10px] font-bold uppercase tracking-widest">
                            [ Edit ]
                        </a>
                        <a href="?page=delete_product&id=<?= $p['id'] ?>" 
                           onclick="return confirm('CRITICAL WARNING: Purge this asset from the matrix permanently?');" 
                           class="text-red-300 hover:text-red-600 transition-colors text-[10px] font-bold uppercase tracking-widest">
                            [ Del ]
                        </a>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- PAGINATION HUD -->
    <div class="p-4 bg-slate-50 border-t border-slate-100 flex justify-between items-center">
        <div class="text-[10px] text-slate-400 font-bold uppercase tracking-widest font-mono ml-2">
            Nodes_Loaded: <?= count($products) ?> / <?= $total_products ?>
        </div>
        <div class="flex gap-2">
            <?php for($i=1; $i<=$total_pages; $i++): ?>
                <a href="?page=products&p=<?= $i ?>&search=<?= urlencode($search) ?>&category=<?= $cat_filter ?>" 
                   class="w-8 h-8 flex items-center justify-center text-xs font-bold border <?= $page == $i ? 'bg-slate-900 text-white border-slate-900' : 'bg-white text-slate-400 border-slate-200 hover:border-cyan-400' ?> transition-all duration-300">
                    <?= $i ?>
                </a>
            <?php endfor; ?>
        </div>
    </div>
</div>