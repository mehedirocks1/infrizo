<?php
require_once 'includes/config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Security Check: Validate Admin Login here if implemented
// if (!isset($_SESSION['admin_logged_in'])) { header('Location: login.php'); exit; }

$success_msg = '';
$error_msg = '';

// ==========================================
// 1. HANDLE DELETE PRODUCT
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_product'])) {
    $product_id = (int)$_POST['product_id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        $success_msg = "Asset successfully purged from the matrix.";
    } catch (Exception $e) {
        $error_msg = "Database Error: " . $e->getMessage();
    }
}

// ==========================================
// 2. HANDLE ADDING NEW PRODUCT
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $category_id = (int)$_POST['category_id'];
    $sku = trim($_POST['sku']);
    $name = trim($_POST['name']);
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
    $short_desc = trim($_POST['short_description']);
    $icon = trim($_POST['icon']);
    $price_display = trim($_POST['price_display'] ?? 'Custom Quote');
    $status = trim($_POST['status'] ?? 'Active');
    
    $image_path = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = "uploads/";
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        
        $file_ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (in_array($file_ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
            $image_path = uniqid('sw_') . '.' . $file_ext;
            move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $image_path);
        } else {
            $error_msg = "Invalid image format.";
        }
    }

    if (empty($error_msg)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO products 
                (category_id, sku, name, slug, short_description, icon, image, price_display, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$category_id, $sku, $name, $slug, $short_desc, $icon, $image_path, $price_display, $status]);
            $success_msg = "Software/Asset module successfully deployed.";
        } catch (PDOException $e) {
            $error_msg = "Database error: " . $e->getMessage();
        }
    }
}

// ==========================================
// 3. HANDLE ADDING SOFTWARE INFORMATION
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_software_info'])) {
    $product_id = (int)$_POST['info_product_id'];
    $feature_name = trim($_POST['feature_name']);
    $feature_value = trim($_POST['feature_value']);

    if (!empty($product_id) && !empty($feature_name) && !empty($feature_value)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO software_information (product_id, feature_name, feature_value) VALUES (?, ?, ?)");
            $stmt->execute([$product_id, $feature_name, $feature_value]);
            $success_msg = "Software Information parameter successfully linked to the selected asset.";
        } catch (PDOException $e) {
            $error_msg = "Database error: " . $e->getMessage();
        }
    } else {
        $error_msg = "All information fields are required.";
    }
}

// ==========================================
// 4. HANDLE DELETE SOFTWARE INFORMATION
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_info'])) {
    $info_id = (int)$_POST['info_id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM software_information WHERE id = ?");
        $stmt->execute([$info_id]);
        $success_msg = "Software parameter successfully deleted.";
    } catch (PDOException $e) {
        $error_msg = "Database error: " . $e->getMessage();
    }
}

// Fetch necessary data for UI
$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
$products = $pdo->query("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC")->fetchAll();

require_once 'includes/header.php';
?>

