<?php
// Reset admin password utility
require_once 'config/database.php';
require_once 'includes/functions.php';

echo "<h2>Admin Password Reset</h2>";

try {
    // Generate new password hash for 'password'
    $new_password = 'password';
    $password_hash = hash_password($new_password);
    
    // Update admin password
    $stmt = $conn->prepare("UPDATE users SET password_hash = ? WHERE email = 'admin@healthsure.com'");
    $stmt->execute([$password_hash]);
    
    if ($stmt->rowCount() > 0) {
        echo "✅ Admin password reset successfully!<br>";
        echo "<strong>Email:</strong> admin@healthsure.com<br>";
        echo "<strong>Password:</strong> password<br><br>";
        
        echo "<a href='auth/login.php'>Go to Login</a> | ";
        echo "<a href='test_logout.php'>Test Session</a>";
    } else {
        echo "❌ Admin user not found. Please run setup first.<br>";
        echo "<a href='setup.php'>Run Setup</a>";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
    echo "<a href='debug.php'>Debug System</a>";
}
?>
