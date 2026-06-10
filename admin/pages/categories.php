<?php
// Handle form submission to add a category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        die('CSRF token validation failed.');
    }
    $name = $_POST['name'];
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $_POST['slug'])));
    $desc = $_POST['description'];

    $stmt = $pdo->prepare("INSERT INTO categories (name, slug, description) VALUES (?, ?, ?)");
    try {
        $stmt->execute([$name, $slug, $desc]);
        echo '<div class="p-3 mb-6 bg-cyan-100 border border-cyan-500 text-cyan-700 text-xs font-bold tracking-widest uppercase">Success: Directory added to Matrix.</div>';
    } catch (PDOException $e) {
        echo '<div class="p-3 mb-6 bg-red-100 border border-red-500 text-red-700 text-xs font-bold tracking-widest uppercase">Error: Protocol failure. Slug must be unique.</div>';
    }
}

// Fetch all categories
$categories = $pdo->query("SELECT * FROM categories ORDER BY id DESC")->fetchAll();
?>

<div class="flex justify-between items-end mb-8">
    <h1 class="text-4xl font-robot font-bold text-slate-900 uppercase">Manage Directories.</h1>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Add Category Form -->
    <div class="sci-fi-card p-6 bg-white/80 h-fit lg:col-span-1">
        <h3 class="font-robot font-bold text-xl text-slate-900 mb-4 border-b border-cyan-200 pb-2 uppercase">Add New Entry</h3>
        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
            <div class="mb-4">
                <label class="block text-[10px] font-bold text-cyan-700 tracking-widest mb-1">NAME</label>
                <input type="text" name="name" id="category_name" required class="input-cyber w-full p-2 text-sm" placeholder="e.g. Network Gear">
            </div>
            <div class="mb-4">
                <label class="block text-[10px] font-bold text-cyan-700 tracking-widest mb-1">URL SLUG (AUTO-GEN)</label>
                <input type="text" name="slug" id="category_slug" required class="input-cyber w-full p-2 text-sm bg-slate-50" placeholder="network-gear">
            </div>
            <div class="mb-6">
                <label class="block text-[10px] font-bold text-cyan-700 tracking-widest mb-1">DESCRIPTION</label>
                <textarea name="description" class="input-cyber w-full p-2 text-sm h-20"></textarea>
            </div>
            <button type="submit" name="add_category" class="btn-cyber btn-cyber-solid w-full py-3 text-xs uppercase tracking-widest font-bold">Create Directory</button>
        </form>
    </div>

    <!-- Category List -->
    <div class="sci-fi-card p-6 bg-white/80 lg:col-span-2">
        <table class="w-full text-left admin-table">
            <thead>
                <tr class="border-b border-cyan-100">
                    <th class="py-3 px-4 text-[10px] font-bold text-cyan-700 tracking-widest uppercase">ID</th>
                    <th class="py-3 px-4 text-[10px] font-bold text-cyan-700 tracking-widest uppercase">Name</th>
                    <th class="py-3 px-4 text-[10px] font-bold text-cyan-700 tracking-widest uppercase">Slug</th>
                    <th class="py-3 px-4 text-[10px] font-bold text-cyan-700 tracking-widest uppercase text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="text-sm font-bold text-slate-600">
                <?php foreach($categories as $cat): ?>
                <tr class="border-b border-slate-100 hover:bg-cyan-50/30 transition-colors group">
                    <td class="py-4 px-4 text-cyan-700 font-mono text-xs"><?= $cat['id'] ?></td>
                    <td class="py-4 px-4 text-slate-900 font-robot uppercase tracking-tight"><?= htmlspecialchars($cat['name']) ?></td>
                    <td class="py-4 px-4 text-slate-400 font-normal text-xs">/<?= htmlspecialchars($cat['slug']) ?></td>
                    <td class="py-4 px-4 text-right">
                        <div class="flex justify-end gap-3">
                            <a href="?page=edit_category&id=<?= $cat['id'] ?>" class="text-[10px] text-cyan-600 hover:text-cyan-400 uppercase tracking-widest border border-cyan-200 px-2 py-1 bg-white">[ Edit ]</a>
                            <a href="?page=delete_category&id=<?= $cat['id'] ?>&token=<?= htmlspecialchars($_SESSION['csrf_token']) ?>" 
                               onclick="return confirm('CRITICAL WARNING: Purging this directory will delete all associated products in the matrix. Continue?');"
                               class="text-[10px] text-red-500 hover:text-red-700 uppercase tracking-widest border border-red-200 px-2 py-1 bg-white">[ Purge ]</a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.getElementById('category_name').addEventListener('input', function() {
    let name = this.value;
    let slug = name.toLowerCase()
                   .trim()
                   .replace(/[^\w\s-]/g, '')
                   .replace(/[\s_-]+/g, '-')
                   .replace(/^-+|-+$/g, '');
    document.getElementById('category_slug').value = slug;
});
</script>