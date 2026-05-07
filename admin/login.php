<?php
// 1. Require config (This now securely starts the session for us!)
require_once '../includes/config.php';



// 2. Auto-Redirect: If you are already logged in, go straight to the dashboard
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: index.php");
    exit;
}

// --- AUTO-SETUP (Creates default admin if the table is empty) ---
$checkAdmin = $pdo->query("SELECT COUNT(*) FROM admins")->fetchColumn();
if ($checkAdmin == 0) {
    $defaultPass = password_hash('admin123', PASSWORD_DEFAULT);
    $pdo->query("INSERT INTO admins (username, password) VALUES ('admin', '$defaultPass')");
}
// -----------------------------------------------------------------

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = :username LIMIT 1");
    $stmt->execute(['username' => $username]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password'])) {
        // Login Success
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $admin['username'];
        
        // Optional: Update last login timestamp
        $pdo->prepare("UPDATE admins SET last_login = NOW() WHERE id = ?")->execute([$admin['id']]);
        
        header("Location: index.php");
        exit;
    } else {
        $error = "ACCESS DENIED: Invalid credentials.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>INFRIZO - SYSTEM AUTHENTICATION</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@400;500;600;700&family=Share+Tech+Mono&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-slate-900 flex items-center justify-center min-h-screen relative overflow-hidden text-slate-300">
  
  <div class="scanlines"></div>
  <div class="global-cyber-grid opacity-20"></div>

  <div class="relative z-10 w-full max-w-md">
      <div class="text-center mb-8">
          <div class="font-robot font-bold text-4xl tracking-widest text-white mb-2">
              <span class="text-cyan-500">⟨</span>INFRIZO<span class="text-cyan-500">⟩</span>
          </div>
          <div class="text-[10px] text-cyan-500 tracking-[0.3em] font-bold animate-pulse">RESTRICTED TERMINAL</div>
      </div>

      <div class="sci-fi-card p-8 bg-slate-800/90 border-cyan-500 shadow-[0_0_30px_rgba(2,132,199,0.2)]">
          <form method="POST" action="">
              
              <?php if($error): ?>
              <div class="mb-6 p-3 border border-red-500 bg-red-500/10 text-red-400 text-xs tracking-widest font-bold text-center">
                  [!] <?= $error ?>
              </div>
              <?php endif; ?>

              <div class="mb-6">
                  <label class="block text-[10px] font-bold text-cyan-500 tracking-widest mb-2 uppercase">Admin ID</label>
                  <input type="text" name="username" required class="w-full bg-slate-900 border border-slate-600 p-3 text-sm text-cyan-400 font-mono focus:outline-none focus:border-cyan-500 focus:shadow-[0_0_10px_rgba(2,132,199,0.3)] transition-all">
              </div>

              <div class="mb-8">
                  <label class="block text-[10px] font-bold text-cyan-500 tracking-widest mb-2 uppercase">Passcode</label>
                  <input type="password" name="password" required class="w-full bg-slate-900 border border-slate-600 p-3 text-sm text-cyan-400 font-mono focus:outline-none focus:border-cyan-500 focus:shadow-[0_0_10px_rgba(2,132,199,0.3)] transition-all">
              </div>

              <button type="submit" class="w-full py-4 border-2 border-cyan-500 text-cyan-500 hover:bg-cyan-500 hover:text-slate-900 transition-colors font-robot font-bold tracking-widest uppercase text-sm relative overflow-hidden group">
                  <span class="relative z-10">INITIATE_LOGIN</span>
              </button>
          </form>
      </div>
  </div>
</body>
</html>