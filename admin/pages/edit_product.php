<?php
// 1. Get the Product ID from the URL
$product_id = $_GET['id'] ?? null;

if (!$product_id) {
    echo '<div class="p-4 bg-red-100 border border-red-500 text-red-800 font-bold">ERROR: NO ASSET ID PROVIDED.</div>';
    exit;
}

// 2. Handle Form Submission (Updating the database)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_product'])) {
    $cat_id = $_POST['category_id'];
    $sku = $_POST['sku'];
    $name = $_POST['name'];
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $_POST['slug'])));
    $price_num = $_POST['price_numeric'] ?: 0.00;
    $price_disp = $_POST['price_display'];
    $icon = $_POST['icon'];
    $short_desc = $_POST['short_description'];
    $status = $_POST['status'];

    // --- HANDLE JSON LIST DESCRIPTION ---
    $list_input = explode("\n", str_replace("\r", "", $_POST['list_description']));
    $list_json  = json_encode(array_filter(array_map('trim', $list_input)));

    // --- HANDLE IMAGE UPDATE ---
    $image_query = "";
    $params = [$cat_id, $sku, $name, $slug, $price_num, $price_disp, $icon, $short_desc, $list_json, $status];

    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $upload_dir = '../uploads/';
        $file_ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $image_name = $slug . '-' . time() . '.' . $file_ext;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $image_name)) {
            $image_query = ", image = ?";
            $params[] = $image_name;
        }
    }

    $params[] = $product_id; // For the WHERE clause

    $update_stmt = $pdo->prepare("
        UPDATE products 
        SET category_id = ?, sku = ?, name = ?, slug = ?, price_numeric = ?, price_display = ?, icon = ?, short_description = ?, list_description = ?, status = ? $image_query
        WHERE id = ?
    ");
    
    try {
        $update_stmt->execute($params);
        echo '<div class="p-4 mb-6 bg-cyan-100 border border-cyan-500 text-cyan-800 font-bold tracking-widest">ASSET PARAMETERS RECONFIGURED SUCCESSFULLY.</div>';
    } catch (PDOException $e) {
        echo '<div class="p-4 mb-6 bg-red-100 border border-red-500 text-red-800 font-bold">ERROR: SKU or SLUG conflict.</div>';
    }
}

// 3. Fetch the Current Product Data
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

// Convert JSON back to newline-separated text for the textarea
$specs_text = "";
if (!empty($product['list_description'])) {
    $specs_array = json_decode($product['list_description'], true);
    if (is_array($specs_array)) {
        $specs_text = implode("\n", $specs_array);
    }
}

if (!$product) {
    echo '<div class="p-4 bg-red-100 border border-red-500 text-red-800 font-bold">ERROR: ASSET NOT FOUND IN MATRIX.</div>';
    exit;
}

// 4. Fetch Categories for the dropdown
$categories = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC")->fetchAll();
?>

<div class="flex justify-between items-end mb-8">
    <h1 class="text-4xl font-robot font-bold text-slate-900">RECONFIGURE ASSET.</h1>
    <a href="?page=products" class="text-cyan-600 hover:text-cyan-400 font-bold tracking-widest text-xs uppercase underline">← Back to Database</a>
</div>

