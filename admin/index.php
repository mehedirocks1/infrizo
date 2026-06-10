<?php
ob_start(); // Prevent headers already sent errors for modular page redirects
// 1. Require Database Connection FIRST (This securely starts the session!)
require_once '../includes/config.php';

// 2. Require Authentication Guard SECOND (Now it can read the session)
require_once 'auth.php';

// 3. Simple Routing System
$page = $_GET['page'] ?? 'dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>INFRIZO - SYSTEM COMMAND</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@400;500;600;700&family=Share+Tech+Mono&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/style.css">

  <style>
    .admin-sidebar { background: rgba(15, 23, 42, 0.95); border-right: 1px solid var(--neon-cyan); }
    .input-cyber { background: transparent; border: 1px solid var(--hud-border); color: var(--text-main); font-family: 'Share Tech Mono', monospace; }
    .input-cyber:focus { outline: none; border-color: var(--neon-cyan); box-shadow: 0 0 10px rgba(2, 132, 199, 0.2); }
  </style>
</head>
<body class="selection:bg-cyan-500/30 selection:text-white flex h-screen overflow-hidden text-slate-800">
  
  <div class="scanlines"></div>
  <div class="global-cyber-grid"></div>

  <!-- INJECT MODULAR SIDEBAR HERE -->
  <?php require_once 'sidebar.php'; ?>

  <!-- MAIN DASHBOARD AREA -->
  <main class="flex-1 h-full overflow-y-auto relative z-10 p-10 bg-slate-50/90 backdrop-blur-sm">
      
      <?php
      // Secure Routing Logic
      $allowed_pages = [
          'dashboard', 
          'categories', 
          'products', 
          'add_product', 
          'edit_product',
          'delete_product',
          'delete_category',
          'edit_category',
          'orders',
          'view_order',
          'generate_quote',
          'applications',
          'inquiries',
          'engineers',
          'engineer_form',
          'settings',
          'admins',
          'backup'

      ];
      
      if (in_array($page, $allowed_pages)) {
          // This pulls the content from the pages folder based on the URL
          require_once "pages/{$page}.php";
      } else {
          echo '<h1 class="text-4xl font-robot font-bold text-red-600 mb-6">ERROR 404.</h1>';
          echo '<p>Directory not found or access denied.</p>';
      }
      ?>

  </main>
</body>
</html>