<?php
require_once 'includes/config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// NOTE: Ensure you add your Admin authentication check here
// if (!isset($_SESSION['admin_logged_in'])) { ... }

$success = false;
$errors = [];

// Handle Status Update & File Upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_quote'])) {
    $quote_id = (int)$_POST['quote_id'];
    $new_status = $_POST['order_status'];

    $quote_file_path = null;
    $update_file_query = "";
    $params = [$new_status];

    // Handle File Upload
    if (isset($_FILES['quote_file']) && $_FILES['quote_file']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = "uploads/quotes/";
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        
        $file_ext = strtolower(pathinfo($_FILES['quote_file']['name'], PATHINFO_EXTENSION));
        if ($file_ext === 'pdf') {
            $quote_file_path = uniqid('quote_') . '.pdf';
            if (move_uploaded_file($_FILES['quote_file']['tmp_name'], $upload_dir . $quote_file_path)) {
                $update_file_query = ", quote_file_path = ?";
                $params[] = $quote_file_path;
            } else {
                $errors[] = "Failed to upload the file.";
            }
        } else {
            $errors[] = "Only PDF files are allowed for quotes.";
        }
    }

    $params[] = $quote_id;

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("UPDATE orders SET order_status = ? $update_file_query WHERE id = ? AND order_type = 'Quotation'");
            $stmt->execute($params);
            $success = true;
        } catch (PDOException $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
}

// Fetch Quotes
$stmt = $pdo->query("SELECT * FROM orders WHERE order_type = 'Quotation' ORDER BY created_at DESC");
$quotes = $stmt->fetchAll();

require_once 'includes/header.php';
?>

<main class="pt-32 pb-20 px-6 max-w-7xl mx-auto min-h-screen">
    <div class="mb-8 border-b border-slate-200 pb-4">
        <h1 class="text-3xl font-robot font-bold text-slate-900 uppercase">Admin Dashboard - Quotes Management</h1>
        <p class="text-slate-500 text-xs tracking-widest uppercase font-bold mt-2">Monitor and update generated system quotes.</p>
    </div>

    <?php if ($success): ?>
        <div class="mb-8 p-4 border border-cyan-500 bg-cyan-50 text-cyan-700 text-xs font-mono uppercase tracking-widest">
            [ SUCCESS ] Quote record updated successfully.
        </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="mb-8 p-4 border border-red-500 bg-red-500/10 text-red-500 text-xs font-mono uppercase tracking-widest">
            <?php foreach ($errors as $error): ?>
                <p>[ ERROR ] <?= htmlspecialchars($error) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="bg-white border border-slate-200 shadow-2xl overflow-x-auto">
        <table class="w-full text-left border-collapse min-w-[800px]">
            <thead>
                <tr class="bg-slate-900 text-white text-[10px] tracking-widest uppercase font-bold">
                    <th class="p-4 whitespace-nowrap">Reference</th>
                    <th class="p-4">Client Info</th>
                    <th class="p-4">Est. Value</th>
                    <th class="p-4 w-48">Status</th>
                    <th class="p-4 w-48">Attach PDF</th>
                    <th class="p-4 text-right">Action</th>
                </tr>
            </thead>
            <tbody class="text-sm">
                <?php if (empty($quotes)): ?>
                    <tr>
                        <td colspan="6" class="p-8 text-center text-slate-400 font-mono text-xs uppercase tracking-widest">
                            [ NO QUOTES FOUND IN THE SYSTEM ]
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($quotes as $quote): ?>
                        <tr class="border-b border-slate-100 hover:bg-slate-50 transition-colors">
                            <td class="p-4 font-mono text-cyan-700 font-bold text-xs whitespace-nowrap"><?= htmlspecialchars($quote['order_number']) ?></td>
                            <td class="p-4">
                                <div class="font-bold text-slate-900"><?= htmlspecialchars($quote['customer_name']) ?></div>
                                <div class="text-[10px] tracking-widest uppercase font-bold text-slate-500"><?= htmlspecialchars($quote['customer_email']) ?></div>
                            </td>
                            <td class="p-4 font-robot font-bold whitespace-nowrap">$<?= number_format($quote['total_amount'], 2) ?></td>
                            <td class="p-4">
                                <form method="POST" enctype="multipart/form-data" class="flex items-center gap-4 m-0">
                                    <input type="hidden" name="quote_id" value="<?= $quote['id'] ?>">
                                    <select name="order_status" class="w-full p-2 text-xs border border-slate-200 bg-white focus:border-cyan-500 focus:outline-none">
                                        <?php
                                        $statuses = ['Processing', 'Quote Sent', 'Quote Accepted', 'Quote Rejected', 'Cancelled'];
                                        foreach ($statuses as $status) {
                                            $selected = ($quote['order_status'] === $status) ? 'selected' : '';
                                            echo "<option value=\"$status\" $selected>$status</option>";
                                        }
                                        ?>
                                    </select>
                            </td>
                            <td class="p-4">
                                    <div class="flex flex-col gap-2">
                                        <?php if ($quote['quote_file_path']): ?>
                                            <a href="uploads/quotes/<?= htmlspecialchars($quote['quote_file_path']) ?>" target="_blank" class="text-[9px] text-cyan-600 hover:text-cyan-800 font-bold uppercase tracking-widest flex items-center gap-1">
                                                📄 View File
                                            </a>
                                        <?php else: ?>
                                            <span class="text-[9px] text-slate-400 font-bold uppercase tracking-widest">No File Attached</span>
                                        <?php endif; ?>
                                        <input type="file" name="quote_file" accept=".pdf" class="text-[9px] w-full file:mr-2 file:py-1 file:px-2 file:border-0 file:text-[9px] file:font-bold file:uppercase file:tracking-widest file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200">
                                    </div>
                            </td>
                            <td class="p-4 text-right">
                                    <button type="submit" name="update_quote" class="bg-slate-900 text-white text-[9px] font-bold tracking-widest uppercase px-4 py-3 hover:bg-cyan-600 transition-colors whitespace-nowrap">
                                        UPDATE
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>