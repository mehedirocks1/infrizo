<?php
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        die('CSRF validation failed.');
    }
    $site_name = $_POST['site_name'] ?? '';
    $seo_description = $_POST['seo_description'] ?? '';
    $meta_pixel_id = $_POST['meta_pixel_id'] ?? '';
    
    $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('site_name', ?) ON DUPLICATE KEY UPDATE setting_value = ?")->execute([$site_name, $site_name]);
    $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('seo_description', ?) ON DUPLICATE KEY UPDATE setting_value = ?")->execute([$seo_description, $seo_description]);
    $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('meta_pixel_id', ?) ON DUPLICATE KEY UPDATE setting_value = ?")->execute([$meta_pixel_id, $meta_pixel_id]);
    
    if (!empty($_FILES['logo']['name'])) {
        $target_dir = "../uploads/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        $file_extension = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
        $new_name = 'logo_' . time() . '.' . $file_extension;
        
        if (in_array($file_extension, ['jpg','jpeg','png','webp','svg'])) {
            if (move_uploaded_file($_FILES['logo']['tmp_name'], $target_dir . $new_name)) {
                $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('logo', ?) ON DUPLICATE KEY UPDATE setting_value = ?")->execute([$new_name, $new_name]);
            }
        }
    }
    $msg = 'System Core Configuration Updated Successfully.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_admin_url'])) {
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        die('CSRF validation failed.');
    }
    $new_url = trim($_POST['new_admin_url']);
    if (preg_match('/^[a-zA-Z0-9_-]+$/', $new_url)) {
        $old_dir = dirname(__DIR__); 
        $base_dir = dirname($old_dir);
        $new_dir = $base_dir . '/' . $new_url;
        
        if (!file_exists($new_dir)) {
            if (@rename($old_dir, $new_dir)) {
                echo "<script>window.location.href='../" . htmlspecialchars($new_url) . "/index.php?page=settings';</script>";
                exit;
            } else {
                $msg = "Failed to rename directory. Check permissions.";
            }
        } else {
            $msg = "Directory already exists.";
        }
    } else {
        $msg = "Invalid folder name (alphanumeric, dashes, underscores only).";
    }
}

$settings = [];
$stmt = $pdo->query("SELECT * FROM settings");
while ($row = $stmt->fetch()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}
?>

<div class="max-w-3xl mx-auto bg-white p-8 shadow-lg">
    <h2 class="text-2xl font-robot font-bold text-slate-800 mb-6 border-b border-slate-200 pb-4">Global System Settings</h2>
    <?php if ($msg): ?><div class="p-3 mb-6 bg-green-500/10 border border-green-500 text-green-600 text-xs font-mono uppercase tracking-widest">[SUCCESS] <?= htmlspecialchars($msg) ?></div><?php endif; ?>
    <form method="POST" action="" enctype="multipart/form-data" class="space-y-6">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
        <div>
            <label class="block text-xs font-bold text-slate-500 mb-2 uppercase tracking-widest">System Name (Site Title)</label>
            <input type="text" name="site_name" value="<?= htmlspecialchars($settings['site_name'] ?? 'INFRIZO') ?>" class="input-cyber w-full p-3 bg-slate-50">
        </div>
        <div>
            <label class="block text-xs font-bold text-slate-500 mb-2 uppercase tracking-widest">SEO Meta Description</label>
            <textarea name="seo_description" rows="3" class="input-cyber w-full p-3 bg-slate-50"><?= htmlspecialchars($settings['seo_description'] ?? '') ?></textarea>
        </div>
        <div>
            <label class="block text-xs font-bold text-slate-500 mb-2 uppercase tracking-widest">Meta Pixel ID (Tracking)</label>
            <input type="text" name="meta_pixel_id" value="<?= htmlspecialchars($settings['meta_pixel_id'] ?? '') ?>" class="input-cyber w-full p-3 bg-slate-50" placeholder="e.g. 123456789012345">
        </div>
        <div>
            <label class="block text-xs font-bold text-slate-500 mb-2 uppercase tracking-widest">System Logo Overlay (PNG, SVG, WEBP)</label>
            <?php if (!empty($settings['logo'])): ?><div class="mb-4 bg-slate-100 p-4 inline-block border border-slate-200"><img src="../uploads/<?= htmlspecialchars($settings['logo']) ?>" class="h-12 object-contain"></div><?php endif; ?>
            <input type="file" name="logo" class="input-cyber file-input w-full text-sm">
        </div>
        <button type="submit" name="save_settings" class="w-full btn-cyber btn-cyber-solid py-4 text-sm mt-4">SAVE_CONFIGURATION</button>
    </form>
    
    <h2 class="text-2xl font-robot font-bold text-slate-800 mt-12 mb-6 border-b border-slate-200 pb-4">Security: Change Admin URL</h2>
    <form method="POST" action="" class="space-y-6">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
        <div class="p-4 bg-yellow-50 border border-yellow-200 text-yellow-800 text-xs mb-4">
            <strong>WARNING:</strong> Changing this will immediately rename the admin directory. Ensure the web server user has correct file permissions to rename directories.
        </div>
        <div>
            <label class="block text-xs font-bold text-slate-500 mb-2 uppercase tracking-widest">New Admin Directory Name</label>
            <input type="text" name="new_admin_url" value="<?= htmlspecialchars(basename(dirname(__DIR__))) ?>" class="input-cyber w-full p-3 bg-slate-50" placeholder="e.g. secure-admin-panel">
        </div>
        <button type="submit" name="change_admin_url" class="w-full btn-cyber btn-cyber-solid py-4 text-sm mt-4 bg-red-600 hover:bg-red-700 text-white">RENAME ADMIN DIRECTORY</button>
    </form>
</div>