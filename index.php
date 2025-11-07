<?php
session_start();

// If user is already logged in, redirect to appropriate dashboard
if (isset($_SESSION['user_id'])) {
    switch ($_SESSION['role']) {
        case 'admin':
            header('Location: admin/dashboard.php');
            break;
        case 'agent':
            header('Location: agent/dashboard.php');
            break;
        case 'customer':
            header('Location: customer/dashboard.php');
            break;
        default:
            header('Location: auth/login.php');
            break;
    }
    exit();
}

// If not logged in, redirect to landing page
header('Location: landing.php');
exit();
?>
