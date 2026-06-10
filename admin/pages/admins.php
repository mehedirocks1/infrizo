<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_admin'])) {
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        die('CSRF token validation failed.');
    }
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO admins (username, password, role) VALUES (?, ?, 'Admin')");
    try {
        $stmt->execute([$username, $password]);
        $msg = "Admin access granted.";
    } catch (PDOException $e) {
        $error = "Username already exists in the matrix.";
    }
}

if (isset($_GET['delete'])) {
    if (!isset($_GET['token']) || !verify_csrf_token($_GET['token'])) {
        die('CSRF token validation failed.');
    }
    $id = filter_var($_GET['delete'], FILTER_VALIDATE_INT);
    if ($id && $id != $_SESSION['admin_user']['id']) {
        $pdo->prepare("DELETE FROM admins WHERE id = ?")->execute([$id]);
    }
    header("Location: index.php?page=admins");
    exit;
}

$admins = $pdo->query("SELECT id, username, role, created_at FROM admins ORDER BY id ASC")->fetchAll();
?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-3xl font-robot font-bold text-slate-800">Administrator Access Matrix</h1>
</div>

<?php if (isset($msg)): ?><div class="p-3 mb-6 bg-green-500/10 border border-green-500 text-green-600 text-xs font-mono uppercase tracking-widest">[SUCCESS] <?= $msg ?></div><?php endif; ?>
<?php if (isset($error)): ?><div class="p-3 mb-6 bg-red-500/10 border border-red-500 text-red-600 text-xs font-mono uppercase tracking-widest">[ERROR] <?= $error ?></div><?php endif; ?>

<div class="grid md:grid-cols-3 gap-6">
    <div class="md:col-span-1">
        <div class="bg-white p-6 shadow-lg border-t-4 border-cyan-500">
            <h3 class="text-sm font-bold uppercase tracking-widest mb-4">Grant Access</h3>
            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                <div class="mb-4">
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2">Username / Email</label>
                    <input type="text" name="username" required class="input-cyber w-full p-3 bg-slate-50 text-sm">
                </div>
                <div class="mb-6">
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2">Password</label>
                    <input type="password" name="password" required class="input-cyber w-full p-3 bg-slate-50 text-sm">
                </div>
                <button type="submit" name="add_admin" class="w-full btn-cyber btn-cyber-solid py-3 text-xs">CREATE_USER</button>
            </form>
        </div>
    </div>
    <div class="md:col-span-2">
        <div class="bg-white shadow-lg overflow-x-auto">
            <table class="w-full text-sm text-left text-slate-500">
                <thead class="text-xs text-slate-700 uppercase bg-slate-50">
                    <tr><th class="px-6 py-3">ID</th><th class="px-6 py-3">Username</th><th class="px-6 py-3">Role</th><th class="px-6 py-3">Actions</th></tr>
                </thead>
                <tbody>
                    <?php foreach($admins as $adm): ?>
                    <tr class="bg-white border-b">
                        <td class="px-6 py-4 font-mono">#<?= $adm['id'] ?></td>
                        <td class="px-6 py-4 font-bold text-slate-900"><?= htmlspecialchars($adm['username']) ?></td>
                        <td class="px-6 py-4"><span class="px-2 py-1 text-[10px] font-bold uppercase bg-slate-100"><?= $adm['role'] ?></span></td>
                        <td class="px-6 py-4">
                            <?php if ($adm['id'] != $_SESSION['admin_user']['id']): ?>
                                <a href="?page=admins&delete=<?= $adm['id'] ?>&token=<?= htmlspecialchars($_SESSION['csrf_token']) ?>" onclick="return confirm('Revoke access?')" class="text-red-500 hover:text-red-700 text-[10px] font-bold uppercase tracking-widest">Revoke</a>
                            <?php else: ?><span class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">Current</span><?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>