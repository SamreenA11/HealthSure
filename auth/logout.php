<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Log logout activity
if (isset($_SESSION['user_id'])) {
    log_activity($_SESSION['user_id'], 'User logged out', $conn);
}

// Clear all session variables
$_SESSION = array();

// Destroy the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy session
session_destroy();

// Redirect to login
redirect('login.php');
?>
