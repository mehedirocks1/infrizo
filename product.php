<?php
require_once 'includes/config.php';

// 1. Get the Slug from URL
$slug = $_GET['slug'] ?? '';

if (empty($slug)) {
    header("Location: index.php");
    exit;
}

// 2. Fetch Product Data with Category Info
$stmt = $pdo->prepare("
    SELECT p.*, c.name as category_name, c.slug as category_slug 
    FROM products p 
    JOIN categories c ON p.category_id = c.id 
    WHERE p.slug = ? AND p.status != 'Hidden'
");
$stmt->execute([$slug]);
$product = $stmt->fetch();

// 3. Redirect if product doesn't exist
if (!$product) {
    header("Location: index.php");
    exit;
}

// 4. Decode Technical Specs (JSON)
$specs = json_decode($product['list_description'], true) ?: [];

// 5. UI Logic for Accent Colors
$isHardware = ($product['category_slug'] === 'hardware');
$accentClass = $isHardware ? 'purple' : 'cyan';
$accentHex = $isHardware ? '#7c3aed' : '#0284c7';

// Inject SEO Data
$page_title = ($product['meta_title'] ?: $product['name']) . ' | INFRIZO';
$meta_description = $product['meta_description'] ?: $product['short_description'];

require_once 'includes/header.php';
?>

<!-- BREADCRUMB HUD -->
<nav class="pt-32 px-6 max-w-7xl mx-auto">
    <div class="flex items-center gap-2 text-[10px] font-bold tracking-[0.2em] text-slate-400 uppercase">
        <a href="index.php" class="hover:text-<?= $accentClass ?>-600 transition-colors">Root</a>
        <span>/</span>
        <a href="index.php#<?= $product['category_slug'] ?>" class="hover:text-<?= $accentClass ?>-600 transition-colors"><?= $product['category_name'] ?></a>
        <span>/</span>
        <span class="text-<?= $accentClass ?>-600"><?= $product['sku'] ?></span>
    </div>
</nav>

<!-- MAIN PRODUCT INTERFACE -->
<main class="py-12 px-6 max-w-7xl mx-auto relative z-10">
    <div class="grid lg:grid-cols-2 gap-16 items-start">
        
        <!-- VISUAL ANALYZER (Image Section) -->
        <div class="relative group">
            <div class="sci-fi-card p-2 bg-white overflow-hidden shadow-2xl shadow-<?= $accentClass ?>-500/10">
                <div class="relative aspect-square bg-slate-50 flex items-center justify-center overflow-hidden">
                    <?php if($product['image']): ?>
                        <img src="uploads/<?= htmlspecialchars($product['image']) ?>" 
                             alt="<?= htmlspecialchars($product['name']) ?>" 
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
                    <?php else: ?>
                        <div class="text-9xl opacity-10"><?= $product['icon'] ?></div>
                    <?php endif; ?>
                    
                    <!-- Decorative HUD corners -->
                    <div class="absolute top-0 left-0 w-8 h-8 border-t-2 border-l-2 border-<?= $accentClass ?>-500"></div>
                    <div class="absolute bottom-0 right-0 w-8 h-8 border-b-2 border-r-2 border-<?= $accentClass ?>-500"></div>
                </div>
            </div>
            
            <!-- Technical Status HUD -->
            <div class="mt-6 grid grid-cols-2 gap-4">
                <div class="bg-white border border-slate-200 p-4">
                    <div class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1">System_Status</div>
                    <div class="flex items-center gap-2 text-<?= $accentClass ?>-600 font-bold text-sm">
                        <span class="w-2 h-2 rounded-full bg-<?= $accentClass ?>-500 animate-pulse"></span>
                        <?= strtoupper($product['status']) ?>
                    </div>
                </div>
                <div class="bg-white border border-slate-200 p-4">
                    <div class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1">Asset_ID</div>
                    <div class="text-slate-900 font-mono text-sm font-bold"><?= $product['sku'] ?></div>
                </div>
            </div>
        </div>

        <!-- SPECIFICATION ANALYTICS (Content Section) -->
        <div class="flex flex-col">
            <div class="mb-8">
                <h1 class="text-5xl lg:text-7xl font-robot font-bold text-slate-900 leading-none mb-4">
                    <?= htmlspecialchars($product['name']) ?><span class="text-<?= $accentClass ?>-600">.</span>
                </h1>
                <div class="h-1 w-24 bg-<?= $accentClass ?>-600"></div>
            </div>

            <!-- Price & Buy -->
            <div class="flex items-center gap-8 mb-10 pb-8 border-b border-slate-100">
                <div>
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Acquisition_Value</div>
                    <div class="text-4xl font-robot font-bold text-slate-900"><?= $product['price_display'] ?></div>
                </div>
                <button class="flex-1 btn-cyber btn-cyber-solid py-5 text-sm shadow-xl shadow-<?= $accentClass ?>-500/20">
                    INITIALIZE_DEPLOYMENT
                </button>
            </div>

            <!-- Descriptive Data -->
            <div class="mb-10">
                <h3 class="text-xs font-bold text-cyan-700 tracking-[0.3em] uppercase mb-4">// EXEC_SUMMARY</h3>
                <p class="text-slate-600 leading-relaxed text-lg italic">
                    "<?= htmlspecialchars($product['short_description']) ?>"
                </p>
            </div>

            <!-- Technical List (JSON Data) -->
            <?php if(!empty($specs)): ?>
            <div class="mb-10">
                <h3 class="text-xs font-bold text-<?= $accentClass ?>-600 tracking-[0.3em] uppercase mb-6">// TECH_SPECIFICATIONS</h3>
                <div class="grid sm:grid-cols-2 gap-y-4 gap-x-8">
                    <?php foreach($specs as $spec): ?>
                        <div class="flex items-start gap-3 group">
                            <span class="mt-1.5 w-1.5 h-1.5 bg-<?= $accentClass ?>-500 rounded-full group-hover:scale-150 transition-transform"></span>
                            <span class="text-sm font-bold text-slate-700 uppercase tracking-wide"><?= htmlspecialchars($spec) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Warning Terminal -->
            <div class="p-6 bg-slate-900 border-l-4 border-<?= $accentClass ?>-500 font-mono text-[11px] text-slate-400 leading-relaxed">
                <p class="text-<?= $accentClass ?>-400 font-bold mb-2">&gt; SECURITY_PROTOCOL_ACTIVE</p>
                <p>Verify all technical requirements before deployment. System configuration may vary based on node availability in the Dhaka Matrix.</p>
            </div>
        </div>
    </div>
</main>

<!-- FOOTER MARQUEE -->
<div class="bg-white border-y border-slate-200 py-4 mt-20 overflow-hidden whitespace-nowrap">
    <div class="inline-block animate-marquee uppercase text-[10px] font-bold tracking-[0.5em] text-slate-300">
        <?= str_repeat($product['name'] . " // " . $product['sku'] . " // SECURE_ASSET // ", 10) ?>
    </div>
</div>

<style>
@keyframes marquee {
    0% { transform: translateX(0); }
    100% { transform: translateX(-50%); }
}
.animate-marquee {
    display: inline-block;
    animation: marquee 30s linear infinite;
}
</style>

<?php require_once 'includes/footer.php'; ?>