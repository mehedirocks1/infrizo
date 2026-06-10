<?php
require_once '../includes/config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        die('CSRF token validation failed.');
    }

    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Username and password are required.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_user'] = [
                'id' => $admin['id'],
                'username' => $admin['username'],
                'role' => $admin['role']
            ];
            header("Location: index.php");
            exit;
        } else {
            $error = 'Invalid credentials. Access denied.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-slate-900">
    <div class="min-h-screen flex items-center justify-center">
        <div class="max-w-md w-full bg-white p-8 border-t-4 border-cyan-500 shadow-2xl">
            <h1 class="text-3xl font-robot font-bold text-slate-900 mb-4 text-center">ADMIN_AUTH</h1>
            <?php if ($error): ?>
                <div class="p-3 bg-red-500/10 border border-red-500 text-red-600 text-xs font-mono mb-4">
                    [AUTH_FAIL] <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            <form method="POST" action="" class="space-y-6">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                <div>
                    <label class="block text-xs font-bold text-slate-500 mb-2 uppercase tracking-widest">Username</label>
                    <input type="text" name="username" required class="input-cyber w-full p-3 bg-slate-100">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 mb-2 uppercase tracking-widest">Password</label>
                    <input type="password" name="password" required class="input-cyber w-full p-3 bg-slate-100">
                </div>
                <button type="submit" class="w-full btn-cyber btn-cyber-solid py-4 text-sm">Authenticate</button>
            </form>
        </div>
    </div>
</body>
</html>