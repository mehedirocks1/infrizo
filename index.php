<?php
require_once 'includes/config.php';

/**
 * 1. FETCH DYNAMIC E-COMMERCE CATEGORIES
 * This only pulls categories that have 'Network' or 'Hardware' in their slug
 * (or you can remove the WHERE clause to show EVERY category you create).
 */
$ecommerce_categories = $pdo->query("
    SELECT * FROM categories 
    WHERE slug IN ('network', 'hardware') 
    ORDER BY id ASC
")->fetchAll();

// We will fetch products inside the loop below to keep the code clean.
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>INFRIZO - IT Solutions || Best IT software company in Bangladesh</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@400;500;600;700&family=Share+Tech+Mono&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="selection:bg-cyan-500/30 selection:text-white">
  
  <div class="scanlines"></div>
  <div class="global-cyber-grid"></div>

  <!-- HUD NAVBAR -->
  <nav id="main-nav" class="fixed top-0 w-full z-50 transition-all duration-300 bg-transparent border-transparent py-6">
    <div class="max-w-7xl mx-auto px-6 flex items-center justify-between">
      <div class="font-robot font-bold text-3xl tracking-widest text-slate-900 flex items-center gap-2">
        <span class="text-cyan-600">⟨</span>INFRIZO<span class="text-cyan-600">⟩</span>
      </div>
      <div class="hidden md:flex items-center gap-10 text-sm font-bold tracking-widest text-slate-500" id="desktop-links"></div>
      <button class="hidden md:block btn-cyber px-6 py-2 text-sm">INITIATE_CONTACT</button>
      <button id="mobile-menu-btn" class="md:hidden text-cyan-600 text-2xl font-bold">[≡]</button>
    </div>
  </nav>

  <div id="mobile-menu" class="hidden fixed inset-0 z-40 bg-white/95 backdrop-blur-xl border-b border-cyan-200 pt-24 px-6 flex-col gap-6"></div>

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
          <button class="btn-cyber btn-cyber-solid px-8 py-4">EXECUTE_DEPLOYMENT</button>
          <button class="btn-cyber px-8 py-4 bg-white">VIEW_LOGS</button>
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

  <!-- STATIC MODULE: SOFTWARE -->
  <section id="systems" class="py-24 px-6 relative border-t border-slate-200 bg-slate-100/50">
    <div class="max-w-7xl mx-auto relative z-10">
      <div class="mb-16 text-center md:text-left">
        <div class="text-cyan-600 font-bold text-xs tracking-[0.3em] mb-4 flex items-center justify-center md:justify-start gap-3">
          <span class="w-8 h-[1px] bg-cyan-600"></span> // MODULE: SOFTWARE
        </div>
        <h2 class="text-5xl font-robot font-bold text-slate-900">DIGITAL <span class="text-transparent bg-clip-text bg-gradient-to-r from-cyan-600 to-purple-600">CONSTRUCTS</span>.</h2>
      </div>
      <div id="systems-grid" class="grid md:grid-cols-2 lg:grid-cols-3 gap-6"></div>
    </div>
  </section>


<!-- DYNAMIC MODULES: E-COMMERCE -->
<?php foreach ($ecommerce_categories as $index => $cat): 
    // Fetch products for THIS specific category
    $stmt = $pdo->prepare("SELECT * FROM products WHERE category_id = ? AND status != 'Hidden' ORDER BY id ASC");
    $stmt->execute([$cat['id']]);
    $products = $stmt->fetchAll();
    
    // UI Logic
    $bgClass = ($index % 2 == 0) ? 'bg-white/50' : 'bg-slate-100/50';
    $isHardware = ($cat['slug'] == 'hardware');
    $accentColor = $isHardware ? 'purple' : 'cyan';
?>
<section id="<?= $cat['slug'] ?>" class="py-24 px-6 relative border-t border-slate-200 <?= $bgClass ?>">
  <div class="max-w-7xl mx-auto relative z-10">
    
    <!-- Category Header -->
    <div class="mb-16 text-center <?= $isHardware ? 'md:text-right flex flex-col md:items-end' : 'md:text-left' ?>">
      <div class="text-<?= $accentColor ?>-600 font-bold text-xs tracking-[0.3em] mb-4 flex items-center justify-center <?= $isHardware ? 'md:justify-end' : 'md:justify-start' ?> gap-3">
        <?= !$isHardware ? '<span class="w-8 h-[1px] bg-cyan-600"></span>' : '' ?> 
        // MODULE: <?= strtoupper($cat['slug']) ?> 
        <?= $isHardware ? '<span class="w-8 h-[1px] bg-purple-600 hidden md:block"></span>' : '' ?>
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
          <div class="sci-fi-card group flex flex-col bg-white border border-slate-200 hover:shadow-2xl transition-all duration-500" style="<?= $isHardware ? 'border-color: rgba(124, 58, 237, 0.1)' : '' ?>">
              
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
                              
                              <!-- Order Link (Linked to your order.php) -->
                              <a href="order.php?asset_id=<?= $p['id'] ?>" class="px-6 py-2 bg-slate-900 text-white text-[10px] font-bold tracking-[0.2em] uppercase hover:bg-<?= $accentColor ?>-600 transition-all shadow-lg shadow-slate-500/20 flex items-center">
                                  <?= $isHardware ? 'DEPLOY' : 'ACQUIRE' ?>
                              </a>
                          </div>
                      </div>
                  </div>
              </div>
          </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endforeach; ?>

  <!-- STATIC MODULE: UNITS -->
  <section id="units" class="py-24 px-6 relative border-t border-slate-200 bg-white/50">
    <div class="max-w-7xl mx-auto relative z-10">
      <div class="text-center mb-16">
        <h2 class="text-5xl font-robot font-bold text-slate-900 mb-4">AVAILABLE <span class="text-cyan-600">UNITS</span></h2>
        <p class="text-slate-600 text-sm">Deploy specialized biological assets for technical operations.</p>
      </div>
      <div id="experts-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6"></div>
    </div>
  </section>

  <!-- TERMINAL FOOTER -->
  <footer class="relative bg-slate-100 pt-16 pb-8 px-6 border-t-4 border-cyan-600 z-10">
    <div class="max-w-7xl mx-auto text-center md:text-left">
      <div class="grid md:grid-cols-4 gap-12 mb-12">
        <div class="col-span-1 md:col-span-2">
          <div class="font-robot font-bold text-3xl tracking-widest text-slate-900 mb-6">⟨INFRIZO⟩</div>
          <p class="text-slate-600 text-xs max-w-sm mx-auto md:mx-0 leading-relaxed uppercase tracking-wider font-bold">Automated IT infrastructure. Running protocol v2.0.4.</p>
        </div>
      </div>
      <div class="pt-6 border-t border-slate-300 text-[10px] tracking-[0.2em] text-slate-500 font-bold uppercase">© 2026 INFRIZO SYS. ALL RIGHTS RESERVED.</div>
    </div>
  </footer>

  <script>
    const softwareServices = [
      { id: "SW-01", name: "Custom Web Dev", icon: "⟨/⟩", desc: "Full-stack web apps compiled with modern architecture." },
      { id: "SW-02", name: "ERP Systems", icon: "[⚙]", desc: "Enterprise resource planning to automate operations." },
      { id: "SW-03", name: "POS Interface", icon: "◈", desc: "Point-of-sale terminals for retail and service sectors." },
      { id: "SW-04", name: "HRM & Payroll", icon: "⎔", desc: "Algorithmic HR management and payroll processing." },
      { id: "SW-05", name: "Mobile App Dev", icon: "📱", desc: "Cross-platform mobile applications." },
      { id: "SW-06", name: "Cloud Sync", icon: "☁", desc: "Secure cloud migration and continuous integration." },
    ];
    const experts = [
      { id: "UNIT-01", role: "Network Eng.", rate: "$15", class: "Alpha" },
      { id: "UNIT-02", role: "Cyber Security", rate: "$20", class: "Shield" },
      { id: "UNIT-03", role: "Full Stack", rate: "$18", class: "Dev" },
      { id: "UNIT-04", role: "Cloud Arch.", rate: "$25", class: "Nexus" },
    ];

    document.getElementById('systems-grid').innerHTML = softwareServices.map(s => `
      <div class="sci-fi-card p-8 group">
        <div class="flex justify-between items-start mb-6"><div class="text-3xl text-cyan-600">${s.icon}</div><div class="text-[10px] font-bold tracking-widest text-slate-500">${s.id}</div></div>
        <h3 class="text-2xl font-robot font-bold text-slate-900 mb-3 group-hover:text-cyan-600 transition-colors">${s.name}</h3>
        <p class="text-sm text-slate-600 leading-relaxed mb-6">${s.desc}</p>
        <div class="text-xs text-cyan-700 font-bold uppercase tracking-widest cursor-pointer hover:text-cyan-500">Compile.run() ↗</div>
      </div>`).join('');

    document.getElementById('experts-grid').innerHTML = experts.map(e => `
      <div class="sci-fi-card p-6 h-full flex flex-col items-center text-center relative overflow-hidden">
        <div class="text-[10px] font-bold text-cyan-600 tracking-widest w-full text-left mb-6">${e.id} // ${e.class}</div>
        <h3 class="font-robot font-bold text-xl text-slate-900 mb-1">${e.role}</h3>
        <div class="text-2xl font-robot font-bold text-slate-900 mt-auto">${e.rate}<span class="text-xs text-slate-500 ml-1">/HR</span></div>
      </div>`).join('');

    const nav = document.getElementById('main-nav');
    window.addEventListener('scroll', () => {
      window.scrollY > 20 ? nav.classList.add('bg-white/90', 'backdrop-blur-md', 'py-4', 'shadow-sm') : nav.classList.remove('bg-white/90', 'backdrop-blur-md', 'py-4', 'shadow-sm');
    });
  </script>
</body>
</html>