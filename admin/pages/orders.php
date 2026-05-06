<?php
// --- 1. ACTION HANDLERS (Status Update & Purge) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update Order Status
    if (isset($_POST['update_status'])) {
        $order_id = $_POST['order_id'];
        $new_status = $_POST['new_status'];
        $new_payment = $_POST['new_payment'];
        $update = $pdo->prepare("UPDATE orders SET order_status = ?, payment_status = ? WHERE id = ?");
        $update->execute([$new_status, $new_payment, $order_id]);
        echo '<div class="p-3 mb-6 bg-cyan-100 border border-cyan-500 text-cyan-700 text-[10px] font-bold tracking-widest uppercase">Protocol Updated: Order Matrix Reconfigured.</div>';
    }
    
    // Purge Order (Delete)
    if (isset($_POST['purge_order'])) {
        $order_id = $_POST['order_id'];
        $delete = $pdo->prepare("DELETE FROM orders WHERE id = ?");
        $delete->execute([$order_id]);
        echo '<div class="p-3 mb-6 bg-red-100 border border-red-500 text-red-700 text-[10px] font-bold tracking-widest uppercase">Asset Purged: Record Deleted from Matrix.</div>';
    }
}

// --- 2. BIG DATA LOGIC: PAGINATION & FILTERS ---
$limit = 25; 
$page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$offset = ($page - 1) * $limit;

$search = $_GET['search'] ?? '';
$filter_status = $_GET['status'] ?? '';

// Build Base Query - Using unique placeholders to prevent PDO HY093 error
$whereClauses = ["(o.order_number LIKE :s1 OR o.customer_name LIKE :s2 OR p.name LIKE :s3)"];
$params = [
    ':s1' => "%$search%",
    ':s2' => "%$search%",
    ':s3' => "%$search%"
];

if ($filter_status) {
    $whereClauses[] = "o.order_status = :status";
    $params[':status'] = $filter_status;
}

$whereSQL = "WHERE " . implode(" AND ", $whereClauses);

// Get Total Count for Pagination
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM orders o LEFT JOIN products p ON o.product_id = p.id $whereSQL");
$countStmt->execute($params);
$total_orders = $countStmt->fetchColumn();
$total_pages = ceil($total_orders / $limit);

