<?php
require_once 'includes/config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Generate CSRF if missing (required for validation)
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$success_msg = '';
$error_msg = '';

// ==========================================
// 1. HANDLE ADDING NEW SOFTWARE/PRODUCT
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $error_msg = "CSRF Token Validation Failed.";
    } else {
        $category_id = (int)$_POST['category_id'];
        $sku = trim($_POST['sku']);
        $name = trim($_POST['name']);
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
        $short_desc = trim($_POST['short_description']);
        $icon = trim($_POST['icon']);
        $price_display = trim($_POST['price_display'] ?? 'Custom Quote');
        
        $image_path = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = "uploads/";
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
            
            $file_ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            if (in_array($file_ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
                $image_path = uniqid('sw_') . '.' . $file_ext;
                move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $image_path);
            } else {
                $error_msg = "Invalid image format.";
            }
        }

        if (empty($error_msg)) {
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO products 
                    (category_id, sku, name, slug, short_description, icon, image, price_display, status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Active')
                ");
                $stmt->execute([$category_id, $sku, $name, $slug, $short_desc, $icon, $image_path, $price_display]);
                $success_msg = "Software/Asset module successfully registered in the matrix.";
            } catch (PDOException $e) {
                $error_msg = "Database error: " . $e->getMessage();
            }
        }
    }
}

// ==========================================
// 2. HANDLE QUOTE STATUS UPDATE & PDF UPLOAD
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_quote'])) {
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $error_msg = "CSRF Token Validation Failed.";
    } else {
        $quote_id = (int)$_POST['quote_id'];
        $new_status = $_POST['order_status'];
        $quote_file_path = null;
        $update_file_query = "";
        $params = [$new_status];

        if (isset($_FILES['quote_file']) && $_FILES['quote_file']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = "uploads/quotes/";
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
            
            $file_ext = strtolower(pathinfo($_FILES['quote_file']['name'], PATHINFO_EXTENSION));
            if ($file_ext === 'pdf') {
                $quote_file_path = uniqid('quote_') . '.pdf';
                if (move_uploaded_file($_FILES['quote_file']['tmp_name'], $upload_dir . $quote_file_path)) {
                    $update_file_query = ", quote_file_path = ?";
                    $params[] = $quote_file_path;
                }
            } else {
                $error_msg = "Only PDF files are allowed for quotes.";
            }
        }

        $params[] = $quote_id;

        if (empty($error_msg)) {
            try {
                $stmt = $pdo->prepare("UPDATE orders SET order_status = ? $update_file_query WHERE id = ? AND order_type = 'Quotation'");
                $stmt->execute($params);
                $success_msg = "Quotation record updated successfully.";
            } catch (PDOException $e) {
                $error_msg = "Database error: " . $e->getMessage();
            }
        }
    }
}

// ==========================================
// 3. HANDLE ADDING SOFTWARE INFORMATION
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_software_info'])) {
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $error_msg = "CSRF Token Validation Failed.";
    } else {
        $product_id = (int)$_POST['info_product_id'];
        $feature_name = trim($_POST['feature_name']);
        $feature_value = trim($_POST['feature_value']);

        if (!empty($product_id) && !empty($feature_name) && !empty($feature_value)) {
            try {
                $stmt = $pdo->prepare("INSERT INTO software_information (product_id, feature_name, feature_value) VALUES (?, ?, ?)");
                $stmt->execute([$product_id, $feature_name, $feature_value]);
                $success_msg = "Software Information parameter successfully linked to the selected asset.";
            } catch (PDOException $e) {
                $error_msg = "Database error: " . $e->getMessage();
            }
        } else {
            $error_msg = "All information fields are required.";
        }
    }
}

// Fetch necessary data for UI
$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
$quotes = $pdo->query("SELECT * FROM orders WHERE order_type = 'Quotation' ORDER BY created_at DESC")->fetchAll();

require_once 'includes/header.php';
?>

