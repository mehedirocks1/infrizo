<?php
// 1. Require config (This securely resumes your active session)
require_once '../includes/config.php';

// 2. Clear all session variables
session_unset();

// 3. Destroy the session completely from the server
session_destroy();

// 4. Redirect to the login terminal
header("Location: login.php");
exit;
?>