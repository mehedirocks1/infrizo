<?php
require_once 'includes/config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// NOTE: Ensure you add your Admin authentication check here
// if (!isset($_SESSION['admin_logged_in'])) { ... }

$success = false;
$errors = [];

// Dynamically fetch the "Software" category ID
$stmt = $pdo->prepare("SELECT id FROM categories WHERE slug = 'software' LIMIT 1");
$stmt->execute();
$software_cat = $stmt->fetch();
$software_cat_id = $software_cat ? $software_cat['id'] : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_software'])) {
    if (!$software_cat_id) {
        $errors[] = "Software category does not exist in the database.";
    } else {
        $sku = trim($_POST['sku']);
        $name = trim($_POST['name']);
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
        $short_desc = trim($_POST['short_description']);
        $icon = trim($_POST['icon']);
        
        // Handle Optional Image Upload
        $image_path = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = "uploads/";
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
            
            $file_ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
            
            if (in_array($file_ext, $allowed)) {
                $image_path = uniqid('sw_') . '.' . $file_ext;
                move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $image_path);
            } else {
                $errors[] = "Invalid image format.";
            }
        }

        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO products 
                    (category_id, sku, name, slug, short_description, icon, image, price_display, status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, 'Custom Quote', 'Active')
                ");
                $stmt->execute([
                    $software_cat_id, $sku, $name, $slug, $short_desc, $icon, $image_path
                ]);
                $success = true;
            } catch (PDOException $e) {
                $errors[] = "Database error: " . $e->getMessage();
            }
        }
    }
}

require_once 'includes/header.php';
?>

<main class="pt-32 pb-20 px-6 max-w-3xl mx-auto min-h-screen">
    <div class="bg-white p-10 border border-slate-200 shadow-2xl">
        <div class="border-b border-slate-200 pb-4 mb-8">
            <h1 class="text-3xl font-robot font-bold text-slate-900 uppercase">Add Software Service</h1>
            <p class="text-slate-500 text-xs tracking-widest uppercase font-bold mt-2">Inject new algorithmic solutions into the database.</p>
        </div>

        <?php if ($success): ?>
            <div class="mb-8 p-4 border border-cyan-500 bg-cyan-50 text-cyan-700 text-xs font-mono uppercase tracking-widest">
                [ SUCCESS ] Software module successfully registered in the matrix.
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="mb-8 p-4 border border-red-500 bg-red-500/10 text-red-500 text-xs font-mono uppercase tracking-widest">
                <?php foreach ($errors as $error): ?>
                    <p>[ ERROR ] <?= htmlspecialchars($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="space-y-6">
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <label class="block text-[9px] font-bold text-slate-500 tracking-widest mb-2 uppercase">Service ID / SKU (e.g., SW-01)</label>
                    <input type="text" name="sku" required class="w-full p-3 text-sm border border-slate-200 bg-slate-50 focus:border-cyan-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-[9px] font-bold text-slate-500 tracking-widest mb-2 uppercase">Software Name</label>
                    <input type="text" name="name" required class="w-full p-3 text-sm border border-slate-200 bg-slate-50 focus:border-cyan-500 focus:outline-none">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <label class="block text-[9px] font-bold text-slate-500 tracking-widest mb-2 uppercase">Symbol / Icon (e.g., ⟨/⟩ or ◈)</label>
                    <input type="text" name="icon" class="w-full p-3 text-sm border border-slate-200 bg-slate-50 focus:border-cyan-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-[9px] font-bold text-slate-500 tracking-widest mb-2 uppercase">Cover Image (Optional)</label>
                    <input type="file" name="image" class="w-full p-2 text-sm border border-slate-200 bg-slate-50 focus:border-cyan-500 focus:outline-none">
                </div>
            </div>
            <div>
                <label class="block text-[9px] font-bold text-slate-500 tracking-widest mb-2 uppercase">Short Description</label>
                <textarea name="short_description" required rows="3" class="w-full p-3 text-sm border border-slate-200 bg-slate-50 focus:border-cyan-500 focus:outline-none resize-none"></textarea>
            </div>
            <button type="submit" name="add_software" class="w-full bg-slate-900 text-white text-xs font-bold tracking-widest uppercase py-4 hover:bg-cyan-600 transition-colors">ADD_SOFTWARE_ASSET</button>
        </form>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>