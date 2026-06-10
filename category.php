<?php
require_once 'includes/header.php';

$slug = $_GET['slug'] ?? '';
$stmt = $pdo->prepare("SELECT * FROM categories WHERE slug = ?");
$stmt->execute([$slug]);
$category = $stmt->fetch();

if (!$category) {
    echo "<script>window.location.href='index.php';</script>";
    exit;
}

$min_price = $_GET['min_price'] ?? '';
$max_price = $_GET['max_price'] ?? '';
$status = $_GET['status'] ?? '';

// Pagination Setup
$limit = 12; // Assets per page
$page = isset($_GET['p']) ? max(1, (int)$_GET['p']) : 1;
$offset = ($page - 1) * $limit;

$count_query = "SELECT COUNT(*) FROM products WHERE category_id = :cat_id AND status != 'Hidden'";
$query = "SELECT * FROM products WHERE category_id = :cat_id AND status != 'Hidden'";
$params = [':cat_id' => $category['id']];

if ($min_price !== '') { 
    $count_query .= " AND price_numeric >= :min_price"; 
    $query .= " AND price_numeric >= :min_price"; 
    $params[':min_price'] = $min_price; 
}
if ($max_price !== '') { 
    $count_query .= " AND price_numeric <= :max_price"; 
    $query .= " AND price_numeric <= :max_price"; 
    $params[':max_price'] = $max_price; 
}
if ($status !== '') { 
    $count_query .= " AND status = :status"; 
    $query .= " AND status = :status"; 
    $params[':status'] = $status; 
}

$count_stmt = $pdo->prepare($count_query);
$count_stmt->execute($params);
$total_products = $count_stmt->fetchColumn();
$total_pages = ceil($total_products / $limit);

$query .= " ORDER BY id DESC LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll();
$accentColor = 'cyan';
?>

