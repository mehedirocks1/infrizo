<?php
// Safely start session if not already active to track the cart
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Calculate total cart items for the HUD indicator
$cart_count = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;

// Nav links array
$navLinks = ["SYSTEMS", "NETWORK", "HARDWARE", "UNITS"];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= defined('SITE_TITLE') ? SITE_TITLE : 'INFRIZO IT Solutions' ?></title>
  
  <!-- Tailwind CSS CDN for styling -->
  <script src="https://cdn.tailwindcss.com"></script>

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@400;500;600;700&family=Share+Tech+Mono&display=swap" rel="stylesheet">

  <!-- Custom Styles -->
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="selection:bg-cyan-500/30 selection:text-white">
  
  <div class="scanlines"></div>
  <div class="global-cyber-grid"></div>

  <!-- HUD NAVBAR -->
  <nav id="main-nav" class="fixed top-0 w-full z-50 transition-all duration-300 bg-transparent border-transparent py-6">
    <div class="max-w-7xl mx-auto px-6 flex items-center justify-between">
      <div class="font-robot font-bold text-3xl tracking-widest text-slate-900 flex items-center gap-2">
        <a href="index.php" class="flex items-center">
            <span class="text-cyan-600">⟨</span>INFRIZO<span class="text-cyan-600">⟩</span>
        </a>
      </div>
      
      <div class="hidden md:flex items-center gap-8 text-sm font-bold tracking-widest text-slate-500" id="desktop-links">
        <?php foreach ($navLinks as $index => $link): ?>
          <a href="index.php#<?= strtolower($link) ?>" class="hover:text-cyan-600 transition-colors relative group">
            <span class="text-xs text-cyan-600 mr-1 opacity-0 group-hover:opacity-100 transition-opacity">0<?= $index + 1 ?>.</span>
            <?= $link ?>
          </a>
        <?php endforeach; ?>
        
        <!-- DESKTOP CART HUD -->
        <a href="cart.php" class="flex items-center gap-2 text-slate-900 hover:text-cyan-600 transition-all uppercase bg-white/60 px-4 py-2 border border-slate-200 backdrop-blur-sm group shadow-sm hover:shadow-cyan-500/20">
            <span class="text-cyan-600 group-hover:animate-pulse">[*]</span> CART
            <?php if($cart_count > 0): ?>
                <span class="bg-cyan-600 text-white px-2 py-0.5 text-[10px] animate-pulse font-mono tracking-tighter">
                    <?= $cart_count ?>_ASSET<?= $cart_count > 1 ? 'S' : '' ?>
                </span>
            <?php endif; ?>
        </a>
      </div>
      
      <div class="hidden md:flex items-center gap-4">
          <button class="btn-cyber px-6 py-2 text-sm">
            INITIATE_CONTACT
          </button>
      </div>

      <!-- MOBILE TOGGLE BUTTON -->
      <div class="flex items-center gap-4 md:hidden">
          <!-- Quick Mobile Cart Icon -->
          <a href="cart.php" class="text-slate-900 flex items-center relative">
              <span class="text-xl font-bold">[C]</span>
              <?php if($cart_count > 0): ?>
                  <span class="absolute -top-2 -right-3 bg-cyan-600 text-white text-[9px] px-1.5 py-0.5 font-mono animate-pulse">
                      <?= $cart_count ?>
                  </span>
              <?php endif; ?>
          </a>
          
          <button id="mobile-menu-btn" class="text-cyan-600 text-2xl font-bold">
            [≡]
          </button>
      </div>
    </div>
  </nav>

  <!-- MOBILE MENU -->
  <div id="mobile-menu" class="hidden fixed inset-0 z-40 bg-white/95 backdrop-blur-xl border-b border-cyan-200 pt-24 px-6 flex-col gap-6">
      
      <!-- MOBILE CART LINK -->
      <a href="cart.php" class="mobile-link text-2xl font-robot font-bold text-cyan-600 hover:text-cyan-700 border-b border-cyan-200 pb-4 flex justify-between items-center">
        <span>&gt; CART_MATRIX</span>
        <?php if($cart_count > 0): ?>
            <span class="bg-cyan-600 text-white px-3 py-1 text-sm animate-pulse font-mono">
                <?= $cart_count ?> ASSETS
            </span>
        <?php endif; ?>
      </a>

      <!-- STANDARD MOBILE LINKS -->
      <?php foreach ($navLinks as $link): ?>
        <a href="index.php#<?= strtolower($link) ?>" class="mobile-link text-2xl font-robot font-bold text-slate-800 hover:text-cyan-600 border-b border-slate-200 pb-4">
          &gt; <?= $link ?>
        </a>
      <?php endforeach; ?>
      
      <button class="btn-cyber btn-cyber-solid py-4 text-sm mt-4 w-full">
        INITIATE_CONTACT
      </button>
  </div>