<?php
$id = $_GET['id'] ?? null;
$engineer = [
    'unit_id' => 'UNIT-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT),
    'name' => '',
    'role' => '',
    'hourly_rate' => '0.00',
    'unit_class' => '',
    'details' => '',
    'photo_path' => '',
    'status' => 'Active'
];

if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM engineers WHERE id = ?");
    $stmt->execute([$id]);
    $engineer = $stmt->fetch() ?: $engineer;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $unit_id = trim($_POST['unit_id'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $role = trim($_POST['role'] ?? '');
    $hourly_rate = floatval($_POST['hourly_rate'] ?? 0);
    $unit_class = trim($_POST['unit_class'] ?? '');
    $details = trim($_POST['details'] ?? '');
    $status = $_POST['status'] ?? 'Active';
    $photo_path = $engineer['photo_path'];

    // Handle Cropped Image Upload (Base64 from Cropper.js)
    if (!empty($_POST['cropped_image'])) {
        $base64_image = $_POST['cropped_image'];
        list($type, $data) = explode(';', $base64_image);
        list(, $data)      = explode(',', $data);
        $data = base64_decode($data);

        if ($data) {
            $new_name = 'unit_' . uniqid() . '.webp';
            $upload_dir = '../uploads/photos/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            $target_path = $upload_dir . $new_name;
            $image_res = imagecreatefromstring($data);
            if ($image_res) {
                if (imagewebp($image_res, $target_path, 85)) { // 85% quality
                    $photo_path = $new_name;
                    imagedestroy($image_res);
                } else {
                    $error = 'Failed to save optimized image.';
                }
            } else {
                $error = 'Invalid image data provided by cropper.';
            }
        } else {
            $error = 'Failed to decode cropped image data.';
        }
    }

    if (!$error && ($unit_id && $name && $role && $unit_class)) {
        try {
            if ($id) {
                $stmt = $pdo->prepare("UPDATE engineers SET unit_id=?, name=?, role=?, hourly_rate=?, unit_class=?, details=?, photo_path=?, status=? WHERE id=?");
                $stmt->execute([$unit_id, $name, $role, $hourly_rate, $unit_class, $details, $photo_path, $status, $id]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO engineers (unit_id, name, role, hourly_rate, unit_class, details, photo_path, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$unit_id, $name, $role, $hourly_rate, $unit_class, $details, $photo_path, $status]);
            }
            header("Location: ?page=engineers");
            exit;
        } catch (PDOException $e) {
            $error = 'Database error: Ensure the Unit ID is unique.';
        }
    }
}
?>

<link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" rel="stylesheet">
<div class="max-w-2xl mx-auto bg-white p-8 shadow-lg">
    <div class="flex justify-between items-center mb-6 border-b border-slate-200 pb-4">
        <h2 class="text-2xl font-robot font-bold text-slate-800"><?= $id ? 'Edit' : 'Add New' ?> Unit Engineer</h2>
        <a href="?page=engineers" class="text-xs font-bold text-slate-500 uppercase tracking-widest hover:text-slate-900">&larr; Back to List</a>
    </div>

    <?php if ($error): ?>
        <div class="p-3 mb-6 bg-red-500/10 border border-red-500 text-red-600 text-xs font-mono uppercase tracking-widest">
            [ERROR] <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="" enctype="multipart/form-data" class="space-y-6">
        <div class="grid grid-cols-2 gap-6">
            <div>
                <label class="block text-xs font-bold text-slate-500 mb-2 uppercase tracking-widest">Unit ID</label>
                <input type="text" name="unit_id" required value="<?= htmlspecialchars($engineer['unit_id']) ?>" class="input-cyber w-full p-3 bg-slate-50" placeholder="e.g. UNIT-01">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-500 mb-2 uppercase tracking-widest">Status</label>
                <select name="status" class="input-cyber w-full p-3 bg-slate-50 appearance-none">
                    <option value="Active" <?= $engineer['status'] === 'Active' ? 'selected' : '' ?>>Active</option>
                    <option value="Hidden" <?= $engineer['status'] === 'Hidden' ? 'selected' : '' ?>>Hidden</option>
                </select>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-6">
            <div>
                <label class="block text-xs font-bold text-slate-500 mb-2 uppercase tracking-widest">Subject Name</label>
                <input type="text" name="name" required value="<?= htmlspecialchars($engineer['name']) ?>" class="input-cyber w-full p-3 bg-slate-50" placeholder="e.g. John Doe">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-500 mb-2 uppercase tracking-widest">Role / Specialization</label>
                <input type="text" name="role" required value="<?= htmlspecialchars($engineer['role']) ?>" class="input-cyber w-full p-3 bg-slate-50" placeholder="e.g. Network Eng.">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-6">
            <div>
                <label class="block text-xs font-bold text-slate-500 mb-2 uppercase tracking-widest">Hourly Rate ($)</label>
                <input type="number" step="0.01" name="hourly_rate" required value="<?= htmlspecialchars($engineer['hourly_rate']) ?>" class="input-cyber w-full p-3 bg-slate-50" placeholder="0.00">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-500 mb-2 uppercase tracking-widest">Unit Class</label>
                <input type="text" name="unit_class" required value="<?= htmlspecialchars($engineer['unit_class']) ?>" class="input-cyber w-full p-3 bg-slate-50" placeholder="e.g. Alpha, Shield, Dev">
            </div>
        </div>
        
        <div>
            <label class="block text-xs font-bold text-slate-500 mb-2 uppercase tracking-widest">Operational Details / Bio (Optional)</label>
            <textarea name="details" rows="3" class="input-cyber w-full p-3 bg-slate-50 resize-none" placeholder="Provide background intel..."><?= htmlspecialchars($engineer['details']) ?></textarea>
        </div>

        <div>
            <label class="block text-xs font-bold text-slate-500 mb-2 uppercase tracking-widest">Unit Photo / Avatar</label>
            <div id="preview-container" class="mb-4">
                <?php if (!empty($engineer['photo_path'])): ?>
                    <img id="current-preview" src="../uploads/photos/<?= htmlspecialchars($engineer['photo_path']) ?>" class="h-20 w-20 object-cover rounded-full border-2 border-cyan-500 shadow-lg shadow-cyan-500/20">
                <?php else: ?>
                     <div class="h-20 w-20 rounded-full border-2 border-dashed border-slate-300 flex items-center justify-center text-slate-400 text-xs font-bold text-center">PREVIEW</div>
                <?php endif; ?>
            </div>
            <input type="file" name="photo" id="photo-input" class="input-cyber file-input w-full text-sm" accept="image/*">
            <input type="hidden" name="cropped_image" id="cropped-image-input">
            <div class="text-xs text-slate-500 mt-2">Select an image to open the cropper. New uploads will be optimized to WEBP format.</div>
        </div>

        <button type="submit" class="w-full btn-cyber btn-cyber-solid py-4 text-sm mt-4">
            <?= $id ? 'UPDATE_UNIT' : 'DEPLOY_NEW_UNIT' ?>
        </button>
    </form>
</div>

<!-- Cropper Modal -->
<div id="cropper-modal" class="fixed inset-0 bg-black/80 z-50 hidden items-center justify-center p-4">
    <div class="bg-white p-6 rounded-lg max-w-2xl w-full">
        <h3 class="text-xl font-bold mb-4">Crop & Optimize Image</h3>
        <div>
            <img id="cropper-image" src="" style="max-height: 50vh;">
        </div>
        <div class="mt-4 flex justify-end gap-4">
            <button type="button" id="cancel-crop" class="px-6 py-2 bg-slate-600 text-white text-xs font-bold uppercase tracking-widest hover:bg-slate-700">Cancel</button>
            <button type="button" id="confirm-crop" class="px-6 py-2 bg-cyan-600 text-white text-xs font-bold uppercase tracking-widest hover:bg-cyan-700">Crop & Save</button>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
<script>
    const modal = document.getElementById('cropper-modal');
    const image = document.getElementById('cropper-image');
    const fileInput = document.getElementById('photo-input');
    const hiddenInput = document.getElementById('cropped-image-input');
    const form = document.querySelector('form');
    let cropper;

    fileInput.addEventListener('change', (e) => {
        const files = e.target.files;
        if (files && files.length > 0) {
            const reader = new FileReader();
            reader.onload = (event) => {
                image.src = event.target.result;
                modal.style.display = 'flex';
                cropper = new Cropper(image, { aspectRatio: 1, viewMode: 1, background: false });
            };
            reader.readAsDataURL(files[0]);
        }
    });

    document.getElementById('cancel-crop').addEventListener('click', () => {
        modal.style.display = 'none';
        if (cropper) cropper.destroy();
        fileInput.value = '';
    });

    document.getElementById('confirm-crop').addEventListener('click', () => {
        const canvas = cropper.getCroppedCanvas({ width: 512, height: 512, imageSmoothingQuality: 'high' });
        document.getElementById('preview-container').innerHTML = `<img id="current-preview" src="${canvas.toDataURL()}" class="h-20 w-20 object-cover rounded-full border-2 border-cyan-500 shadow-lg">`;
        hiddenInput.value = canvas.toDataURL('image/webp', 0.85);
        modal.style.display = 'none';
        cropper.destroy();
    });
</script>