<?php
require_once 'includes/config.php';

// 1. Get Product ID from GET (from index or product page)
$product_id = $_GET['asset_id'] ?? null;

if (!$product_id) {
    header("Location: index.php");
    exit;
}

// 2. Fetch Product Data
$stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) {
    header("Location: index.php");
    exit;
}

// 3. Handle Order Submission
$order_success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_order'])) {
    $order_num = "ORD-" . strtoupper(substr(md5(time()), 0, 8));
    $name = $_POST['customer_name'];
    $email = $_POST['customer_email'];
    $total = $product['price_numeric'];

    $ins = $pdo->prepare("INSERT INTO orders (order_number, customer_name, customer_email, total_amount, payment_status, order_status) VALUES (?, ?, ?, ?, 'Pending', 'Processing')");
    
    if ($ins->execute([$order_num, $name, $email, $total])) {
        $order_success = true;
    }
}

require_once 'includes/header.php';
?>

<main class="pt-40 pb-20 px-6 min-h-screen flex items-center justify-center relative overflow-hidden">
    <!-- Decorative background elements -->
    <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-cyan-500/5 rounded-full blur-3xl"></div>
    
    <div class="max-w-4xl w-full relative z-10">
        <?php if ($order_success): ?>
            <!-- SUCCESS STATE -->
            <div class="sci-fi-card p-12 text-center bg-white/90 backdrop-blur-md">
                <div class="w-20 h-20 bg-cyan-500 text-white rounded-full flex items-center justify-center text-4xl mx-auto mb-6 shadow-lg shadow-cyan-500/50">
                    ✓
                </div>
                <h1 class="text-4xl font-robot font-bold text-slate-900 mb-4">DEPLOYMENT INITIALIZED.</h1>
                <p class="text-slate-600 mb-8 uppercase tracking-widest text-xs font-bold">Order Reference: <span class="text-cyan-600"><?= $order_num ?></span></p>
                <div class="p-4 bg-slate-50 border border-slate-200 text-slate-500 font-mono text-[10px] mb-8 leading-relaxed text-left">
                    &gt; Asset: <?= $product['name'] ?><br>
                    &gt; Protocol: Standard Delivery<br>
                    &gt; Status: Awaiting Matrix Synchronization...
                </div>
                <a href="index.php" class="btn-cyber btn-cyber-solid px-10 py-4 inline-block text-xs">RETURN_TO_BASE</a>
            </div>

        <?php else: ?>
            <!-- ORDER FORM STATE -->
            <div class="grid md:grid-cols-5 gap-0 shadow-2xl">
                
                <!-- Left Panel: Asset Summary -->
                <div class="md:col-span-2 bg-slate-900 p-8 text-white flex flex-col justify-between relative overflow-hidden">
                    <div class="relative z-10">
                        <div class="text-cyan-500 font-bold text-[10px] tracking-[0.3em] mb-8 uppercase">// ASSET_SUMMARY</div>
                        
                        <div class="mb-6">
                            <div class="text-[10px] text-slate-500 uppercase tracking-widest mb-1">Designation</div>
                            <h2 class="text-2xl font-robot font-bold"><?= $product['name'] ?></h2>
                        </div>

                        <div class="mb-6">
                            <div class="text-[10px] text-slate-500 uppercase tracking-widest mb-1">ID_Code</div>
                            <div class="font-mono text-cyan-400"><?= $product['sku'] ?></div>
                        </div>

                        <div class="mb-6">
                            <div class="text-[10px] text-slate-500 uppercase tracking-widest mb-1">Category</div>
                            <div class="text-sm font-bold opacity-80"><?= $product['category_name'] ?></div>
                        </div>
                    </div>

                    <div class="relative z-10 pt-8 border-t border-white/10">
                        <div class="text-[10px] text-slate-500 uppercase tracking-widest mb-2">Total_Value</div>
                        <div class="text-4xl font-robot font-bold text-cyan-500"><?= $product['price_display'] ?></div>
                    </div>

                    <!-- Watermark background icon -->
                    <div class="absolute -bottom-10 -right-10 text-[15rem] opacity-5 pointer-events-none">
                        <?= $product['icon'] ?>
                    </div>
                </div>

                <!-- Right Panel: Checkout Form -->
                <div class="md:col-span-3 bg-white p-10 border-y md:border-y-0 md:border-r border-slate-200">
                    <h2 class="text-3xl font-robot font-bold text-slate-900 mb-2 uppercase">Identity_Verification.</h2>
                    <p class="text-slate-500 text-xs mb-8 tracking-widest uppercase font-bold">Input credentials to confirm acquisition.</p>

                    <form method="POST" action="" class="space-y-6">
                        <div class="relative">
                            <label class="block text-[9px] font-bold text-cyan-700 tracking-widest mb-2 uppercase">Full_Name</label>
                            <input type="text" name="customer_name" required 
                                   class="input-cyber w-full p-4 text-sm bg-slate-50 focus:bg-white" 
                                   placeholder="ENTER SUBJECT NAME">
                        </div>

                        <div class="relative">
                            <label class="block text-[9px] font-bold text-cyan-700 tracking-widest mb-2 uppercase">Contact_Email</label>
                            <input type="email" name="customer_email" required 
                                   class="input-cyber w-full p-4 text-sm bg-slate-50 focus:bg-white" 
                                   placeholder="COMM_LINK@SERVER.COM">
                        </div>

                        <div class="p-4 bg-cyan-50 border border-cyan-100 rounded-sm">
                            <label class="flex items-start gap-3 cursor-pointer">
                                <input type="checkbox" required class="mt-1 accent-cyan-600">
                                <span class="text-[10px] text-cyan-800 leading-relaxed font-bold uppercase tracking-tight">
                                    I confirm that this asset deployment follows matrix protocol and I agree to the terms of digital acquisition.
                                </span>
                            </label>
                        </div>

                        <button type="submit" name="confirm_order" 
                                class="w-full btn-cyber btn-cyber-solid py-5 text-sm font-bold shadow-xl shadow-cyan-500/20">
                            CONFIRM_ACQUISITION_NOW
                        </button>
                        
                        <div class="text-center">
                            <a href="index.php" class="text-[10px] text-slate-400 hover:text-slate-900 font-bold tracking-widest uppercase transition-colors">
                                [ ABORT_OPERATION ]
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>