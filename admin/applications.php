<?php
require_once 'includes/auth.php';

$filter = $_GET['filter'] ?? 'Pending';
$allowed_filters = ['Pending', 'Approved', 'Rejected', 'All'];
if (!in_array($filter, $allowed_filters)) {
    $filter = 'Pending';
}

$where_clause = "WHERE status = :status";
if ($filter === 'All') {
    $where_clause = "";
}

$stmt = $pdo->prepare("SELECT * FROM freelancer_applications $where_clause ORDER BY created_at DESC");
if ($filter !== 'All') {
    $stmt->bindParam(':status', $filter, PDO::PARAM_STR);
}
$stmt->execute();
$applications = $stmt->fetchAll();

require_once 'includes/header.php';
?>

<h1 class="text-3xl font-robot font-bold text-slate-800 mb-6">Freelancer Applications</h1>

<div class="flex items-center gap-4 mb-6 border-b border-slate-300 pb-4">
    <span class="text-sm font-bold text-slate-600">Filter by status:</span>
    <?php foreach($allowed_filters as $f): ?>
        <a href="?filter=<?= $f ?>" class="px-4 py-1 text-xs font-bold uppercase tracking-widest <?= $filter === $f ? 'bg-cyan-600 text-white' : 'bg-white border border-slate-300 text-slate-600 hover:bg-slate-200' ?> transition-colors">
            <?= $f ?>
        </a>
    <?php endforeach; ?>
</div>

<div class="bg-white shadow-lg overflow-x-auto">
    <table class="w-full text-sm text-left text-slate-500">
        <thead class="text-xs text-slate-700 uppercase bg-slate-50">
            <tr>
                <th scope="col" class="px-6 py-3">Applicant</th>
                <th scope="col" class="px-6 py-3">Contact</th>
                <th scope="col" class="px-6 py-3">Submitted</th>
                <th scope="col" class="px-6 py-3">Status</th>
                <th scope="col" class="px-6 py-3">Documents</th>
                <th scope="col" class="px-6 py-3">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($applications)): ?>
                <tr><td colspan="6" class="text-center p-8 text-slate-400 font-mono">-- No applications found for this filter --</td></tr>
            <?php endif; ?>
            <?php foreach ($applications as $app): ?>
                <tr class="bg-white border-b hover:bg-slate-50">
                    <td class="px-6 py-4 font-bold text-slate-900"><?= htmlspecialchars($app['name']) ?></td>
                    <td class="px-6 py-4"><?= htmlspecialchars($app['email']) ?><br><?= htmlspecialchars($app['phone']) ?></td>
                    <td class="px-6 py-4 font-mono text-xs"><?= date('d M Y, H:i', strtotime($app['created_at'])) ?></td>
                    <td class="px-6 py-4"><span class="px-2 py-1 text-xs font-bold uppercase <?= $app['status'] === 'Approved' ? 'bg-green-100 text-green-800' : ($app['status'] === 'Rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') ?>"><?= $app['status'] ?></span></td>
                    <td class="px-6 py-4">
                        <a href="../uploads/photos/<?= $app['photo_path'] ?>" target="_blank" class="text-cyan-600 hover:underline text-xs font-bold">Photo</a> |
                        <a href="../uploads/cvs/<?= $app['cv_path'] ?>" target="_blank" class="text-cyan-600 hover:underline text-xs font-bold">CV</a>
                    </td>
                    <td class="px-6 py-4">
                        <?php if ($app['status'] === 'Pending'): ?>
                        <form method="POST" action="application_action.php" class="flex gap-2">
                            <input type="hidden" name="application_id" value="<?= $app['id'] ?>">
                            <button type="submit" name="action" value="Approved" class="px-3 py-1 bg-green-600 text-white text-xs font-bold hover:bg-green-700">Approve</button>
                            <button type="submit" name="action" value="Rejected" class="px-3 py-1 bg-red-600 text-white text-xs font-bold hover:bg-red-700">Reject</button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once 'includes/footer.php'; ?>