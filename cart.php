<?php
session_start();
require_once 'includes/config.php';

$cart_items = $_SESSION['cart'] ?? [];
$products = [];
$total_value = 0;

if (!empty($cart_items)) {
    // Fetch products in cart
    $ids = implode(',', array_map('intval', array_keys($cart_items)));
    $stmt = $pdo->query("SELECT * FROM products WHERE id IN ($ids)");
    while ($row = $stmt->fetch()) {
        $row['qty'] = $cart_items[$row['id']];
        $row['subtotal'] = $row['price_numeric'] * $row['qty'];
        $total_value += $row['subtotal'];
        $products[] = $row;
    }
}

// Handle Quote Submission
$quote_success = false;
$order_num = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_quote']) && !empty($products)) {
    $order_num = "QUO-" . strtoupper(substr(md5(time()), 0, 8));
    $name = $_POST['customer_name'];
    $email = $_POST['customer_email'];

    try {
        $pdo->beginTransaction();
        
        // Insert main order (product_id is now NULL since we use order_items)
        $ins = $pdo->prepare("INSERT INTO orders (order_number, customer_name, customer_email, total_amount, payment_status, order_status) VALUES (?, ?, ?, ?, 'Pending', 'Processing')");
        $ins->execute([$order_num, $name, $email, $total_value]);
        $order_id = $pdo->lastInsertId();

        // Insert items
        $ins_item = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price_at_order) VALUES (?, ?, ?, ?)");
        foreach ($products as $p) {
            $ins_item->execute([$order_id, $p['id'], $p['qty'], $p['price_numeric']]);
        }

        $pdo->commit();
        unset($_SESSION['cart']); // Clear cart
        $quote_success = true;
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "System Failure during Quote Generation.";
    }
}

require_once 'includes/header.php';
?>

<main class="pt-32 pb-20 px-6 max-w-7xl mx-auto min-h-screen">
    <?php if ($quote_success): ?>
        <div class="sci-fi-card p-12 text-center max-w-2xl mx-auto mt-20">
            <h1 class="text-4xl font-robot font-bold text-cyan-600 mb-4">QUOTATION REQUESTED.</h1>
            <p class="text-slate-400 mb-8 tracking-widest text-xs uppercase">Reference: <?= $order_num ?></p>
            <p class="text-slate-600 mb-8">Your request has been logged into the matrix. An administrator will review your requirements and transmit the official PDF quotation to your comm-link shortly.</p>
            <a href="index.php" class="btn-cyber btn-cyber-solid px-8 py-3 text-xs">Return to Base</a>
        </div>
    <?php else: ?>
        <h1 class="text-4xl font-robot font-bold text-slate-900 mb-8 uppercase">Acquisition Cart.</h1>
        
        <div class="grid lg:grid-cols-3 gap-10">
            <div class="lg:col-span-2 space-y-4">
                <?php if(empty($products)): ?>
                    <div class="p-10 border border-slate-200 text-center text-slate-400 font-mono text-sm tracking-widest uppercase bg-white">
                        [ CART IS EMPTY. NO ASSETS SELECTED. ]
                    </div>
                <?php else: ?>
                    <?php foreach($products as $p): ?>
                        <div class="sci-fi-card p-4 flex items-center gap-6 bg-white border border-slate-200">
                            <div class="w-20 h-20 bg-slate-50 flex items-center justify-center border border-slate-100 overflow-hidden">
                                <?php if($p['image']): ?>
                                    <img src="uploads/<?= $p['image'] ?>" class="w-full h-full object-cover">
                                <?php else: ?>
                                    <span class="text-2xl opacity-20"><?= $p['icon'] ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="flex-1">
                                <div class="text-[9px] text-cyan-600 font-mono mb-1"><?= $p['sku'] ?></div>
                                <h3 class="font-bold text-slate-900 uppercase text-lg"><?= $p['name'] ?></h3>
                                <div class="text-slate-500 font-mono text-sm mt-1">$<?= number_format($p['price_numeric'], 2) ?> x <?= $p['qty'] ?></div>
                            </div>
                            <div class="text-right">
                                <div class="font-bold text-slate-900 text-xl mb-2">$<?= number_format($p['subtotal'], 2) ?></div>
                                <form method="POST" action="cart_action.php">
                                    <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                                    <button type="submit" name="remove_item" class="text-[9px] text-red-400 hover:text-red-600 font-bold uppercase tracking-widest">[ Remove ]</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="lg:col-span-1">
                <div class="sci-fi-card p-8 bg-slate-900 text-white sticky top-32">
                    <h3 class="text-cyan-500 text-[10px] font-bold tracking-widest uppercase mb-6 border-b border-white/10 pb-4">// REQUEST_QUOTATION</h3>
                    
                    <div class="flex justify-between items-end mb-8">
                        <span class="text-slate-400 text-xs uppercase tracking-widest">Estimated Value</span>
                        <span class="text-3xl font-robot font-bold text-cyan-400">$<?= number_format($total_value, 2) ?></span>
                    </div>

                    <form method="POST" action="" class="space-y-4">
                        <div>
                            <label class="block text-[9px] font-bold text-slate-400 tracking-widest mb-2 uppercase">Subject Name</label>
                            <input type="text" name="customer_name" required class="input-cyber w-full p-3 text-sm bg-white/5 focus:bg-white/10 border-white/20 text-white" <?= empty($products) ? 'disabled' : '' ?>>
                        </div>
                        <div>
                            <label class="block text-[9px] font-bold text-slate-400 tracking-widest mb-2 uppercase">Comm Link (Email)</label>
                            <input type="email" name="customer_email" required class="input-cyber w-full p-3 text-sm bg-white/5 focus:bg-white/10 border-white/20 text-white" <?= empty($products) ? 'disabled' : '' ?>>
                        </div>
                        <button type="submit" name="submit_quote" class="w-full btn-cyber btn-cyber-solid py-4 text-xs mt-6 <?= empty($products) ? 'opacity-50 cursor-not-allowed' : '' ?>" <?= empty($products) ? 'disabled' : '' ?>>
                            TRANSMIT_REQUEST
                        </button>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?>
</main>

<?php require_once 'includes/footer.php'; ?>