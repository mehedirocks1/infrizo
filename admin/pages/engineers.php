<?php
// Handle Delete
if (isset($_GET['delete'])) {
    $id = filter_var($_GET['delete'], FILTER_VALIDATE_INT);
    if ($id) {
        $pdo->prepare("DELETE FROM engineers WHERE id = ?")->execute([$id]);
    }
    header("Location: ?page=engineers");
    exit;
}

$stmt = $pdo->query("SELECT * FROM engineers ORDER BY id DESC");
$engineers = $stmt->fetchAll();
?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-3xl font-robot font-bold text-slate-800">Manage Engineers (Units)</h1>
    <a href="?page=engineer_form" class="bg-cyan-600 hover:bg-cyan-700 text-white px-4 py-2 text-xs font-bold uppercase tracking-widest transition-colors">
        + Add New Unit
    </a>
</div>

<div class="bg-white shadow-lg overflow-x-auto">
    <table class="w-full text-sm text-left text-slate-500">
        <thead class="text-xs text-slate-700 uppercase bg-slate-50">
            <tr>
                <th class="px-6 py-3">Unit ID</th>
                <th class="px-6 py-3">Operative Name & Role</th>
                <th class="px-6 py-3">Class</th>
                <th class="px-6 py-3">Hourly Rate</th>
                <th class="px-6 py-3">Status</th>
                <th class="px-6 py-3">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($engineers)): ?>
                <tr><td colspan="6" class="text-center p-8 text-slate-400 font-mono">-- No engineers deployed to the Matrix yet --</td></tr>
            <?php endif; ?>
            <?php foreach ($engineers as $eng): ?>
                <tr class="bg-white border-b hover:bg-slate-50">
                    <td class="px-6 py-4 font-mono font-bold text-cyan-600"><?= htmlspecialchars($eng['unit_id']) ?></td>
                    <td class="px-6 py-4">
                        <div class="font-bold text-slate-900"><?= htmlspecialchars($eng['name']) ?></div>
                        <div class="text-xs text-slate-500"><?= htmlspecialchars($eng['role']) ?></div>
                    </td>
                    <td class="px-6 py-4 uppercase tracking-widest text-xs"><?= htmlspecialchars($eng['unit_class']) ?></td>
                    <td class="px-6 py-4 font-mono">$<?= number_format($eng['hourly_rate'], 2) ?>/HR</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs font-bold uppercase <?= $eng['status'] === 'Active' ? 'bg-green-100 text-green-800' : 'bg-slate-100 text-slate-800' ?>">
                            <?= $eng['status'] ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 flex gap-3">
                        <a href="?page=engineer_form&id=<?= $eng['id'] ?>" class="text-cyan-600 hover:text-cyan-800 text-xs font-bold uppercase tracking-widest">Edit</a>
                        <a href="?page=engineers&delete=<?= $eng['id'] ?>" onclick="return confirm('Delete this unit?')" class="text-red-600 hover:text-red-800 text-xs font-bold uppercase tracking-widest">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>