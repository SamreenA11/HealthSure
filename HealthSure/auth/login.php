<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    redirect('../index.php');
}

$error = '';
$role_hint = $_GET['role'] ?? null;

if ($_POST) {
    $email = sanitize_input($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields';
    } elseif (!validate_email($email)) {
        $error = 'Please enter a valid email address';
    } else {
        try {
            $stmt = $conn->prepare("SELECT user_id, password_hash, role, status FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && verify_password($password, $user['password_hash'])) {
                if ($user['status'] === 'blocked') {
                    $error = 'Your account has been blocked. Please contact support.';
                } elseif ($role_hint && $user['role'] !== $role_hint) {
                    // If a specific role was expected but user has different role
                    $error = "This account is registered as " . ucfirst($user['role']) . ". Please use the correct login portal.";
                } else {
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['email'] = $email;
                    
                    log_activity($user['user_id'], 'User logged in', $conn);
                    
                    // Redirect based on actual user role
                    switch ($user['role']) {
                        case 'admin':
                            redirect('../admin/dashboard.php');
                            break;
                        case 'agent':
                            redirect('../agent/dashboard.php');
                            break;
                        case 'customer':
                            redirect('../customer/dashboard.php');
                            break;
                        default:
                            redirect('../index.php');
                            break;
                    }
                }
            } else {
                $error = 'Invalid email or password';
            }
        } catch (PDOException $e) {
            $error = 'Database error occurred';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - HealthSure</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/animations.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h2>HealthSure</h2>
                <p>
                    <?php if ($role_hint): ?>
                        <?php echo ucfirst($role_hint); ?> Login
                    <?php else: ?>
                        Sign in to your account
                    <?php endif; ?>
                </p>
            </div>
            <div class="auth-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" id="email" name="email" class="form-control" 
                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" id="password" name="password" class="form-control" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary" style="width: 100%;">Sign In</button>
                </form>
                
                <div class="text-center mt-3">
                    <?php if ($role_hint === 'customer'): ?>
                        <p>Don't have an account? <a href="register.php" style="color: var(--primary-color);">Sign up</a></p>
                    <?php else: ?>
                        <p><a href="../landing.php" style="color: var(--primary-color);">← Back to Home</a></p>
                    <?php endif; ?>
                    <p><a href="forgot-password.php" style="color: var(--text-light);">Forgot your password?</a></p>
                </div>
                
                <div class="mt-4" style="padding-top: 1rem; border-top: 1px solid var(--border-color);">
                    <?php if ($role_hint === 'admin'): ?>
                        <p style="font-size: 0.875rem; color: var(--text-light); text-align: center;">
                            <strong>Admin Credentials:</strong><br>
                            Email: admin@healthsure.com<br>
                            Password: password<br>
                            <small style="color: var(--warning-color);">⚠️ Only admin accounts can access this portal</small>
                        </p>
                    <?php elseif ($role_hint === 'agent'): ?>
                        <p style="font-size: 0.875rem; color: var(--text-light); text-align: center;">
                            <strong>Agent Access:</strong><br>
                            Contact admin to create your agent account<br>
                            Use credentials provided by admin<br>
                            <small style="color: var(--warning-color);">⚠️ Only agent accounts can access this portal</small>
                        </p>
                    <?php elseif ($role_hint === 'customer'): ?>
                        <p style="font-size: 0.875rem; color: var(--text-light); text-align: center;">
                            <strong>Customer Access:</strong><br>
                            Register for free or use:<br>
                            customer@test.com / password (demo)<br>
                            <small style="color: var(--warning-color);">⚠️ Only customer accounts can access this portal</small>
                        </p>
                    <?php else: ?>
                        <p style="font-size: 0.875rem; color: var(--text-light); text-align: center;">
                            <strong>Demo Accounts:</strong><br>
                            Admin: admin@healthsure.com / password<br>
                            Customer: Register or use demo account<br>
                            <small style="color: var(--primary-color);">💡 Use role-specific login from landing page</small>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <script src="../assets/js/main.js"></script>
</body>
</html>