// Fetch Main Data with Product Join
$stmt = $pdo->prepare("
    SELECT o.*, p.name as product_name, p.sku as product_sku 
    FROM orders o 
    LEFT JOIN products p ON o.product_id = p.id 
    $whereSQL 
    ORDER BY o.created_at DESC 
    LIMIT $limit OFFSET $offset
");
$stmt->execute($params);
$orders = $stmt->fetchAll();

// --- 3. STATS HUD DATA ---
$stats = $pdo->query("SELECT 
    COUNT(*) as total, 
    SUM(CASE WHEN order_status = 'Processing' THEN 1 ELSE 0 END) as processing,
    SUM(total_amount) as revenue 
    FROM orders")->fetch();
?>

<!-- HUD HEADER -->
<div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6 mb-10">
    <div>
        <h1 class="text-5xl font-robot font-bold text-slate-900 uppercase tracking-tighter">Order Matrix<span class="text-cyan-600">.</span></h1>
        <p class="text-[10px] text-slate-400 font-bold tracking-[0.3em] uppercase mt-2">Centralized Acquisition Terminal v4.0</p>
    </div>
    
    <div class="flex gap-4">
        <div class="sci-fi-card bg-slate-900 text-white px-6 py-3 border border-slate-700">
            <div class="text-[8px] text-cyan-400 font-bold tracking-widest uppercase">Total_Revenue</div>
            <div class="text-xl font-robot font-bold">$<?= number_format($stats['revenue'] ?? 0, 2) ?></div>
        </div>
        <button onclick="window.print()" class="btn-cyber px-6 py-3 text-[10px] bg-white border border-slate-200 font-bold tracking-widest hover:bg-slate-50 transition-colors">EXPORT_LOGS</button>
    </div>
</div>

<!-- SEARCH & FILTER TERMINAL -->
<div class="sci-fi-card p-4 bg-white/50 border border-slate-200 mb-8">
    <form method="GET" class="flex flex-wrap gap-4">
        <input type="hidden" name="page" value="orders">
        <div class="flex-1 min-w-[300px]">
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="SEARCH BY ORDER#, NAME, OR ASSET..." class="input-cyber w-full bg-white border-slate-200 px-4 py-3 text-xs focus:border-cyan-500">
        </div>
        <select name="status" class="input-cyber bg-white border-slate-200 px-6 py-3 text-xs font-bold uppercase cursor-pointer">
            <option value="">ALL_STATUS</option>
            <option value="Processing" <?= $filter_status == 'Processing' ? 'selected' : '' ?>>Processing</option>
            <option value="Shipped" <?= $filter_status == 'Shipped' ? 'selected' : '' ?>>Shipped</option>
            <option value="Delivered" <?= $filter_status == 'Delivered' ? 'selected' : '' ?>>Delivered</option>
        </select>
        <button type="submit" class="btn-cyber btn-cyber-solid px-10 py-3 text-[10px] font-bold tracking-widest">EXECUTE_QUERY</button>
    </form>
</div>

<!-- MAIN MATRIX TABLE -->
<div class="sci-fi-card bg-white border border-slate-200 overflow-hidden shadow-sm">
    <table class="w-full text-left">
        <thead>
            <tr class="bg-slate-50 border-b border-slate-200">
                <th class="py-5 px-6 text-[9px] font-bold text-slate-400 tracking-widest uppercase">Reference</th>
                <th class="py-5 px-6 text-[9px] font-bold text-slate-400 tracking-widest uppercase">Subject / Comms</th>
                <th class="py-5 px-6 text-[9px] font-bold text-slate-400 tracking-widest uppercase">Asset_Deployed</th>
                <th class="py-5 px-6 text-[9px] font-bold text-slate-400 tracking-widest uppercase">Financials</th>
                <th class="py-5 px-6 text-[9px] font-bold text-slate-400 tracking-widest uppercase">Status_Controls</th>
            </tr>
        </thead>
        <tbody class="text-sm">
            <?php if (empty($orders)): ?>
                <tr>
                    <td colspan="5" class="py-20 text-center text-slate-400 font-mono text-xs tracking-widest uppercase">
                        [ NO RECORDS MATCHING QUERY PARAMETERS ]
                    </td>
                </tr>
            <?php endif; ?>

            <?php foreach($orders as $o): ?>
            <tr class="border-b border-slate-100 hover:bg-cyan-50/20 transition-all group">
                <td class="py-6 px-6">
                    <div class="text-cyan-600 font-mono font-bold text-xs"><?= $o['order_number'] ?></div>
                    <div class="text-[9px] text-slate-400 font-mono mt-1 uppercase"><?= date('Y/m/d • H:i', strtotime($o['created_at'])) ?></div>
                </td>
                
                <td class="py-6 px-6">
                    <div class="font-bold text-slate-900 uppercase tracking-tight"><?= htmlspecialchars($o['customer_name']) ?></div>
                    <div class="text-[10px] text-slate-400 lowercase font-mono"><?= htmlspecialchars($o['customer_email']) ?></div>
                </td>

                <td class="py-6 px-6">
                    <?php if($o['product_name']): ?>
                        <div class="text-slate-900 font-bold"><?= htmlspecialchars($o['product_name']) ?></div>
                        <div class="text-[9px] text-cyan-600 font-mono uppercase tracking-tighter"><?= $o['product_sku'] ?></div>
                    <?php else: ?>
                        <span class="text-slate-300 italic text-xs">Unknown Asset</span>
                    <?php endif; ?>
                </td>

                <td class="py-6 px-6">
                    <div class="text-lg font-robot font-bold text-slate-900">$<?= number_format($o['total_amount'], 2) ?></div>
                    <div class="mt-1">
                        <span class="px-2 py-0.5 text-[8px] font-black uppercase border <?= $o['payment_status'] == 'Paid' ? 'bg-green-50 text-green-600 border-green-200' : 'bg-red-50 text-red-600 border-red-200' ?>">
                            <?= $o['payment_status'] ?>
                        </span>
                    </div>
                </td>
                
                <td class="py-6 px-6">
                    <form method="POST" class="flex flex-col gap-2">
                        <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                        <div class="flex gap-1">
                            <select name="new_payment" class="text-[9px] p-1.5 border border-slate-200 bg-white font-bold uppercase focus:border-cyan-500 outline-none">
                                <option value="Pending" <?= $o['payment_status'] == 'Pending' ? 'selected' : '' ?>>UNPAID</option>
                                <option value="Paid" <?= $o['payment_status'] == 'Paid' ? 'selected' : '' ?>>PAID</option>
                            </select>
                            <select name="new_status" class="text-[9px] p-1.5 border border-slate-200 bg-white font-bold uppercase focus:border-cyan-500 outline-none">
                                <option value="Processing" <?= $o['order_status'] == 'Processing' ? 'selected' : '' ?>>PROC</option>
                                <option value="Shipped" <?= $o['order_status'] == 'Shipped' ? 'selected' : '' ?>>SHIP</option>
                                <option value="Delivered" <?= $o['order_status'] == 'Delivered' ? 'selected' : '' ?>>DLVR</option>
                            </select>
                        </div>
                        <div class="flex justify-between items-center mt-1">
                            <button type="submit" name="update_status" class="text-[9px] text-cyan-600 font-bold uppercase hover:underline underline-offset-4 tracking-widest">[ Sync_Data ]</button>
                            <button type="submit" name="purge_order" onclick="return confirm('CRITICAL WARNING: Permanent deletion of record. Proceed?')" class="text-[9px] text-red-400 font-bold uppercase hover:text-red-600 opacity-0 group-hover:opacity-100 transition-opacity tracking-widest">[ Purge ]</button>
                        </div>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- PAGINATION HUD -->
    <div class="p-6 bg-slate-50 border-t border-slate-100 flex justify-between items-center">
        <div class="text-[10px] text-slate-400 font-bold uppercase tracking-widest font-mono">
            Nodes_Loaded: <?= count($orders) ?> / <?= $total_orders ?>
        </div>
        <div class="flex gap-2">
            <?php for($i=1; $i<=$total_pages; $i++): ?>
                <a href="?page=orders&p=<?= $i ?>&search=<?= urlencode($search) ?>&status=<?= $filter_status ?>" 
                   class="w-10 h-10 flex items-center justify-center text-xs font-bold border <?= $page == $i ? 'bg-slate-900 text-white border-slate-900' : 'bg-white text-slate-400 border-slate-200 hover:border-cyan-400' ?> transition-all duration-300">
                    <?= str_pad($i, 2, '0', STR_PAD_LEFT) ?>
                </a>
            <?php endfor; ?>
        </div>
    </div>
</div>