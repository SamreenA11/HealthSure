<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Logout - HealthSure</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h2>Logout Test</h2>
            </div>
            <div class="auth-body">
                <h4>Current Session Status:</h4>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="alert alert-success">
                        <strong>Logged In</strong><br>
                        User ID: <?php echo $_SESSION['user_id']; ?><br>
                        Role: <?php echo $_SESSION['role'] ?? 'Unknown'; ?><br>
                        Email: <?php echo $_SESSION['email'] ?? 'Unknown'; ?>
                    </div>
                    
                    <a href="auth/logout.php" class="btn btn-danger" style="width: 100%;">
                        Test Logout
                    </a>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <strong>Not Logged In</strong><br>
                        No active session found.
                    </div>
                    
                    <a href="auth/login.php" class="btn btn-primary" style="width: 100%;">
                        Go to Login
                    </a>
                <?php endif; ?>
                
                <div class="mt-4">
                    <h5>Quick Links:</h5>
                    <a href="auth/login.php">Login</a> | 
                    <a href="debug.php">Debug</a> | 
                    <a href="index.php">Home</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
