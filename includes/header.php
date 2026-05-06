<?php
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
        <span class="text-cyan-600">⟨</span>INFRIZO<span class="text-cyan-600">⟩</span>
      </div>
      
      <div class="hidden md:flex items-center gap-10 text-sm font-bold tracking-widest text-slate-500" id="desktop-links">
        <?php foreach ($navLinks as $index => $link): ?>
          <a href="#<?= strtolower($link) ?>" class="hover:text-cyan-600 transition-colors relative group">
            <span class="text-xs text-cyan-600 mr-1 opacity-0 group-hover:opacity-100 transition-opacity">0<?= $index + 1 ?>.</span>
            <?= $link ?>
          </a>
        <?php endforeach; ?>
      </div>
      
      <button class="hidden md:block btn-cyber px-6 py-2 text-sm">
        INITIATE_CONTACT
      </button>

      <button id="mobile-menu-btn" class="md:hidden text-cyan-600 text-2xl font-bold">
        [≡]
      </button>
    </div>
  </nav>

  <!-- MOBILE MENU -->
  <div id="mobile-menu" class="hidden fixed inset-0 z-40 bg-white/95 backdrop-blur-xl border-b border-cyan-200 pt-24 px-6 flex-col gap-6">
      <?php foreach ($navLinks as $link): ?>
        <a href="#<?= strtolower($link) ?>" class="mobile-link text-2xl font-robot font-bold text-slate-800 hover:text-cyan-600 border-b border-slate-200 pb-4">
          &gt; <?= $link ?>
        </a>
      <?php endforeach; ?>
  </div>