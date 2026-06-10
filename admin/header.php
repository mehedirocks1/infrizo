<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>INFRIZO Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-slate-100">
    <header class="bg-slate-900 text-white shadow-lg">
        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
            <h1 class="font-robot text-2xl font-bold">
                <a href="index.php">⟨ADMIN_PANEL⟩</a>
            </h1>
            <nav class="flex items-center gap-6 text-sm font-bold uppercase tracking-widest">
                <a href="applications.php" class="hover:text-cyan-400 transition-colors">Applications</a>
                <a href="engineers.php" class="hover:text-cyan-400 transition-colors">Engineers</a>
                <!-- Add other admin links here -->
                <a href="logout.php" class="bg-cyan-600 hover:bg-cyan-700 px-4 py-2 transition-colors">Logout</a>
            </nav>
        </div>
    </header>
    <main class="max-w-7xl mx-auto p-6">