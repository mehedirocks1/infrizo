<?php
require_once __DIR__ . '/config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$settings = [];
try {
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
    while ($row = $stmt->fetch()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
} catch (Exception $e) {}

$site_name = $settings['site_name'] ?? 'INFRIZO';
$seo_desc = $settings['seo_description'] ?? 'Automated IT infrastructure and robotic software solutions. Best IT software company in BD.';
$logo_path = $settings['logo'] ?? '';
$meta_pixel_id = $settings['meta_pixel_id'] ?? '';

$cart_count = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($site_name) ?> - IT Solutions</title>
  <meta name="description" content="<?= htmlspecialchars($seo_desc) ?>">
  <meta name="robots" content="index, follow">
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@400;500;600;700&family=Share+Tech+Mono&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
  
  <?php if (!empty($meta_pixel_id)): ?>
  <!-- Meta Pixel Code -->
  <script>
  !function(f,b,e,v,n,t,s)
  {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
  n.callMethod.apply(n,arguments):n.queue.push(arguments)};
  if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
  n.queue=[];t=b.createElement(e);t.async=!0;
  t.src=v;s=b.getElementsByTagName(e)[0];
  s.parentNode.insertBefore(t,s)}(window, document,'script',
  'https://connect.facebook.net/en_US/fbevents.js');
  fbq('init', '<?= htmlspecialchars($meta_pixel_id) ?>');
  fbq('track', 'PageView');
  </script>
  <noscript><img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id=<?= htmlspecialchars($meta_pixel_id) ?>&ev=PageView&noscript=1"/></noscript>
  <!-- End Meta Pixel Code -->
  <?php endif; ?>

  <!-- AI Scraping Ready / Schema.org -->
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "Organization",
    "name": "<?= htmlspecialchars($site_name) ?>",
    "url": "<?= 'http://' . $_SERVER['HTTP_HOST'] . '/' ?>",
    "logo": "<?= 'http://' . $_SERVER['HTTP_HOST'] . '/uploads/' . htmlspecialchars($logo_path) ?>"
  }
  </script>
</head>
<body class="selection:bg-cyan-500/30 selection:text-white">
  <div class="scanlines"></div>
  <div class="global-cyber-grid"></div>

  <nav id="main-nav" class="fixed top-0 w-full z-50 transition-all duration-300 bg-white/90 backdrop-blur-md py-4 shadow-sm border-b border-slate-200">
    <div class="max-w-7xl mx-auto px-6 flex items-center justify-between">
      <div class="font-robot font-bold text-3xl tracking-widest text-slate-900 flex items-center gap-2">
        <a href="index.php" class="flex items-center gap-2">
            <?php if($logo_path): ?><img src="uploads/<?= htmlspecialchars($logo_path) ?>" alt="Logo" class="h-8"><?php else: ?><span class="text-cyan-600">⟨</span><?= htmlspecialchars($site_name) ?><span class="text-cyan-600">⟩</span><?php endif; ?>
        </a>
      </div>
      <div class="hidden md:flex items-center gap-10 text-sm font-bold tracking-widest text-slate-500" id="desktop-links">
          <a href="index.php#systems" class="hover:text-cyan-600 transition-colors uppercase">Systems</a>
          <a href="cart.php" class="flex items-center gap-2 text-slate-900 hover:text-cyan-600 transition-colors uppercase bg-slate-100 px-4 py-2 border border-slate-200">
              [ CART ]
              <?php if($cart_count > 0): ?><span class="bg-cyan-600 text-white px-2 py-0.5 text-[10px] animate-pulse"><?= $cart_count ?>_ASSETS</span><?php endif; ?>
          </a>
      </div>
      <button class="hidden md:block btn-cyber px-6 py-2 text-sm">INITIATE_CONTACT</button>
      <button id="mobile-menu-btn" class="md:hidden text-cyan-600 text-2xl font-bold">[≡]</button>
    </div>
  </nav>
  <div id="mobile-menu" class="hidden fixed inset-0 z-40 bg-white/95 backdrop-blur-xl border-b border-cyan-200 pt-24 px-6 flex-col gap-6"></div>