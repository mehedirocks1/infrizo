<?php
// Fetch categories for the dropdown
$categories = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        die('CSRF token validation failed.');
    }
    
    // Basic Field Data
    $cat_id     = $_POST['category_id'];
    $sku        = $_POST['sku'];
    $name       = $_POST['name'];
    $slug       = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $_POST['slug'])));
    $price_num  = $_POST['price_numeric'] ?: 0.00;
    $price_disp = $_POST['price_display'];
    $icon       = $_POST['icon'];
    $short_desc = $_POST['short_description'];

    // --- 1. HANDLE JSON LIST DESCRIPTION ---
    // Converts line-by-line input into a JSON array for technical specs
    $list_input = explode("\n", str_replace("\r", "", $_POST['list_description']));
    $list_json  = json_encode(array_filter(array_map('trim', $list_input)));

    // --- 2. IMAGE UPLOAD LOGIC ---
    $image_name = null; 
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $upload_dir = '../uploads/';
        
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        if (!in_array(strtolower($file_ext), ['jpg', 'jpeg', 'png', 'webp', 'svg'])) {
            echo '<div class="p-4 mb-6 bg-red-100 border border-red-500 text-red-800 font-bold">WARNING: Invalid image type.</div>';
            $image_name = null;
        } else {
            $image_name = $slug . '-' . time() . '.' . $file_ext;
            $target_path = $upload_dir . $image_name;

            if (!move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
                echo '<div class="p-4 mb-6 bg-yellow-100 border border-yellow-500 text-yellow-800 font-bold">WARNING: Image upload failed.</div>';
                $image_name = null;
            }
        }
    }

    // --- 3. SECURE DATABASE INSERTION ---
    $stmt = $pdo->prepare("INSERT INTO products 
        (category_id, sku, name, slug, price_numeric, price_display, icon, short_description, list_description, image) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    try {
        $stmt->execute([$cat_id, $sku, $name, $slug, $price_num, $price_disp, $icon, $short_desc, $list_json, $image_name]);
        echo '<div class="p-4 mb-6 bg-cyan-100 border border-cyan-500 text-cyan-800 font-bold">ASSET DEPLOYED SUCCESSFULLY TO MATRIX.</div>';
    } catch (PDOException $e) {
        // Debug error if needed: echo $e->getMessage();
        echo '<div class="p-4 mb-6 bg-red-100 border border-red-500 text-red-800 font-bold">ERROR: SKU or SLUG conflict detected.</div>';
    }
}
?>

<h1 class="text-4xl font-robot font-bold text-slate-900 mb-8">DEPLOY NEW ASSET.</h1>

<div class="sci-fi-card p-8 bg-white/80 max-w-4xl">
    <form method="POST" action="" enctype="multipart/form-data" class="space-y-6">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
        
        <div class="grid grid-cols-2 gap-6">
            <div>
                <label class="block text-[10px] font-bold text-cyan-700 tracking-widest mb-2">CATEGORY DIRECTORY</label>
                <select name="category_id" required class="input-cyber w-full p-3 text-sm">
                    <option value="" disabled selected>Select Category</option>
                    <?php foreach($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-bold text-cyan-700 tracking-widest mb-2">SKU (UNIQUE ID)</label>
                <input type="text" name="sku" required class="input-cyber w-full p-3 text-sm" placeholder="e.g. INF-99">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-6">
            <div>
                <label class="block text-[10px] font-bold text-cyan-700 tracking-widest mb-2">DESIGNATION (NAME)</label>
                <input type="text" id="asset_name" name="name" required class="input-cyber w-full p-3 text-sm" placeholder="e.g. Core Engine">
            </div>
            <div>
                <label class="block text-[10px] font-bold text-cyan-700 tracking-widest mb-2">URL SLUG (AUTO-GEN)</label>
                <input type="text" id="asset_slug" name="slug" required class="input-cyber w-full p-3 text-sm bg-slate-50" placeholder="core-engine">
            </div>
        </div>

        <div class="grid grid-cols-3 gap-6">
            <div>
                <label class="block text-[10px] font-bold text-cyan-700 tracking-widest mb-2">MATH PRICE ($)</label>
                <input type="number" step="0.01" name="price_numeric" class="input-cyber w-full p-3 text-sm" placeholder="0.00">
            </div>
            <div>
                <label class="block text-[10px] font-bold text-cyan-700 tracking-widest mb-2">DISPLAY PRICE</label>
                <input type="text" name="price_display" required class="input-cyber w-full p-3 text-sm" placeholder="e.g. $49.00 or Custom">
            </div>
            <div>
                <label class="block text-[10px] font-bold text-cyan-700 tracking-widest mb-2">HUD ICON (SYMBOL)</label>
                <input type="text" name="icon" class="input-cyber w-full p-3 text-sm" placeholder="e.g. ◈ or ⚡">
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6">
            <div>
                <label class="block text-[10px] font-bold text-cyan-700 tracking-widest mb-2">TECHNICAL SPECS (ONE ITEM PER LINE)</label>
                <textarea name="list_description" class="input-cyber w-full p-3 text-sm h-32" placeholder="Item 1: Detail&#10;Item 2: Detail&#10;Item 3: Detail"></textarea>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6">
            <div>
                <label class="block text-[10px] font-bold text-cyan-700 tracking-widest mb-2">VISUAL ASSET (IMAGE)</label>
                <div class="relative border-2 border-dashed border-cyan-200 p-4 hover:border-cyan-500 transition-colors bg-white/50">
                    <input type="file" name="image" accept="image/*" class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:border-0 file:text-xs file:font-bold file:bg-cyan-50 file:text-cyan-700 hover:file:bg-cyan-100">
                </div>
            </div>
        </div>

        <div>
            <label class="block text-[10px] font-bold text-cyan-700 tracking-widest mb-2">SHORT DESCRIPTION (SUMMARY)</label>
            <textarea name="short_description" required class="input-cyber w-full p-3 text-sm h-20" placeholder="Enter brief technical summary..."></textarea>
        </div>

        <div class="flex justify-end pt-6 border-t border-cyan-100">
            <button type="submit" name="add_product" class="btn-cyber btn-cyber-solid px-10 py-4 text-sm font-bold uppercase tracking-widest">
                COMPILE & DEPLOY ASSET
            </button>
        </div>
    </form>
</div>

<script>
// Real-time Slug Generation
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