<div class="sci-fi-card p-8 bg-white/80 max-w-4xl">
    <form method="POST" action="" enctype="multipart/form-data" class="space-y-6">
        
        <div class="grid grid-cols-2 gap-6">
            <div>
                <label class="block text-[10px] font-bold text-cyan-700 tracking-widest mb-2">CATEGORY DIRECTORY</label>
                <select name="category_id" required class="input-cyber w-full p-3 text-sm">
                    <?php foreach($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= ($cat['id'] == $product['category_id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-bold text-cyan-700 tracking-widest mb-2">SKU (UNIQUE ID)</label>
                <input type="text" name="sku" required value="<?= htmlspecialchars($product['sku']) ?>" class="input-cyber w-full p-3 text-sm">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-6">
            <div>
                <label class="block text-[10px] font-bold text-cyan-700 tracking-widest mb-2">DESIGNATION (NAME)</label>
                <input type="text" id="asset_name" name="name" required value="<?= htmlspecialchars($product['name']) ?>" class="input-cyber w-full p-3 text-sm">
            </div>
            <div>
                <label class="block text-[10px] font-bold text-cyan-700 tracking-widest mb-2">URL SLUG (SEO)</label>
                <input type="text" id="asset_slug" name="slug" required value="<?= htmlspecialchars($product['slug']) ?>" class="input-cyber w-full p-3 text-sm bg-slate-50">
            </div>
        </div>

        <div class="grid grid-cols-4 gap-6">
            <div>
                <label class="block text-[10px] font-bold text-cyan-700 tracking-widest mb-2">MATH PRICE ($)</label>
                <input type="number" step="0.01" name="price_numeric" value="<?= htmlspecialchars($product['price_numeric']) ?>" class="input-cyber w-full p-3 text-sm">
            </div>
            <div>
                <label class="block text-[10px] font-bold text-cyan-700 tracking-widest mb-2">DISPLAY PRICE</label>
                <input type="text" name="price_display" required value="<?= htmlspecialchars($product['price_display']) ?>" class="input-cyber w-full p-3 text-sm">
            </div>
            <div>
                <label class="block text-[10px] font-bold text-cyan-700 tracking-widest mb-2">HUD ICON</label>
                <input type="text" name="icon" value="<?= htmlspecialchars($product['icon']) ?>" class="input-cyber w-full p-3 text-sm">
            </div>
            <div>
                <label class="block text-[10px] font-bold text-cyan-700 tracking-widest mb-2">SYS STATUS</label>
                <select name="status" class="input-cyber w-full p-3 text-sm">
                    <?php
                    $statuses = ['Active', 'Maintenance', 'In Stock', 'Low Stock', 'Out of Stock', 'Hidden'];
                    foreach($statuses as $stat) {
                        $selected = ($stat == $product['status']) ? 'selected' : '';
                        echo "<option value=\"$stat\" $selected>$stat</option>";
                    }
                    ?>
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6">
            <div>
                <label class="block text-[10px] font-bold text-cyan-700 tracking-widest mb-2">TECHNICAL SPECS (ONE ITEM PER LINE)</label>
                <textarea name="list_description" class="input-cyber w-full p-3 text-sm h-32"><?= htmlspecialchars($specs_text) ?></textarea>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6">
            <div>
                <label class="block text-[10px] font-bold text-cyan-700 tracking-widest mb-2">VISUAL ASSET (IMAGE)</label>
                <div class="flex items-center gap-6 p-4 border border-slate-200 bg-white/50">
                    <div class="w-20 h-20 bg-slate-100 border border-slate-300 flex-shrink-0 overflow-hidden">
                        <?php if($product['image']): ?>
                            <img src="../uploads/<?= $product['image'] ?>" class="w-full h-full object-cover">
                        <?php else: ?>
                            <div class="flex items-center justify-center h-full text-[10px] text-slate-400">NO_IMG</div>
                        <?php endif; ?>
                    </div>
                    <div class="flex-1">
                        <input type="file" name="image" accept="image/*" class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:border-0 file:text-xs file:font-bold file:bg-cyan-50 file:text-cyan-700 hover:file:bg-cyan-100">
                        <p class="text-[9px] text-slate-400 mt-2 uppercase tracking-widest">Leave empty to keep existing image</p>
                    </div>
                </div>
            </div>
        </div>

        <div>
            <label class="block text-[10px] font-bold text-cyan-700 tracking-widest mb-2">SHORT DESCRIPTION</label>
            <textarea name="short_description" required class="input-cyber w-full p-3 text-sm h-20"><?= htmlspecialchars($product['short_description']) ?></textarea>
        </div>

        <div class="flex justify-end pt-6 border-t border-cyan-100 gap-4">
            <a href="?page=products" class="px-8 py-4 text-sm font-bold border border-slate-300 text-slate-500 hover:bg-slate-100 transition-colors uppercase tracking-widest">CANCEL</a>
            <button type="submit" name="edit_product" class="btn-cyber btn-cyber-solid px-10 py-4 text-sm font-bold uppercase tracking-widest">
                COMMIT OVERRIDE
            </button>
        </div>
    </form>
</div>

<script>
document.getElementById('asset_name').addEventListener('input', function() {
    let name = this.value;
    let slug = name.toLowerCase()
                   .trim()
                   .replace(/[^\w\s-]/g, '')
                   .replace(/[\s_-]+/g, '-')
                   .replace(/^-+|-+$/g, '');
    document.getElementById('asset_slug').value = slug;
});
</script>