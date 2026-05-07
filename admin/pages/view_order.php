<?php
$order_id = $_GET['id'] ?? null;
if (!$order_id) exit("No ID provided.");

// Fetch Order Info
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch();

// Fetch Items
$itemStmt = $pdo->prepare("SELECT oi.*, p.name, p.sku FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
$itemStmt->execute([$order_id]);
$items = $itemStmt->fetchAll();
?>

<div class="flex justify-between items-end mb-8">
    <h1 class="text-4xl font-robot font-bold text-slate-900 uppercase">Quote Review.</h1>
    <div class="flex gap-4">
        <a href="?page=orders" class="px-6 py-2 border border-slate-300 text-xs font-bold uppercase tracking-widest bg-white hover:bg-slate-50 transition-colors">← Back</a>
        <a href="generate_quote.php?id=<?= $order['id'] ?>" target="_blank" class="btn-cyber btn-cyber-solid px-6 py-2 text-xs">Generate PDF</a>
    </div>
</div>

<div class="grid lg:grid-cols-3 gap-8">
    <div class="lg:col-span-1 space-y-6">
        <div class="sci-fi-card p-6 bg-slate-900 text-white">
            <div class="text-[10px] text-cyan-500 font-bold tracking-widest uppercase mb-4 border-b border-white/10 pb-2">Client Data</div>
            <div class="text-xl font-bold mb-1"><?= htmlspecialchars($order['customer_name']) ?></div>
            <div class="text-sm text-slate-400 font-mono mb-4"><?= htmlspecialchars($order['customer_email']) ?></div>
            
            <div class="text-[10px] text-cyan-500 font-bold tracking-widest uppercase mt-6 mb-2 border-b border-white/10 pb-2">Reference</div>
            <div class="font-mono text-lg text-cyan-400 mb-1"><?= $order['order_number'] ?></div>
            <div class="text-[10px] text-slate-400 font-mono"><?= date('Y-m-d H:i:s', strtotime($order['created_at'])) ?></div>
        </div>
    </div>

    <div class="lg:col-span-2">
        <div class="sci-fi-card bg-white border border-slate-200">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200">
                        <th class="py-4 px-6 text-[10px] font-bold text-slate-400 tracking-widest uppercase">Asset</th>
                        <th class="py-4 px-6 text-[10px] font-bold text-slate-400 tracking-widest uppercase">Qty</th>
                        <th class="py-4 px-6 text-[10px] font-bold text-slate-400 tracking-widest uppercase text-right">Value</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($items as $i): ?>
                    <tr class="border-b border-slate-100">
                        <td class="py-4 px-6">
                            <div class="font-bold text-slate-900"><?= htmlspecialchars($i['name']) ?></div>
                            <div class="text-[10px] text-cyan-600 font-mono"><?= htmlspecialchars($i['sku']) ?></div>
                        </td>
                        <td class="py-4 px-6 font-mono font-bold"><?= $i['quantity'] ?></td>
                        <td class="py-4 px-6 text-right font-robot font-bold">$<?= number_format($i['quantity'] * $i['price_at_order'], 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="2" class="py-6 px-6 text-right text-xs font-bold uppercase tracking-widest text-slate-500">Total Valuation</td>
                        <td class="py-6 px-6 text-right text-2xl font-robot font-bold text-cyan-600">$<?= number_format($order['total_amount'], 2) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>