<main class="pt-32 pb-20 px-6 max-w-7xl mx-auto min-h-screen">
    <div class="flex flex-col md:flex-row gap-10">
        <aside class="w-full md:w-64 shrink-0 bg-white border border-slate-200 p-6 self-start sticky top-32 shadow-xl shadow-slate-200/50">
            <h3 class="text-slate-900 font-bold tracking-widest text-sm uppercase border-b border-slate-200 pb-4 mb-6">Filter Assets</h3>
            <form method="GET" action="category.php" class="space-y-6">
                <input type="hidden" name="slug" value="<?= htmlspecialchars($slug) ?>">
                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2">Price Range ($)</label>
                    <div class="flex gap-2">
                        <input type="number" name="min_price" value="<?= htmlspecialchars($min_price) ?>" placeholder="Min" class="w-full p-2 text-xs border border-slate-200 bg-slate-50 focus:border-cyan-500 focus:outline-none">
                        <input type="number" name="max_price" value="<?= htmlspecialchars($max_price) ?>" placeholder="Max" class="w-full p-2 text-xs border border-slate-200 bg-slate-50 focus:border-cyan-500 focus:outline-none">
                    </div>
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2">Availability</label>
                    <select name="status" class="w-full p-2 text-xs border border-slate-200 bg-slate-50 focus:border-cyan-500 focus:outline-none">
                        <option value="">All Statuses</option>
                        <option value="In Stock" <?= $status == 'In Stock' ? 'selected' : '' ?>>In Stock</option>
                        <option value="Active" <?= $status == 'Active' ? 'selected' : '' ?>>Active (Services)</option>
                    </select>
                </div>
                <button type="submit" class="w-full bg-slate-900 text-white text-[10px] font-bold uppercase tracking-widest py-3 hover:bg-cyan-600 transition-colors">APPLY_FILTERS</button>
                <a href="category.php?slug=<?= htmlspecialchars($slug) ?>" class="block text-center mt-2 text-[10px] font-bold text-slate-400 hover:text-red-500 uppercase tracking-widest">Clear Filters</a>
            </form>
        </aside>

        <div class="flex-1">
            <div class="mb-10">
                <h1 class="text-4xl font-robot font-bold text-slate-900 uppercase"><?= htmlspecialchars($category['name']) ?></h1>
                <p class="text-slate-500 mt-2 text-sm"><?= htmlspecialchars($category['description']) ?></p>
            </div>

            <?php if(empty($products)): ?>
                <div class="p-10 border border-slate-200 text-center text-slate-400 font-mono text-sm tracking-widest uppercase bg-white">[ NO ASSETS FOUND MATCHING PARAMETERS ]</div>
            <?php else: ?>
                <div class="grid lg:grid-cols-2 xl:grid-cols-3 gap-6">
                    <?php foreach($products as $p): ?>
                        <div class="sci-fi-card group flex flex-col bg-white border border-slate-200 hover:shadow-2xl transition-all duration-500">
                            <div class="relative h-48 w-full overflow-hidden bg-slate-50 border-b border-slate-100">
                                <?php if(!empty($p['image'])): ?>
                                    <img src="uploads/<?= htmlspecialchars($p['image']) ?>" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110 grayscale group-hover:grayscale-0">
                                <?php else: ?>
                                    <div class="w-full h-full flex items-center justify-center text-5xl opacity-10 grayscale"><?= $p['icon'] ?></div>
                                <?php endif; ?>
                                <div class="absolute bottom-4 right-4 bg-<?= $accentColor ?>-600 text-white text-[8px] px-2 py-1 font-bold tracking-widest uppercase animate-pulse"><?= $p['status'] ?></div>
                            </div>
                            <div class="p-6 flex-1 flex flex-col">
                                <h3 class="text-xl font-robot font-bold text-slate-900 group-hover:text-<?= $accentColor ?>-600 transition-colors mb-2"><?= htmlspecialchars($p['name']) ?></h3>
                                <p class="text-[10px] text-slate-500 leading-relaxed mb-6 font-bold uppercase tracking-wide line-clamp-2">&gt; <?= htmlspecialchars($p['short_description']) ?></p>
                                <div class="mt-auto flex items-center justify-between border-t border-slate-100 pt-4">
                                    <span class="text-lg font-robot font-bold text-slate-900 tracking-tighter"><?= htmlspecialchars($p['price_display']) ?></span>
                                    <div class="flex gap-2">
                                        <a href="product.php?slug=<?= $p['slug'] ?>" class="flex items-center justify-center w-8 h-8 border border-slate-200 text-slate-400 hover:border-<?= $accentColor ?>-500 hover:text-<?= $accentColor ?>-500 transition-all bg-white shadow-sm" title="Technical Specs">
                                            <span class="text-[10px] font-bold">i</span>
                                        </a>
                                        <form method="POST" action="cart_action.php" class="flex items-stretch m-0">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                            <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                                            <button type="submit" name="add_to_cart" class="px-4 bg-slate-900 text-white text-[9px] font-bold tracking-[0.1em] uppercase hover:bg-<?= $accentColor ?>-600 transition-all">ADD</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if ($total_pages > 1): ?>
                    <div class="mt-12 flex justify-center gap-2">
                        <?php for($i = 1; $i <= $total_pages; $i++): ?>
                            <a href="category.php?slug=<?= htmlspecialchars($slug) ?>&min_price=<?= htmlspecialchars($min_price) ?>&max_price=<?= htmlspecialchars($max_price) ?>&status=<?= htmlspecialchars($status) ?>&p=<?= $i ?>" 
                               class="w-10 h-10 flex items-center justify-center text-xs font-bold border <?= $page == $i ? "bg-{$accentColor}-600 text-white border-{$accentColor}-600" : "bg-white text-slate-500 border-slate-200 hover:border-{$accentColor}-500 hover:text-{$accentColor}-600" ?> transition-all duration-300">
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</main>
<?php require_once 'includes/footer.php'; ?>