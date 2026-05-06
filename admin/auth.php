<?php
// session_start(); <-- REMOVED: Our config.php handles this now!

// If the user is not logged in, redirect them to the login terminal
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}
?>