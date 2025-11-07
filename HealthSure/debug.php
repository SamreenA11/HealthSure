<?php
// Debug page to check system status
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>HealthSure Debug Information</h1>";

// Check PHP version
echo "<h3>PHP Information</h3>";
echo "PHP Version: " . PHP_VERSION . "<br>";
echo "PDO Available: " . (extension_loaded('pdo') ? 'Yes' : 'No') . "<br>";
echo "PDO MySQL Available: " . (extension_loaded('pdo_mysql') ? 'Yes' : 'No') . "<br>";

// Check database connection
echo "<h3>Database Connection</h3>";
try {
    require_once 'config/database.php';
    echo "✓ Database connection successful<br>";
    
    // Check if tables exist
    $tables = ['users', 'customers', 'agents', 'policies', 'policy_holders', 'claims', 'payments'];
    echo "<h4>Table Status:</h4>";
    
    foreach ($tables as $table) {
        try {
            $stmt = $conn->query("SELECT COUNT(*) as count FROM $table");
            $count = $stmt->fetch()['count'];
            echo "✓ $table: $count records<br>";
        } catch (Exception $e) {
            echo "❌ $table: Error - " . $e->getMessage() . "<br>";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "<br>";
}

// Check file permissions
echo "<h3>File System</h3>";
echo "Current directory: " . __DIR__ . "<br>";
echo "Config directory exists: " . (is_dir(__DIR__ . '/config') ? 'Yes' : 'No') . "<br>";
echo "Admin directory exists: " . (is_dir(__DIR__ . '/admin') ? 'Yes' : 'No') . "<br>";
echo "Assets directory exists: " . (is_dir(__DIR__ . '/assets') ? 'Yes' : 'No') . "<br>";

// Check specific files
$files_to_check = [
    'config/database.php',
    'includes/functions.php',
    'admin/claims.php',
    'admin/payments.php',
    'admin/dashboard.php'
];

echo "<h4>Critical Files:</h4>";
foreach ($files_to_check as $file) {
    $full_path = __DIR__ . '/' . $file;
    if (file_exists($full_path)) {
        echo "✓ $file exists (" . filesize($full_path) . " bytes)<br>";
    } else {
        echo "❌ $file missing<br>";
    }
}

// Test session
echo "<h3>Session Test</h3>";
session_start();
$_SESSION['test'] = 'working';
echo "Session test: " . ($_SESSION['test'] === 'working' ? 'Working' : 'Failed') . "<br>";

echo "<h3>Quick Actions</h3>";
echo "<a href='setup.php'>Run Setup</a> | ";
echo "<a href='add_sample_data.php'>Add Sample Data</a> | ";
echo "<a href='test_db.php'>Test Database</a> | ";
echo "<a href='auth/login.php'>Login</a><br><br>";

echo "<a href='admin/dashboard.php'>Admin Dashboard</a> | ";
echo "<a href='admin/claims.php'>Claims Page</a> | ";
echo "<a href='admin/payments.php'>Payments Page</a>";
?>
