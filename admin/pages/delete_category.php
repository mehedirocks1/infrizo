<?php
$id = $_GET['id'] ?? null;

if ($id) {
    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->execute([$id]);
}

// Automatically redirect back to the categories list
echo '<script>window.location.href="?page=categories";</script>';
exit;