<main class="pt-40 pb-20 px-6 max-w-[1400px] mx-auto min-h-screen relative z-10 bg-slate-50">
    <h1 class="text-4xl font-robot font-bold text-slate-900 mb-8 uppercase border-b border-slate-200 pb-4">Command Center</h1>
    
    <?php if ($success_msg): ?>
        <div class="mb-8 p-4 border border-cyan-500 bg-cyan-50 text-cyan-700 text-xs font-mono uppercase tracking-widest shadow-sm">
            [ SUCCESS ] <?= htmlspecialchars($success_msg) ?>
        </div>
    <?php endif; ?>

    <?php if ($error_msg): ?>
        <div class="mb-8 p-4 border border-red-500 bg-red-500/10 text-red-500 text-xs font-mono uppercase tracking-widest shadow-sm">
            [ ERROR ] <?= htmlspecialchars($error_msg) ?>
        </div>
    <?php endif; ?>

    <div class="grid lg:grid-cols-12 gap-10 items-start">
        
        <!-- SECTION: ADD SOFTWARE / ASSET -->
        <div class="lg:col-span-4 space-y-10 sticky top-32">
            <div class="bg-white p-8 border border-slate-200 shadow-2xl border-t-4 border-cyan-600">
                <h2 class="text-2xl font-robot font-bold text-slate-900 mb-2 uppercase">Add Software Module</h2>
                <p class="text-slate-500 text-[10px] mb-8 tracking-widest uppercase font-bold">Inject new assets into the database.</p>

                <form method="POST" enctype="multipart/form-data" class="space-y-5">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                    
                    <div>
                        <label class="block text-[9px] font-bold text-cyan-700 tracking-widest mb-2 uppercase">Category Assignment</label>
                        <select name="category_id" required class="input-cyber w-full p-4 text-sm bg-slate-50 focus:bg-white appearance-none cursor-pointer">
                            <?php foreach($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[9px] font-bold text-cyan-700 tracking-widest mb-2 uppercase">SKU / ID</label>
                            <input type="text" name="sku" required class="input-cyber w-full p-4 text-sm bg-slate-50 focus:bg-white" placeholder="SW-07">
                        </div>
                        <div>
                            <label class="block text-[9px] font-bold text-cyan-700 tracking-widest mb-2 uppercase">Price Display</label>
                            <input type="text" name="price_display" value="Custom Quote" class="input-cyber w-full p-4 text-sm bg-slate-50 focus:bg-white">
                        </div>
                    </div>

                    <div>
                        <label class="block text-[9px] font-bold text-cyan-700 tracking-widest mb-2 uppercase">Software Name</label>
                        <input type="text" name="name" required class="input-cyber w-full p-4 text-sm bg-slate-50 focus:bg-white" placeholder="e.g. AI Firewall">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[9px] font-bold text-cyan-700 tracking-widest mb-2 uppercase">Icon (e.g. ⎔)</label>
                            <input type="text" name="icon" class="input-cyber w-full p-4 text-sm bg-slate-50 focus:bg-white">
                        </div>
                        <div>
                            <label class="block text-[9px] font-bold text-cyan-700 tracking-widest mb-2 uppercase">Cover Image</label>
                            <input type="file" name="image" class="input-cyber file-input w-full text-[10px] p-3">
                        </div>
                    </div>

                    <div>
                        <label class="block text-[9px] font-bold text-cyan-700 tracking-widest mb-2 uppercase">Short Description</label>
                        <textarea name="short_description" required rows="3" class="input-cyber w-full p-4 text-sm bg-slate-50 focus:bg-white resize-none" placeholder="Module functionality overview..."></textarea>
                    </div>

                    <button type="submit" name="add_product" class="w-full btn-cyber btn-cyber-solid py-4 text-xs font-bold shadow-xl shadow-cyan-500/20 active:scale-[0.99] transition-transform">
                        DEPLOY_ASSET
                    </button>
                </form>
            </div>
            
            <!-- SECTION: ADD SOFTWARE INFO -->
            <div class="bg-white p-8 border border-slate-200 shadow-2xl border-t-4 border-indigo-600">
                <h2 class="text-xl font-robot font-bold text-slate-900 mb-2 uppercase">Link Info/Specs</h2>
                <p class="text-slate-500 text-[10px] mb-8 tracking-widest uppercase font-bold">Attach parameters to existing modules.</p>

                <form method="POST" class="space-y-5">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                    
                    <div>
                        <label class="block text-[9px] font-bold text-indigo-700 tracking-widest mb-2 uppercase">Target Asset</label>
                        <select name="info_product_id" required class="input-cyber w-full p-4 text-sm bg-slate-50 focus:bg-white appearance-none cursor-pointer">
                            <?php 
                            $prods = $pdo->query("SELECT id, name, sku FROM products ORDER BY name ASC")->fetchAll();
                            foreach($prods as $prod): ?>
                                <option value="<?= $prod['id'] ?>">[<?= htmlspecialchars($prod['sku']) ?>] <?= htmlspecialchars($prod['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-[9px] font-bold text-indigo-700 tracking-widest mb-2 uppercase">Parameter Name</label>
                        <input type="text" name="feature_name" required class="input-cyber w-full p-4 text-sm bg-slate-50 focus:bg-white" placeholder="e.g. Tech Stack">
                    </div>

                    <div>
                        <label class="block text-[9px] font-bold text-indigo-700 tracking-widest mb-2 uppercase">Parameter Value</label>
                        <input type="text" name="feature_value" required class="input-cyber w-full p-4 text-sm bg-slate-50 focus:bg-white" placeholder="e.g. React & Node.js">
                    </div>

                    <button type="submit" name="add_software_info" class="w-full btn-cyber py-4 text-xs font-bold shadow-xl shadow-indigo-500/20 active:scale-[0.99] transition-transform border border-indigo-600 text-indigo-600 hover:bg-indigo-600 hover:text-white">
                        LINK_PARAMETER
                    </button>
                </form>
            </div>
        </div>

        <!-- SECTION: MANAGE QUOTES -->
        <div class="lg:col-span-8 bg-white border border-slate-200 shadow-2xl overflow-x-auto border-t-4 border-slate-900">
            <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                <h2 class="text-xl font-robot font-bold text-slate-900 uppercase">Active Quotations</h2>
                <span class="text-[9px] font-bold text-slate-500 tracking-widest uppercase bg-slate-200 px-3 py-1 rounded-full"><?= count($quotes) ?> Records Found</span>
            </div>
            
            <table class="w-full text-left border-collapse min-w-[800px]">
                <thead>
                    <tr class="bg-slate-900 text-white text-[9px] tracking-widest uppercase font-bold">
                        <th class="p-4 w-1/3">Client & Requested Assets</th>
                        <th class="p-4">Est. Value</th>
                        <th class="p-4 w-40">Pipeline Status</th>
                        <th class="p-4 w-40">Attach PDF</th>
                        <th class="p-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    <?php if (empty($quotes)): ?>
                        <tr>
                            <td colspan="5" class="p-12 text-center text-slate-400 font-mono text-xs uppercase tracking-widest bg-slate-50">
                                [ NO QUOTES DETECTED ]
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($quotes as $quote): ?>
                            <tr class="border-b border-slate-100 hover:bg-slate-50/50 transition-colors">
                                <td class="p-4">
                                    <div class="font-mono text-cyan-700 font-bold text-xs"><?= htmlspecialchars($quote['order_number']) ?></div>
                                    <div class="font-bold text-slate-900 text-sm mt-1"><?= htmlspecialchars($quote['customer_name']) ?></div>
                                    <div class="text-[9px] tracking-widest uppercase text-slate-500 mb-3"><?= htmlspecialchars($quote['customer_email']) ?></div>
                                    
                                    <!-- Dynamically List Requested Software Assets -->
                                    <div class="border-t border-slate-100 pt-3">
                                        <span class="text-[8px] font-bold text-slate-400 uppercase tracking-widest block mb-1">Requested Loadout:</span>
                                        <?php
                                        $items_stmt = $pdo->prepare("SELECT oi.quantity, p.name FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
                                        $items_stmt->execute([$quote['id']]);
                                        $items = $items_stmt->fetchAll();
                                        foreach($items as $i): ?>
                                            <div class="text-[10px] text-slate-600 font-medium py-0.5">
                                                <span class="text-cyan-600 font-bold"><?= $i['quantity'] ?>x</span> <?= htmlspecialchars($i['name']) ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </td>
                                <td class="p-4 align-top font-robot font-bold text-slate-900 text-lg">$<?= number_format($quote['total_amount'], 2) ?></td>
                                <td class="p-4 align-top">
                                    <form method="POST" enctype="multipart/form-data" class="flex flex-col gap-3 m-0">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                        <input type="hidden" name="quote_id" value="<?= $quote['id'] ?>">
                                        <select name="order_status" class="input-cyber w-full p-2 text-[10px] font-bold uppercase tracking-widest bg-white border border-slate-200 cursor-pointer">
                                            <?php
                                            $statuses = ['Processing', 'Quote Sent', 'Quote Accepted', 'Quote Rejected', 'Cancelled'];
                                            foreach ($statuses as $status) {
                                                $selected = ($quote['order_status'] === $status) ? 'selected' : '';
                                                echo "<option value=\"$status\" $selected>$status</option>";
                                            }
                                            ?>
                                        </select>
                                </td>
                                <td class="p-4 align-top">
                                        <?php if ($quote['quote_file_path']): ?>
                                            <a href="uploads/quotes/<?= htmlspecialchars($quote['quote_file_path']) ?>" target="_blank" class="text-[9px] text-cyan-600 hover:text-cyan-800 font-bold uppercase tracking-widest block mb-3 border border-cyan-100 bg-cyan-50 text-center py-1">📄 View Active PDF</a>
                                        <?php endif; ?>
                                        <input type="file" name="quote_file" accept=".pdf" class="text-[9px] w-full file:mr-2 file:py-1 file:px-2 file:border-0 file:text-[9px] file:font-bold file:uppercase file:tracking-widest file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 cursor-pointer">
                                </td>
                                <td class="p-4 text-right align-top">
                                        <button type="submit" name="update_quote" class="btn-cyber btn-cyber-solid px-6 py-3 text-[9px] w-full">
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
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>