<?php
require_once 'includes/header.php';

$slug = $_GET['slug'] ?? '';

// Fetch product and its associated category
$stmt = $pdo->prepare("
    SELECT p.*, c.name as category_name, c.slug as category_slug 
    FROM products p 
    JOIN categories c ON p.category_id = c.id 
    WHERE p.slug = ? AND p.status != 'Hidden'
");
$stmt->execute([$slug]);
$product = $stmt->fetch();

if (!$product) {
    echo "<script>window.location.href='index.php';</script>";
    exit;
}

// Decode technical specifications from JSON
$specs = json_decode($product['list_description'], true) ?? [];
$accentColor = 'cyan';
?>

<main class="pt-32 pb-20 px-6 max-w-7xl mx-auto min-h-screen relative z-10">
    
    <!-- Navigational Breadcrumbs -->
    <div class="mb-8 flex items-center gap-3 text-[10px] font-bold tracking-widest uppercase text-slate-500">
        <a href="index.php" class="hover:text-cyan-600 transition-colors">[ BASE ]</a>
        <span class="text-slate-300">/</span>
        <a href="category.php?slug=<?= htmlspecialchars($product['category_slug']) ?>" class="hover:text-cyan-600 transition-colors">[ <?= htmlspecialchars($product['category_name']) ?> ]</a>
        <span class="text-slate-300">/</span>
        <span class="text-cyan-600">ID_REF: <?= htmlspecialchars($product['sku']) ?></span>
    </div>

    <div class="grid lg:grid-cols-12 gap-12 items-start">
        
        <!-- Visual Asset Presentation (Left Column) -->
        <div class="lg:col-span-5 sticky top-32">
            <div class="sci-fi-card group bg-white border border-slate-200 aspect-square flex items-center justify-center overflow-hidden relative shadow-2xl shadow-slate-200/50">
                <?php if(!empty($product['image'])): ?>
                    <img src="uploads/<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="w-full h-full object-cover transition-transform duration-1000 hover:scale-110">
                <?php else: ?>
                    <div class="w-full h-full flex items-center justify-center text-9xl opacity-5 bg-slate-50">
                        <?= htmlspecialchars($product['icon']) ?>
                    </div>
                <?php endif; ?>
                
                <!-- HUD Overlays -->
                <div class="absolute top-6 left-6 bg-slate-900/90 backdrop-blur-md text-[10px] text-white px-3 py-1.5 tracking-[0.2em] font-mono border border-white/20">
                    <?= htmlspecialchars($product['sku']) ?>
                </div>
                <div class="absolute bottom-6 right-6 bg-cyan-600 text-white text-[10px] px-3 py-1.5 font-bold tracking-widest uppercase animate-pulse shadow-[0_0_15px_rgba(6,182,212,0.5)]">
                    <?= htmlspecialchars($product['status']) ?>
                </div>
            </div>
        </div>

        <!-- Asset Data & Acquisition Terminal (Right Column) -->
        <div class="lg:col-span-7 flex flex-col">
            
            <!-- Header & Pricing -->
            <div class="border-b border-slate-200 pb-8 mb-8">
                <h1 class="text-4xl md:text-5xl font-robot font-bold text-slate-900 uppercase mb-4 tracking-tight leading-none">
                    <?= htmlspecialchars($product['name']) ?>
                </h1>
                <p class="text-sm text-slate-500 font-bold uppercase tracking-widest leading-relaxed">
                    &gt; <?= htmlspecialchars($product['short_description']) ?>
                </p>
                
                <div class="mt-8 flex items-end gap-4">
                    <span class="text-[10px] text-slate-400 font-bold tracking-widest uppercase mb-1">Acquisition_Val //</span>
                    <span class="text-5xl font-robot font-bold text-slate-900 tracking-tighter"><?= htmlspecialchars($product['price_display']) ?></span>
                </div>
            </div>

            <!-- Acquisition Action Terminal -->
            <div class="bg-slate-50 border border-slate-200 p-8 mb-10 relative overflow-hidden">
                <div class="absolute top-0 left-0 w-1 h-full bg-cyan-500"></div>
                <form method="POST" action="cart_action.php" class="flex flex-col sm:flex-row gap-4 items-end">
                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                    <button type="submit" name="add_to_cart" class="w-full sm:w-auto flex-1 bg-slate-900 text-white text-xs font-bold tracking-[0.2em] uppercase py-5 px-8 hover:bg-cyan-600 transition-all shadow-xl hover:shadow-cyan-500/20 active:scale-95 flex items-center justify-center gap-3">
                        <span>[+]</span> INITIATE_ACQUISITION
                    </button>
                </form>
                <div class="mt-4 text-[9px] text-slate-400 font-mono tracking-widest uppercase text-center sm:text-left">
                    * Secure protocol. Deployment pending approval.
                </div>
            </div>

            <!-- Technical Specifications Grid -->
            <?php if (!empty($specs)): ?>
                <div class="mb-10">
                    <h3 class="text-xs font-bold text-slate-900 uppercase tracking-widest mb-6 border-b border-slate-200 pb-2 flex items-center gap-2">
                        <span class="text-cyan-500">[⚙]</span> Technical_Specifications
                    </h3>
                    <ul class="grid sm:grid-cols-2 gap-x-6 gap-y-4">
                        <?php foreach($specs as $spec): ?>
                            <li class="text-sm text-slate-600 flex items-start gap-3">
                                <span class="text-cyan-500 text-[10px] mt-1">◈</span>
                                <span><?= htmlspecialchars($spec) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- Extended Documentation -->
            <div class="prose prose-slate prose-sm max-w-none text-slate-600 leading-loose">
                <?= nl2br(htmlspecialchars($product['full_description'])) ?>
            </div>
        </div>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>