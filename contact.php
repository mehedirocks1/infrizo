<?php
require_once 'includes/config.php';

$success = false;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_inquiry'])) {
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        die('CSRF token validation failed.');
    }
    // Sanitize incoming payload
    $client_name = trim($_POST['client_name'] ?? '');
    $client_email = trim($_POST['client_email'] ?? '');
    $client_phone = trim($_POST['client_phone'] ?? '');
    $company_name = trim($_POST['company_name'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $product_id = !empty($_POST['product_id']) ? (int)$_POST['product_id'] : null;

    // Basic validation
    if (empty($client_name) || empty($client_email) || empty($message)) {
        $errors[] = "Subject Name, Comm Link, and Message Payload are strictly required.";
    }

    // Process insertion
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO inquiries (product_id, client_name, client_email, client_phone, company_name, message) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$product_id, $client_name, $client_email, $client_phone, $company_name, $message]);
            $success = true;
        } catch (PDOException $e) {
            $errors[] = "System transmission failure. Could not connect to central database.";
        }
    }
}

// Fetch active products for the dropdown so users can select an asset
$products = [];
try {
    $products = $pdo->query("SELECT id, name, sku FROM products WHERE status != 'Hidden' ORDER BY name ASC")->fetchAll();
} catch (Exception $e) {}

// Auto-select product if passed via URL parameter (e.g., contact.php?product_id=5)
$preselected_product = $_GET['product_id'] ?? '';

require_once 'includes/header.php';
?>

<main class="pt-40 pb-20 px-6 min-h-screen flex items-center justify-center relative overflow-hidden bg-slate-50">
    <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-cyan-500/5 rounded-full blur-3xl"></div>
    
    <div class="max-w-4xl w-full relative z-10">
        <?php if ($success): ?>
            <!-- Success Transmission HUD -->
            <div class="sci-fi-card p-12 text-center bg-white/90 backdrop-blur-md border-t-4 border-cyan-500 shadow-2xl">
                <h1 class="text-4xl font-robot font-bold text-slate-900 mb-4">COMMUNICATION TRANSMITTED.</h1>
                <p class="text-slate-600 mb-8 uppercase tracking-widest text-xs font-bold leading-relaxed">
                    Your message has been securely logged into the INFRIZO matrix.<br>An operative will review your data and respond shortly.
                </p>
                <a href="index.php" class="btn-cyber btn-cyber-solid px-10 py-4 inline-block text-xs">RETURN_TO_BASE</a>
            </div>
        <?php else: ?>
            <!-- Contact Form Interface -->
            <div class="bg-white p-10 border border-slate-200 shadow-2xl border-t-4 border-cyan-600">
                <h2 class="text-3xl font-robot font-bold text-slate-900 mb-2 uppercase">Initiate Contact.</h2>
                <p class="text-slate-500 text-xs mb-8 tracking-widest uppercase font-bold">Transmit inquiries to central command.</p>

                <?php if (!empty($errors)): ?>
                    <div class="mb-8 p-4 border border-red-500 bg-red-500/10 text-red-500 text-xs font-mono uppercase tracking-widest">
                        <?php foreach ($errors as $error): ?>
                            <p>[ ERROR ] <?= htmlspecialchars($error) ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" class="space-y-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-[9px] font-bold text-cyan-700 tracking-widest mb-2 uppercase">Subject Name *</label>
                            <input type="text" name="client_name" required class="input-cyber w-full p-4 text-sm bg-slate-50 focus:bg-white" placeholder="Enter full name">
                        </div>
                        <div>
                            <label class="block text-[9px] font-bold text-cyan-700 tracking-widest mb-2 uppercase">Comm Link (Email) *</label>
                            <input type="email" name="client_email" required class="input-cyber w-full p-4 text-sm bg-slate-50 focus:bg-white" placeholder="comm_link@domain.com">
                        </div>
                        <div>
                            <label class="block text-[9px] font-bold text-cyan-700 tracking-widest mb-2 uppercase">Phone Number</label>
                            <input type="text" name="client_phone" class="input-cyber w-full p-4 text-sm bg-slate-50 focus:bg-white" placeholder="Optional Frequency">
                        </div>
                        <div>
                            <label class="block text-[9px] font-bold text-cyan-700 tracking-widest mb-2 uppercase">Organization / Entity</label>
                            <input type="text" name="company_name" class="input-cyber w-full p-4 text-sm bg-slate-50 focus:bg-white" placeholder="Company Name">
                        </div>
                    </div>

                    <div>
                        <label class="block text-[9px] font-bold text-cyan-700 tracking-widest mb-2 uppercase">Target Asset (Optional)</label>
                        <select name="product_id" class="input-cyber w-full p-4 text-sm bg-slate-50 focus:bg-white appearance-none cursor-pointer">
                            <option value="">-- General System Inquiry --</option>
                            <?php foreach($products as $prod): ?>
                                <option value="<?= $prod['id'] ?>" <?= $preselected_product == $prod['id'] ? 'selected' : '' ?>>
                                    [<?= htmlspecialchars($prod['sku']) ?>] <?= htmlspecialchars($prod['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[9px] font-bold text-cyan-700 tracking-widest mb-2 uppercase">Message Payload *</label>
                        <textarea name="message" required rows="5" class="input-cyber w-full p-4 text-sm bg-slate-50 focus:bg-white resize-none" placeholder="Enter your inquiry data..."></textarea>
                    </div>

                    <button type="submit" name="submit_inquiry" class="w-full btn-cyber btn-cyber-solid py-5 text-sm font-bold shadow-xl shadow-cyan-500/20 active:scale-[0.99] transition-transform">
                        TRANSMIT_MESSAGE
                    </button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>