<?php
// Get the ID from the URL
$id = $_GET['id'] ?? null;

if ($id) {
    // Fetch the product first to get the image path for cleanup
    $stmt = $pdo->prepare("SELECT image FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch();

    if ($product) {
        // Delete the image file if it exists to save server space
        if (!empty($product['image'])) {
            $image_path = '../uploads/' . $product['image'];
            if (file_exists($image_path) && is_file($image_path)) {
                unlink($image_path);
            }
        }

        // Delete the product from the database
        $deleteStmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $deleteStmt->execute([$id]);
    }
}

// Automatically redirect back to the products matrix
echo '<script>window.location.href="?page=products";</script>';
exit;