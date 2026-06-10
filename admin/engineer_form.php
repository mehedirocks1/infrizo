<?php
require_once 'auth.php';

$id = $_GET['id'] ?? null;
$engineer = [
    'unit_id' => 'UNIT-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT),
    'role' => '',
    'hourly_rate' => '0.00',
    'unit_class' => '',
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
    $role = trim($_POST['role'] ?? '');
    $hourly_rate = floatval($_POST['hourly_rate'] ?? 0);
    $unit_class = trim($_POST['unit_class'] ?? '');
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
            $target_path = '../uploads/photos/' . $new_name;
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

    if (!$error && ($unit_id && $role && $unit_class)) {
        try {
            if ($id) {
                $stmt = $pdo->prepare("UPDATE engineers SET unit_id=?, role=?, hourly_rate=?, unit_class=?, photo_path=?, status=? WHERE id=?");
                $stmt->execute([$unit_id, $role, $hourly_rate, $unit_class, $photo_path, $status, $id]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO engineers (unit_id, role, hourly_rate, unit_class, photo_path, status) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$unit_id, $role, $hourly_rate, $unit_class, $photo_path, $status]);
            }
            header("Location: engineers.php");
            exit;
        } catch (PDOException $e) {
            $error = 'Database error: Ensure the Unit ID is unique.';
        }
    }
}

require_once 'header.php';
?>

<link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" rel="stylesheet">
<div class="max-w-2xl mx-auto bg-white p-8 shadow-lg">
    <div class="flex justify-between items-center mb-6 border-b border-slate-200 pb-4">
        <h2 class="text-2xl font-robot font-bold text-slate-800"><?= $id ? 'Edit' : 'Add New' ?> Unit Engineer</h2>
        <a href="engineers.php" class="text-xs font-bold text-slate-500 uppercase tracking-widest hover:text-slate-900">&larr; Back to List</a>
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

        <div>
            <label class="block text-xs font-bold text-slate-500 mb-2 uppercase tracking-widest">Role / Specialization</label>
            <input type="text" name="role" required value="<?= htmlspecialchars($engineer['role']) ?>" class="input-cyber w-full p-3 bg-slate-50" placeholder="e.g. Network Eng.">
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
            <label class="block text-xs font-bold text-slate-500 mb-2 uppercase tracking-widest">Unit Photo / Avatar (Optional)</label>
            <?php if (!empty($engineer['photo_path'])): ?>
                <div class="mb-4">
                    <img src="../uploads/photos/<?= htmlspecialchars($engineer['photo_path']) ?>" class="h-20 w-20 object-cover rounded-full border-2 border-cyan-500 shadow-lg shadow-cyan-500/20">
                </div>
            <?php endif; ?>
            <input type="file" name="photo" class="input-cyber file-input w-full text-sm">
        </div>

        <button type="submit" class="w-full btn-cyber btn-cyber-solid py-4 text-sm mt-4">
            <?= $id ? 'UPDATE_UNIT' : 'DEPLOY_NEW_UNIT' ?>
        </button>
    </form>
</div>

<?php require_once 'footer.php'; ?>