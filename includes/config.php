<?php
// 1. Security Headers & Cookie Hardening
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_samesite', 'Strict');

if (!headers_sent()) {
    header('X-Frame-Options: SAMEORIGIN');
    header('X-XSS-Protection: 1; mode=block');
    header('X-Content-Type-Options: nosniff');
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Default Timezone (Crucial for e-commerce orders and logs)
date_default_timezone_set('Asia/Dhaka');

// 3. Error Reporting (Development Mode)
// IMPORTANT: Change 1 to 0 when deploying to a live server!
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 4. Site Constants
define('SITE_URL', 'http://infrizo.test'); 
define('SITE_TITLE', 'INFRIZO - IT Solutions || Best IT software company in Bangladesh');

// 5. Database Configuration
$db_host = 'localhost';
$db_name = 'infrizo_db';
$db_user = 'root';
$db_pass = ''; 

try {
    // Create PDO connection
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    
    // Set PDO to throw exceptions on error (Great for debugging)
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Set default fetch mode to Associative Array
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // SECURITY BOOST: Disable emulated prepared statements. 
    // Forces MySQL to handle preparation, preventing edge-case SQL Injections.
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

} catch (PDOException $e) {
    // If connection fails, stop the script. 
    // Note: In production, never echo $e->getMessage() as it can leak database details!
    die("System Offline: Critical Database Failure.");
}
?>