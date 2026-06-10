<?php
require_once 'includes/config.php';

$service_id = $_GET['service_id'] ?? '';
$service_name = $_GET['service_name'] ?? '';

$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_quote'])) {
    $client_name = $_POST['client_name'] ?? '';
    $client_email = $_POST['client_email'] ?? '';
    $client_phone = $_POST['client_phone'] ?? '';
    $company_name = $_POST['company_name'] ?? '';
    $requirements = $_POST['message'] ?? '';
    
    // Append the service details to the message so the admin knows what this is for
    $message = "";
    if ($service_name) {
        $message .= "Service Request: $service_name";
        if ($service_id) {
            $message .= " ($service_id)";
        }
        $message .= "\n\n";
    }
    $message .= "Requirements:\n$requirements";

    // Insert into the inquiries table with 'Unread' default status
    $stmt = $pdo->prepare("INSERT INTO inquiries (client_name, client_email, client_phone, company_name, message) VALUES (?, ?, ?, ?, ?)");
    if ($stmt->execute([$client_name, $client_email, $client_phone, $company_name, $message])) {
        $success = true;
    }
}

require_once 'includes/header.php';
?>

<main class="pt-40 pb-20 px-6 min-h-screen flex items-center justify-center relative overflow-hidden">
    <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-cyan-500/5 rounded-full blur-3xl"></div>
    
    <div class="max-w-4xl w-full relative z-10">
        <?php if ($success): ?>
            <div class="sci-fi-card p-12 text-center bg-white/90 backdrop-blur-md">
                <div class="w-20 h-20 bg-cyan-500 text-white rounded-full flex items-center justify-center text-4xl mx-auto mb-6 shadow-lg shadow-cyan-500/50">
                    ✓
                </div>
                <h1 class="text-4xl font-robot font-bold text-slate-900 mb-4">QUOTATION REQUEST TRANSMITTED.</h1>
                <p class="text-slate-600 mb-8 uppercase tracking-widest text-xs font-bold">
                    Your request <?php if($service_name) echo "for <span class=\"text-cyan-600\">" . htmlspecialchars($service_name) . "</span> "; ?>has been logged into the matrix.
                </p>
                <div class="p-4 bg-slate-50 border border-slate-200 text-slate-500 font-mono text-[10px] mb-8 leading-relaxed text-left">
                    &gt; Protocol: Quotation Request<br>
                    &gt; Status: Awaiting Admin Review...
                </div>
                <a href="index.php" class="btn-cyber btn-cyber-solid px-10 py-4 inline-block text-xs">RETURN_TO_BASE</a>
            </div>

        <?php else: ?>
            <div class="grid md:grid-cols-5 gap-0 shadow-2xl">
                
                <div class="md:col-span-2 bg-slate-900 p-8 text-white flex flex-col justify-between relative overflow-hidden">
                    <div class="relative z-10">
                        <div class="text-cyan-500 font-bold text-[10px] tracking-[0.3em] mb-8 uppercase">// SERVICE_SUMMARY</div>
                        
                        <div class="mb-6">
                            <div class="text-[10px] text-slate-500 uppercase tracking-widest mb-1">Designation</div>
                            <h2 class="text-2xl font-robot font-bold"><?= htmlspecialchars($service_name) ?: 'General Inquiry' ?></h2>
                        </div>

                        <?php if ($service_id): ?>
                        <div class="mb-6">
                            <div class="text-[10px] text-slate-500 uppercase tracking-widest mb-1">ID_Code</div>
                            <div class="font-mono text-cyan-400"><?= htmlspecialchars($service_id) ?></div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="md:col-span-3 bg-white p-10 border-y md:border-y-0 md:border-r border-slate-200">
                    <h2 class="text-3xl font-robot font-bold text-slate-900 mb-2 uppercase">Request_Quotation.</h2>
                    <p class="text-slate-500 text-xs mb-8 tracking-widest uppercase font-bold">Input requirements to request a quote.</p>

                    <form method="POST" action="" class="space-y-6">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div class="relative">
                                <label class="block text-[9px] font-bold text-cyan-700 tracking-widest mb-2 uppercase">Full_Name</label>
                                <input type="text" name="client_name" required class="input-cyber w-full p-4 text-sm bg-slate-50 focus:bg-white" placeholder="ENTER SUBJECT NAME">
                            </div>
                            <div class="relative">
                                <label class="block text-[9px] font-bold text-cyan-700 tracking-widest mb-2 uppercase">Comm_Link (Email)</label>
                                <input type="email" name="client_email" required class="input-cyber w-full p-4 text-sm bg-slate-50 focus:bg-white" placeholder="COMM_LINK@SERVER.COM">
                            </div>
                            <div class="relative">
                                <label class="block text-[9px] font-bold text-cyan-700 tracking-widest mb-2 uppercase">Phone_Number</label>
                                <input type="text" name="client_phone" class="input-cyber w-full p-4 text-sm bg-slate-50 focus:bg-white" placeholder="OPTIONAL">
                            </div>
                            <div class="relative">
                                <label class="block text-[9px] font-bold text-cyan-700 tracking-widest mb-2 uppercase">Company_Name</label>
                                <input type="text" name="company_name" class="input-cyber w-full p-4 text-sm bg-slate-50 focus:bg-white" placeholder="OPTIONAL">
                            </div>
                        </div>
                        <div class="relative">
                            <label class="block text-[9px] font-bold text-cyan-700 tracking-widest mb-2 uppercase">Project_Requirements</label>
                            <textarea name="message" required rows="4" class="input-cyber w-full p-4 text-sm bg-slate-50 focus:bg-white resize-none" placeholder="SPECIFY YOUR NEEDS..."></textarea>
                        </div>
                        <button type="submit" name="submit_quote" class="w-full btn-cyber btn-cyber-solid py-5 text-sm font-bold shadow-xl shadow-cyan-500/20">
                            TRANSMIT_REQUEST
                        </button>
                        <div class="text-center">
                            <a href="index.php" class="text-[10px] text-slate-400 hover:text-slate-900 font-bold tracking-widest uppercase transition-colors">[ ABORT_OPERATION ]</a>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>