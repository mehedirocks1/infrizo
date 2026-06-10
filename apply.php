<?php
require_once 'includes/config.php';

$success = false;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_application'])) {
    // File Upload Configuration
    $photo_dir = "uploads/photos/";
    $cv_dir = "uploads/cvs/";

    // --- Form Data ---
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $portfolio = $_POST['portfolio_link'] ?? '';
    $cover_letter = $_POST['cover_letter'] ?? '';

    // --- File Handling ---
    $photo_file = $_FILES['photo'];
    $cv_file = $_FILES['cv'];

    // Function to handle a single file upload
    function handle_upload($file, $target_dir, $allowed_types) {
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['error' => "File upload failed with error code: " . $file['error']];
        }
        
        $file_name = uniqid() . '_' . basename($file["name"]);
        $target_file = $target_dir . $file_name;
        $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        if (!in_array($file_type, $allowed_types)) {
            return ['error' => "Invalid file type. Allowed: " . implode(', ', $allowed_types)];
        }

        if (move_uploaded_file($file["tmp_name"], $target_file)) {
            return ['path' => $file_name];
        } else {
            return ['error' => "Failed to move uploaded file."];
        }
    }

    $photo_upload = handle_upload($photo_file, $photo_dir, ['jpg', 'jpeg', 'png', 'webp']);
    $cv_upload = handle_upload($cv_file, $cv_dir, ['pdf', 'doc', 'docx']);

    if (isset($photo_upload['error'])) $errors[] = "Photo: " . $photo_upload['error'];
    if (isset($cv_upload['error'])) $errors[] = "CV: " . $cv_upload['error'];

    // --- Database Insertion ---
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare(
                "INSERT INTO freelancer_applications (name, email, phone, portfolio_link, photo_path, cv_path, cover_letter) 
                 VALUES (?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute([$name, $email, $phone, $portfolio, $photo_upload['path'], $cv_upload['path'], $cover_letter]);
            $success = true;
        } catch (PDOException $e) {
            $errors[] = "Database error: Could not submit application.";
            // Optional: log $e->getMessage() for debugging
        }
    }
}

require_once 'includes/header.php';
?>

<main class="pt-40 pb-20 px-6 min-h-screen flex items-center justify-center relative overflow-hidden bg-slate-50">
    <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-cyan-500/5 rounded-full blur-3xl"></div>
    
    <div class="max-w-4xl w-full relative z-10">
        <?php if ($success): ?>
            <div class="sci-fi-card p-12 text-center bg-white/90 backdrop-blur-md">
                <h1 class="text-4xl font-robot font-bold text-slate-900 mb-4">APPLICATION TRANSMITTED.</h1>
                <p class="text-slate-600 mb-8 uppercase tracking-widest text-xs font-bold">
                    Your credentials have been logged. We will review your profile and respond if a suitable mission arises.
                </p>
                <a href="index.php" class="btn-cyber btn-cyber-solid px-10 py-4 inline-block text-xs">RETURN_TO_INDEX</a>
            </div>
        <?php else: ?>
            <div class="bg-white p-10 border border-slate-200 shadow-2xl">
                <h2 class="text-3xl font-robot font-bold text-slate-900 mb-2 uppercase">Freelance Operative Application.</h2>
                <p class="text-slate-500 text-xs mb-8 tracking-widest uppercase font-bold">Submit your credentials for deployment consideration.</p>

                <?php if (!empty($errors)): ?>
                    <div class="mb-8 p-4 border border-red-500 bg-red-500/10 text-red-500 text-xs font-mono uppercase tracking-widest">
                        <?php foreach ($errors as $error): ?>
                            <p>[ ERROR ] <?= htmlspecialchars($error) ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" enctype="multipart/form-data" class="space-y-6">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-[9px] font-bold text-cyan-700 tracking-widest mb-2 uppercase">Full Name</label>
                            <input type="text" name="name" required class="input-cyber w-full p-4 text-sm bg-slate-50 focus:bg-white" placeholder="Subject Name">
                        </div>
                        <div>
                            <label class="block text-[9px] font-bold text-cyan-700 tracking-widest mb-2 uppercase">Email Address</label>
                            <input type="email" name="email" required class="input-cyber w-full p-4 text-sm bg-slate-50 focus:bg-white" placeholder="comm_link@domain.com">
                        </div>
                        <div>
                            <label class="block text-[9px] font-bold text-cyan-700 tracking-widest mb-2 uppercase">Phone Number</label>
                            <input type="text" name="phone" class="input-cyber w-full p-4 text-sm bg-slate-50 focus:bg-white" placeholder="Contact Frequency">
                        </div>
                        <div>
                            <label class="block text-[9px] font-bold text-cyan-700 tracking-widest mb-2 uppercase">Portfolio Link</label>
                            <input type="url" name="portfolio_link" class="input-cyber w-full p-4 text-sm bg-slate-50 focus:bg-white" placeholder="https://...">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-[9px] font-bold text-cyan-700 tracking-widest mb-2 uppercase">Profile Photo (JPG, PNG, WEBP)</label>
                            <input type="file" name="photo" required class="input-cyber file-input w-full text-sm">
                        </div>
                        <div>
                            <label class="block text-[9px] font-bold text-cyan-700 tracking-widest mb-2 uppercase">Curriculum Vitae (PDF, DOC)</label>
                            <input type="file" name="cv" required class="input-cyber file-input w-full text-sm">
                        </div>
                    </div>

                    <div>
                        <label class="block text-[9px] font-bold text-cyan-700 tracking-widest mb-2 uppercase">Cover Letter / Capabilities</label>
                        <textarea name="cover_letter" rows="5" class="input-cyber w-full p-4 text-sm bg-slate-50 focus:bg-white resize-none" placeholder="Detail your operational expertise..."></textarea>
                    </div>

                    <button type="submit" name="submit_application" class="w-full btn-cyber btn-cyber-solid py-5 text-sm font-bold shadow-xl shadow-cyan-500/20">
                        SUBMIT_APPLICATION
                    </button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>ludes/footer.php'; ?>