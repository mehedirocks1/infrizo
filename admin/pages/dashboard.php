<?php
// This file is included from admin/index.php, so $pdo is available.

// Fetch stats from the database
$stats = [
    'pending_applications' => $pdo->query("SELECT COUNT(*) FROM freelancer_applications WHERE status = 'Pending'")->fetchColumn(),
    'processing_orders' => $pdo->query("SELECT COUNT(*) FROM orders WHERE order_status = 'Processing'")->fetchColumn(),
    'active_engineers' => $pdo->query("SELECT COUNT(*) FROM engineers WHERE status = 'Active'")->fetchColumn(),
    'unread_inquiries' => $pdo->query("SELECT COUNT(*) FROM inquiries WHERE status = 'Unread'")->fetchColumn(),
];

// Fetch recent orders
$recent_orders = $pdo->query("SELECT * FROM orders ORDER BY created_at DESC LIMIT 5")->fetchAll();
?>

<h1 class="text-4xl font-robot font-bold text-slate-800 mb-10">System Status Dashboard</h1>

<!-- STATS GRID -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
    
    <!-- Pending Applications -->
    <div class="bg-white p-6 shadow-lg border-l-4 border-yellow-500">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-xs text-slate-500 uppercase font-bold tracking-wider">Pending Applications</p>
                <p class="text-4xl font-robot font-bold text-slate-800 mt-1"><?= $stats['pending_applications'] ?></p>
            </div>
            <div class="text-yellow-500 text-3xl opacity-50">[&]</div>
        </div>
        <a href="?page=applications&filter=Pending" class="text-xs font-bold text-yellow-600 hover:underline mt-4 inline-block">View Applications &rarr;</a>
    </div>

    <!-- New Orders -->
    <div class="bg-white p-6 shadow-lg border-l-4 border-green-500">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-xs text-slate-500 uppercase font-bold tracking-wider">Processing Orders</p>
                <p class="text-4xl font-robot font-bold text-slate-800 mt-1"><?= $stats['processing_orders'] ?></p>
            </div>
            <div class="text-green-500 text-3xl opacity-50">[!]</div>
        </div>
        <a href="index.php?page=orders" class="text-xs font-bold text-green-600 hover:underline mt-4 inline-block">View Orders &rarr;</a>
    </div>

    <!-- Active Engineers -->
    <div class="bg-white p-6 shadow-lg border-l-4 border-cyan-500">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-xs text-slate-500 uppercase font-bold tracking-wider">Active Engineers</p>
                <p class="text-4xl font-robot font-bold text-slate-800 mt-1"><?= $stats['active_engineers'] ?></p>
            </div>
            <div class="text-cyan-500 text-3xl opacity-50">[@]</div>
        </div>
        <a href="?page=engineers" class="text-xs font-bold text-cyan-600 hover:underline mt-4 inline-block">Manage Engineers &rarr;</a>
    </div>

    <!-- Unread Inquiries -->
    <div class="bg-white p-6 shadow-lg border-l-4 border-orange-500">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-xs text-slate-500 uppercase font-bold tracking-wider">Unread Inquiries</p>
                <p class="text-4xl font-robot font-bold text-slate-800 mt-1"><?= $stats['unread_inquiries'] ?></p>
            </div>
            <div class="text-orange-500 text-3xl opacity-50">[?]</div>
        </div>
        <a href="index.php?page=inquiries" class="text-xs font-bold text-orange-600 hover:underline mt-4 inline-block">View Inquiries &rarr;</a>
    </div>
</div>

<!-- RECENT ACTIVITY -->
<div>
    <h2 class="text-2xl font-robot font-bold text-slate-800 mb-4">Recent Order Matrix Activity</h2>
    <div class="bg-white shadow-lg overflow-x-auto">
        <table class="w-full text-sm text-left text-slate-500">
            <thead class="text-xs text-slate-700 uppercase bg-slate-50">
                <tr>
                    <th class="px-6 py-3">Order Ref</th>
                    <th class="px-6 py-3">Customer</th>
                    <th class="px-6 py-3">Total</th>
                    <th class="px-6 py-3">Date</th>
                    <th class="px-6 py-3">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recent_orders)): ?>
                    <tr><td colspan="5" class="text-center p-8 text-slate-400 font-mono">-- No recent orders --</td></tr>
                <?php else: ?>
                    <?php foreach ($recent_orders as $order): ?>
                        <tr class="bg-white border-b hover:bg-slate-50">
                            <td class="px-6 py-4 font-mono font-bold text-cyan-600"><?= htmlspecialchars($order['order_number']) ?></td>
                            <td class="px-6 py-4"><?= htmlspecialchars($order['customer_name']) ?><br><span class="text-xs text-slate-400"><?= htmlspecialchars($order['customer_email']) ?></span></td>
                            <td class="px-6 py-4 font-mono">$<?= number_format($order['total_amount'], 2) ?></td>
                            <td class="px-6 py-4 font-mono text-xs"><?= date('d M Y, H:i', strtotime($order['created_at'])) ?></td>
                            <td class="px-6 py-4"><span class="px-2 py-1 text-xs font-bold uppercase bg-blue-100 text-blue-800"><?= htmlspecialchars($order['order_status']) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>