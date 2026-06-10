<?php
require_once 'auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $application_id = $_POST['application_id'] ?? null;
    $action = $_POST['action'] ?? null;

    if ($application_id && in_array($action, ['Approved', 'Rejected'])) {
        try {
            $stmt = $pdo->prepare("UPDATE freelancer_applications SET status = ? WHERE id = ?");
            $stmt->execute([$action, $application_id]);
        } catch (PDOException $e) {
            // Handle error, maybe set a session message
        }
    }
}

header("Location: index.php?page=applications");
exit;