<main class="pt-32 pb-20 px-6 max-w-[1400px] mx-auto min-h-screen bg-slate-50">
    <div class="mb-8 border-b border-slate-200 pb-4">
        <h1 class="text-3xl font-robot font-bold text-slate-900 uppercase">Asset Database & Deployment</h1>
        <p class="text-slate-500 text-xs tracking-widest uppercase font-bold mt-2">Manage all software, hardware, and network assets.</p>
    </div>

    <?php if ($success_msg): ?>
        <div class="mb-8 p-4 border border-cyan-500 bg-cyan-50 text-cyan-700 text-xs font-mono uppercase tracking-widest shadow-sm">
            [ SUCCESS ] <?= htmlspecialchars($success_msg) ?>
        </div>
    <?php endif; ?>

    <?php if ($error_msg): ?>
        <div class="mb-8 p-4 border border-red-500 bg-red-500/10 text-red-500 text-xs font-mono uppercase tracking-widest shadow-sm">
            [ ERROR ] <?= htmlspecialchars($error_msg) ?>
        </div>
    <?php endif; ?>

    <div class="grid lg:grid-cols-12 gap-10 items-start">
        
        <!-- SECTION: ADD SOFTWARE / ASSET -->
        <div class="lg:col-span-4 space-y-10 sticky top-32">
            <!-- Add Asset Form -->
            <div class="bg-white p-8 border border-slate-200 shadow-2xl border-t-4 border-cyan-600">
                <h2 class="text-2xl font-robot font-bold text-slate-900 mb-2 uppercase">Deploy Asset</h2>
                <form method="POST" enctype="multipart/form-data" class="space-y-4 mt-6">
                    <select name="category_id" required class="input-cyber w-full p-3 text-xs bg-slate-50 focus:bg-white border-slate-200">
                        <option value="">-- Select Category --</option>
                        <?php foreach($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="text" name="sku" required class="input-cyber w-full p-3 text-xs bg-slate-50" placeholder="SKU / ID (e.g. SW-07)">
                    <input type="text" name="name" required class="input-cyber w-full p-3 text-xs bg-slate-50" placeholder="Asset Name">
                    <input type="text" name="icon" class="input-cyber w-full p-3 text-xs bg-slate-50" placeholder="Icon Symbol (e.g. ⎔)">
                    <input type="file" name="image" class="input-cyber w-full p-2 text-xs bg-slate-50 file:mr-2 file:border-0 file:bg-slate-200 file:px-2 file:py-1">
                    <textarea name="short_description" required rows="2" class="input-cyber w-full p-3 text-xs bg-slate-50 resize-none" placeholder="Short Description..."></textarea>
                    <button type="submit" name="add_product" class="w-full btn-cyber btn-cyber-solid py-3 text-xs font-bold tracking-widest">ADD_ASSET</button>
                </form>
            </div>
            
            <!-- Add Specs Form -->
            <div class="bg-white p-8 border border-slate-200 shadow-2xl border-t-4 border-indigo-600">
                <h2 class="text-xl font-robot font-bold text-slate-900 mb-2 uppercase">Link Specs</h2>
                <form method="POST" class="space-y-4 mt-6">
                    <select name="info_product_id" required class="input-cyber w-full p-3 text-xs bg-slate-50 border-slate-200">
                        <option value="">-- Select Target Asset --</option>
                        <?php foreach($products as $prod): ?>
                            <option value="<?= $prod['id'] ?>">[<?= htmlspecialchars($prod['sku']) ?>] <?= htmlspecialchars($prod['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="text" name="feature_name" required class="input-cyber w-full p-3 text-xs bg-slate-50" placeholder="Parameter (e.g. Architecture)">
                    <input type="text" name="feature_value" required class="input-cyber w-full p-3 text-xs bg-slate-50" placeholder="Value (e.g. Microservices)">
                    <button type="submit" name="add_software_info" class="w-full btn-cyber py-3 text-xs font-bold border border-indigo-600 text-indigo-600 hover:bg-indigo-600 hover:text-white transition-colors">LINK_PARAMETER</button>
                </form>
            </div>
        </div>

        <!-- SECTION: ASSET DATABASE TABLE -->
        <div class="lg:col-span-8 bg-white border border-slate-200 shadow-2xl overflow-x-auto border-t-4 border-slate-900">
            <table class="w-full text-left border-collapse min-w-[800px]">
                <thead>
                    <tr class="bg-slate-900 text-white text-[9px] tracking-widest uppercase font-bold">
                        <th class="p-4 w-1/3">Asset Data</th>
                        <th class="p-4 w-1/3">Tech Specs / Info</th>
                        <th class="p-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    <?php if (empty($products)): ?>
                        <tr><td colspan="3" class="p-8 text-center text-slate-400 font-mono text-xs uppercase tracking-widest">[ NO ASSETS IN DATABASE ]</td></tr>
                    <?php else: ?>
                        <?php foreach ($products as $p): ?>
                            <tr class="border-b border-slate-100 hover:bg-slate-50/50 transition-colors">
                                <td class="p-4 align-top">
                                    <div class="font-mono text-cyan-700 font-bold text-xs">[<?= htmlspecialchars($p['sku']) ?>]</div>
                                    <div class="font-bold text-slate-900 text-sm mt-1"><?= htmlspecialchars($p['name']) ?></div>
                                    <div class="text-[9px] tracking-widest uppercase text-slate-500 mb-2">MOD: <?= htmlspecialchars($p['category_name']) ?></div>
                                    <div class="text-[10px] text-slate-500"><?= htmlspecialchars($p['short_description']) ?></div>
                                </td>
                                <td class="p-4 align-top">
                                    <?php 
                                    $info_stmt = $pdo->prepare("SELECT * FROM software_information WHERE product_id = ?");
                                    $info_stmt->execute([$p['id']]);
                                    $infos = $info_stmt->fetchAll();
                                    if(empty($infos)): ?>
                                        <span class="text-[9px] text-slate-400 font-bold uppercase tracking-widest">No Specs Linked</span>
                                    <?php else: ?>
                                        <ul class="space-y-2">
                                            <?php foreach($infos as $i): ?>
                                                <li class="text-[10px] flex items-center justify-between border-b border-slate-100 pb-1">
                                                    <span class="font-mono text-slate-600">
                                                        <span class="text-cyan-600 font-bold">[<?= htmlspecialchars($i['feature_name']) ?>]</span> <?= htmlspecialchars($i['feature_value']) ?>
                                                    </span>
                                                    <form method="POST" class="m-0" onsubmit="return confirm('Remove parameter?');">
                                                        <input type="hidden" name="info_id" value="<?= $i['id'] ?>">
                                                        <button type="submit" name="delete_info" class="text-red-400 hover:text-red-600 ml-2 font-bold" title="Delete Spec">×</button>
                                                    </form>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>
                                </td>
                                <td class="p-4 align-top text-right">
                                    <form method="POST" class="m-0" onsubmit="return confirm('WARNING: Are you sure you want to completely delete this asset?');">
                                        <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                                        <button type="submit" name="delete_product" class="text-[9px] text-white bg-red-500 hover:bg-red-600 px-3 py-2 font-bold tracking-widest uppercase transition-colors">
                                            DELETE
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>