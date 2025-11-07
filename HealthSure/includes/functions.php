<?php
// Common utility functions

function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function hash_password($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

function verify_password($password, $hash) {
    return password_verify($password, $hash);
}

function generate_random_string($length = 10) {
    return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1,$length);
}

function format_currency($amount) {
    return '₹' . number_format($amount, 2);
}

function format_date($date) {
    return date('d M Y', strtotime($date));
}

function calculate_age($birthdate) {
    $today = new DateTime();
    $birth = new DateTime($birthdate);
    return $today->diff($birth)->y;
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function set_flash_message($type, $message) {
    $_SESSION['flash_type'] = $type;
    $_SESSION['flash_message'] = $message;
}

function get_flash_message() {
    if (isset($_SESSION['flash_message'])) {
        $type = $_SESSION['flash_type'];
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_type']);
        unset($_SESSION['flash_message']);
        return ['type' => $type, 'message' => $message];
    }
    return null;
}

function check_login() {
    if (!isset($_SESSION['user_id'])) {
        redirect('../auth/login.php');
    }
}

function check_role($required_role) {
    check_login();
    if ($_SESSION['role'] !== $required_role) {
        redirect('../index.php');
    }
}

function get_user_info($user_id, $conn) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function get_customer_info($user_id, $conn) {
    $stmt = $conn->prepare("SELECT c.*, u.email FROM customers c 
                           JOIN users u ON c.user_id = u.user_id 
                           WHERE c.user_id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function get_agent_info($user_id, $conn) {
    $stmt = $conn->prepare("SELECT a.*, u.email FROM agents a 
                           JOIN users u ON a.user_id = u.user_id 
                           WHERE a.user_id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function log_activity($user_id, $action, $conn) {
    try {
        // Check if activity_logs table exists, if not, skip logging
        $stmt = $conn->prepare("SHOW TABLES LIKE 'activity_logs'");
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, ip_address, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$user_id, $action, $_SERVER['REMOTE_ADDR'] ?? 'unknown']);
        }
    } catch (Exception $e) {
        // Silently ignore logging errors to prevent breaking the application
        error_log("Activity logging failed: " . $e->getMessage());
    }
}
?>
