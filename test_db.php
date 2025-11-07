<?php
// Test database connection and check for data
require_once 'config/database.php';

echo "<h2>Database Connection Test</h2>";

try {
    // Test connection
    echo "✓ Database connected successfully<br>";
    
    // Check tables exist
    $tables = ['users', 'customers', 'agents', 'policies', 'policy_holders', 'claims', 'payments'];
    
    foreach ($tables as $table) {
        $stmt = $conn->query("SELECT COUNT(*) as count FROM $table");
        $count = $stmt->fetch()['count'];
        echo "✓ Table '$table': $count records<br>";
    }
    
    // Check if admin user exists
    $stmt = $conn->query("SELECT * FROM users WHERE role = 'admin' LIMIT 1");
    $admin = $stmt->fetch();
    if ($admin) {
        echo "✓ Admin user found: " . $admin['email'] . "<br>";
    } else {
        echo "❌ No admin user found<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}
?>

<h3>Quick Links</h3>
<a href="auth/login.php">Login</a> | 
<a href="admin/dashboard.php">Admin Dashboard</a> | 
<a href="admin/claims.php">Claims</a> | 
<a href="admin/payments.php">Payments</a>
