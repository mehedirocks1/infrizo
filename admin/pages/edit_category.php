<?php
$id = $_GET['id'] ?? null;
$stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
$stmt->execute([$id]);
$cat = $stmt->fetch();

if (!$cat) {
    echo "Category not found.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_category'])) {
    $name = $_POST['name'];
    $slug = $_POST['slug'];
    $desc = $_POST['description'];

    $update = $pdo->prepare("UPDATE categories SET name = ?, slug = ?, description = ? WHERE id = ?");
    $update->execute([$name, $slug, $desc, $id]);
    
    echo '<script>window.location.href="?page=categories";</script>';
    exit;
}
?>

<div class="mb-8">
    <h1 class="text-4xl font-robot font-bold text-slate-900 uppercase">Update Directory.</h1>
    <p class="text-xs text-slate-500 tracking-widest mt-2 uppercase">Modifying parameters for Entry ID: <?= $id ?></p>
</div>

<div class="sci-fi-card p-8 bg-white/80 max-w-2xl">
    <form method="POST" action="">
        <div class="mb-6">
            <label class="block text-[10px] font-bold text-cyan-700 tracking-widest mb-1">DESIGNATION NAME</label>
            <input type="text" name="name" value="<?= htmlspecialchars($cat['name']) ?>" required class="input-cyber w-full p-3 text-sm">
        </div>
        <div class="mb-6">
            <label class="block text-[10px] font-bold text-cyan-700 tracking-widest mb-1">URL SLUG</label>
            <input type="text" name="slug" value="<?= htmlspecialchars($cat['slug']) ?>" required class="input-cyber w-full p-3 text-sm bg-slate-50">
        </div>
        <div class="mb-8">
            <label class="block text-[10px] font-bold text-cyan-700 tracking-widest mb-1">DESCRIPTION DATA</label>
            <textarea name="description" class="input-cyber w-full p-3 text-sm h-32"><?= htmlspecialchars($cat['description']) ?></textarea>
        </div>
        
        <div class="flex gap-4">
            <a href="?page=categories" class="flex-1 px-6 py-3 border border-slate-300 text-center text-xs font-bold text-slate-500 uppercase tracking-widest hover:bg-slate-100 transition-colors">Abort_Change</a>
            <button type="submit" name="update_category" class="flex-1 btn-cyber btn-cyber-solid py-3 text-xs uppercase tracking-widest font-bold">Commit_Override</button>
        </div>
    </form>
</div>