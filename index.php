<?php
require_once 'includes/header.php';

// Fetch ALL categories dynamically
$all_categories = $pdo->query("
    SELECT * FROM categories
    WHERE status = 'Active'
    ORDER BY id ASC
")->fetchAll();

// Fetch dynamic engineers / units
$engineers = [];
try {
    $stmt = $pdo->query("SELECT * FROM engineers WHERE status = 'Active' ORDER BY id ASC");
    if ($stmt) {
        $engineers = $stmt->fetchAll();
    }
} catch(Exception $e) {}

$first_cat_slug = !empty($all_categories) ? '#' . htmlspecialchars($all_categories[0]['slug']) : '#systems';
?>

  <!-- HERO SECTION -->
  <section class="relative pt-40 pb-20 lg:pt-52 lg:pb-32 px-6 min-h-screen flex items-center overflow-hidden">
    <div class="max-w-7xl mx-auto relative z-10 grid lg:grid-cols-2 gap-12 items-center w-full">
      <div>
        <div class="inline-flex items-center gap-2 bg-cyan-50 border border-cyan-200 text-cyan-700 text-xs tracking-widest px-4 py-2 mb-8 uppercase font-bold shadow-sm">
          <span class="w-2 h-2 bg-cyan-500 animate-pulse"></span> System Status: Online
        </div>
        <h1 class="text-6xl lg:text-8xl font-robot font-bold leading-none mb-6 glitch" data-text="A.I. DRIVEN">A.I. DRIVEN<br/><span class="text-cyan-600">SOLUTIONS</span></h1>
        <p class="text-slate-600 text-lg mb-10 max-w-xl leading-relaxed">
          &gt; Initializing marketplace protocol...<br/>
          &gt; Loading assets...<br/>
          &gt; Standby for operation.<span class="cursor-blink"></span>
        </p>
        <div class="flex flex-wrap gap-6">
          <a href="<?= $first_cat_slug ?>" class="btn-cyber btn-cyber-solid px-8 py-4 text-xs">EXECUTE_DEPLOYMENT</a>
          <a href="cart.php" class="btn-cyber px-8 py-4 bg-white text-xs">VIEW_QUOTES</a>
        </div>
      </div>
      <div class="relative h-[500px] items-center justify-center hidden lg:flex">
        <div class="absolute inset-0 flex items-center justify-center">
          <div class="core-ring ring-1"></div>
          <div class="core-ring ring-2"></div>
          <div class="core-ring ring-3"></div>
          <div class="core-center"></div>
        </div>
      </div>
    </div>
  </section>

<!-- DYNAMIC CATEGORY MODULES -->
<?php foreach ($all_categories as $index => $cat): 
    // Fetch max 12 products for THIS specific category
    $stmt = $pdo->prepare("SELECT * FROM products WHERE category_id = ? AND status != 'Hidden' ORDER BY id ASC LIMIT 12");
    $stmt->execute([$cat['id']]);
    $products = $stmt->fetchAll();
    if (empty($products)) continue;
    
    // UI Logic
    $bgClass = ($index % 2 == 0) ? 'bg-white/50' : 'bg-slate-100/50';
    $accentColor = ['cyan', 'purple', 'indigo', 'blue', 'teal'][$index % 5];
?>
<section id="<?= htmlspecialchars($cat['slug']) ?>" class="py-24 px-6 relative border-t border-slate-200 <?= $bgClass ?>">
  <div class="max-w-7xl mx-auto relative z-10">
    
    <!-- Category Header -->
    <div class="mb-16 text-center md:text-left flex flex-col md:items-start">
      <div class="text-<?= $accentColor ?>-600 font-bold text-xs tracking-[0.3em] mb-4 flex items-center justify-center md:justify-start gap-3">
        <span class="w-8 h-[1px] bg-<?= $accentColor ?>-600"></span> 
        // MODULE: <?= strtoupper(htmlspecialchars($cat['slug'])) ?> 
      </div>
      <h2 class="text-5xl font-robot font-bold text-slate-900 uppercase">
          <?= htmlspecialchars($cat['name']) ?>.
      </h2>
      <p class="text-slate-600 mt-4 max-w-2xl uppercase text-[11px] tracking-widest font-bold leading-relaxed">
          <?= htmlspecialchars($cat['description']) ?>
      </p>
    </div>
    
    <!-- Products Grid -->
    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-10">
      <?php foreach ($products as $p): ?>
          <div class="sci-fi-card group flex flex-col bg-white border border-slate-200 hover:shadow-2xl transition-all duration-500">
              
              <!-- Product Image/Visual Header -->
              <div class="relative h-56 w-full overflow-hidden bg-slate-50 border-b border-slate-100">
                  <?php if(!empty($p['image'])): ?>
                      <img src="uploads/<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110 grayscale group-hover:grayscale-0">
                  <?php else: ?>
                      <div class="w-full h-full flex items-center justify-center text-6xl opacity-10 grayscale">
                          <?= $p['icon'] ?>
                      </div>
                  <?php endif; ?>
                  
                  <!-- HUD Overlays -->
                  <div class="absolute inset-0 bg-gradient-to-t from-slate-900/40 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                  <div class="absolute top-4 left-4 bg-slate-900/90 backdrop-blur-md text-[8px] text-white px-2 py-1 tracking-[0.2em] font-mono border border-white/20">
                      ID_REF: <?= $p['sku'] ?>
                  </div>
                  <div class="absolute bottom-4 right-4 bg-<?= $accentColor ?>-600 text-white text-[8px] px-2 py-1 font-bold tracking-widest uppercase animate-pulse">
                      <?= $p['status'] ?>
                  </div>
              </div>

              <div class="p-8 flex-1 flex flex-col">
                  <div class="flex justify-between items-start mb-4">
                      <h3 class="text-2xl font-robot font-bold text-slate-900 group-hover:text-<?= $accentColor ?>-600 transition-colors tracking-tight">
                          <?= htmlspecialchars($p['name']) ?>
                      </h3>
                      <div class="text-2xl text-<?= $accentColor ?>-500 opacity-30 group-hover:opacity-100 transition-opacity"><?= $p['icon'] ?></div>
                  </div>

                  <p class="text-xs text-slate-500 leading-relaxed mb-8 font-bold uppercase tracking-wide line-clamp-3">
                      &gt; <?= htmlspecialchars($p['short_description']) ?>
                  </p>
                  
                  <div class="mt-auto">
                      <div class="flex items-center justify-between border-t border-slate-100 pt-6">
                          <div class="flex flex-col">
                              <span class="text-[9px] text-slate-400 font-bold tracking-widest uppercase">Acquisition_Val</span>
                              <span class="text-2xl font-robot font-bold text-slate-900 tracking-tighter">
                                  <?= htmlspecialchars($p['price_display']) ?>
                              </span>
                          </div>
                          
                          <div class="flex gap-3">
                              <!-- Detail Button -->
                              <a href="product.php?slug=<?= $p['slug'] ?>" class="flex items-center justify-center w-10 h-10 border border-slate-200 text-slate-400 hover:border-<?= $accentColor ?>-500 hover:text-<?= $accentColor ?>-500 transition-all bg-white shadow-sm" title="Technical Specs">
                                  <span class="text-[10px] font-bold">i</span>
                              </a>
                              
                              <!-- Cart Form -->
                              <form method="POST" action="cart_action.php" class="flex items-stretch m-0">
                                  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                  <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                                  <button type="submit" name="add_to_cart" class="px-6 bg-slate-900 text-white text-[10px] font-bold tracking-[0.2em] uppercase hover:bg-<?= $accentColor ?>-600 transition-all shadow-lg shadow-slate-500/20 flex items-center h-full">
                                      ADD_TO_CART
                                  </button>
                              </form>
                          </div>
                      </div>
                  </div>
              </div>
          </div>
      <?php endforeach; ?>
    </div>

    <div class="mt-12 text-center md:text-left">
        <a href="category.php?slug=<?= htmlspecialchars($cat['slug']) ?>" class="btn-cyber px-8 py-3 text-xs border-<?= $accentColor ?>-600 text-<?= $accentColor ?>-600 hover:bg-<?= $accentColor ?>-600 hover:text-white transition-all">
            VIEW_ALL_<?= strtoupper(htmlspecialchars($cat['name'])) ?> ↗
        </a>
    </div>
  </div>
</section>
<?php endforeach; ?>

  <!-- STATIC MODULE: UNITS -->
  <section id="units" class="py-24 px-6 relative border-t border-slate-200 bg-white/50">
    <div class="max-w-7xl mx-auto relative z-10">
      <div class="text-center mb-16">
        <h2 class="text-5xl font-robot font-bold text-slate-900 mb-4">AVAILABLE <span class="text-cyan-600">UNITS</span></h2>
        <p class="text-slate-600 text-sm font-bold uppercase tracking-widest">Deploy specialized biological assets for technical operations.</p>
      </div>
      <div id="experts-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
          <?php foreach ($engineers as $e): ?>
              <div class="sci-fi-card p-6 h-full flex flex-col items-center text-center relative overflow-hidden border border-slate-200 hover:shadow-xl transition-all duration-300">
                  <div class="text-[10px] font-bold text-cyan-600 tracking-widest w-full text-left mb-6"><?= htmlspecialchars($e['unit_id']) ?> // <?= htmlspecialchars($e['unit_class']) ?></div>
                  <div class="w-24 h-24 rounded-full overflow-hidden mb-4 border-2 border-cyan-500 shadow-[0_0_15px_rgba(6,182,212,0.3)] bg-slate-50 flex items-center justify-center shrink-0">
                      <?php if(!empty($e['photo_path'])): ?>
                          <img src="uploads/photos/<?= htmlspecialchars($e['photo_path']) ?>" class="w-full h-full object-cover">
                      <?php else: ?>
                          <span class="text-3xl text-slate-300 font-mono">?</span>
                      <?php endif; ?>
                  </div>
                  <h3 class="font-robot font-bold text-xl text-slate-900 uppercase"><?= htmlspecialchars($e['name'] ?? 'Unknown Operative') ?></h3>
                  <div class="text-[10px] font-bold text-cyan-700 tracking-[0.2em] mb-3 uppercase"><?= htmlspecialchars($e['role']) ?></div>
                  <p class="text-[11px] text-slate-500 line-clamp-3 mb-6 leading-relaxed italic h-12">"<?= htmlspecialchars($e['details'] ?? 'Ready for deployment.') ?>"</p>
                  <div class="text-2xl font-robot font-bold text-slate-900 mt-auto mb-6">$<?= floatval($e['hourly_rate']) ?><span class="text-xs text-slate-500 ml-1">/HR</span></div>
              </div>
          <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- APPLY CTA -->
  <section class="py-20 px-6 bg-slate-900 text-white text-center border-t-4 border-cyan-900">
    <div class="max-w-4xl mx-auto relative z-10">
        <div class="text-cyan-500 font-bold text-xs tracking-[0.3em] mb-4">// RECRUITMENT_PROTOCOL</div>
        <h2 class="text-4xl font-robot font-bold mb-4">JOIN THE MATRIX.</h2>
        <p class="text-slate-400 mb-8 uppercase tracking-widest text-sm max-w-2xl mx-auto">
          We are seeking skilled freelance operatives. If you possess the talent, we have a mission for you. Submit your credentials for deployment consideration.
        </p>
        <a href="apply.php" class="btn-cyber btn-cyber-solid-white px-10 py-4 text-xs">APPLY_FOR_DEPLOYMENT</a>
    </div>
  </section>

<?php require_once 'includes/footer.php'; ?>