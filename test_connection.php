<?php
// Simple connection test
echo "<h2>Database Connection Test</h2>";

// Test different MySQL configurations
$configs = [
    ['user' => 'root', 'pass' => '', 'desc' => 'Default XAMPP (no password)'],
    ['user' => 'root', 'pass' => 'root', 'desc' => 'Root with password "root"'],
    ['user' => 'root', 'pass' => 'password', 'desc' => 'Root with password "password"'],
    ['user' => 'root', 'pass' => 'mysql', 'desc' => 'Root with password "mysql"']
];

$working_config = null;

foreach ($configs as $config) {
    echo "<p>Testing: {$config['desc']}... ";
    
    try {
        $pdo = new PDO("mysql:host=localhost", $config['user'], $config['pass']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Test query
        $stmt = $pdo->query("SELECT VERSION() as version");
        $version = $stmt->fetch();
        
        echo "<span style='color: green; font-weight: bold;'>SUCCESS!</span>";
        echo "<br>MySQL Version: " . $version['version'];
        $working_config = $config;
        break;
        
    } catch (PDOException $e) {
        echo "<span style='color: red; font-weight: bold;'>FAILED</span>";
        echo "<br>Error: " . $e->getMessage();
    }
    echo "</p>";
}

if ($working_config) {
    echo "<h3 style='color: green;'>✅ MySQL is working!</h3>";
    echo "<p>Working configuration: {$working_config['desc']}</p>";
    
    // Try to create database
    try {
        $pdo = new PDO("mysql:host=localhost", $working_config['user'], $working_config['pass']);
        $pdo->exec("CREATE DATABASE IF NOT EXISTS healthsure_db");
        echo "<p style='color: green;'>✅ Database 'healthsure_db' created successfully!</p>";
        
        // Update config file
        $config_content = "<?php
\$host = 'localhost';
\$username = '{$working_config['user']}';
\$password = '{$working_config['pass']}';
\$database = 'healthsure_db';

try {
    \$pdo = new PDO(\"mysql:host=\$host;dbname=\$database\", \$username, \$password);
    \$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException \$e) {
    die('Connection failed: ' . \$e->getMessage());
}

// For backward compatibility
\$conn = \$pdo;
?>";
        
        file_put_contents('config/database.php', $config_content);
        echo "<p style='color: green;'>✅ Database configuration updated!</p>";
        
        echo "<p><a href='auth/login.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔐 Try Login Now</a></p>";
        
    } catch (PDOException $e) {
        echo "<p style='color: red;'>❌ Failed to create database: " . $e->getMessage() . "</p>";
    }
    
} else {
    echo "<h3 style='color: red;'>❌ MySQL Connection Failed</h3>";
    echo "<p>Please check XAMPP Control Panel and ensure MySQL is running (green status).</p>";
    echo "<p><strong>Steps to fix:</strong></p>";
    echo "<ol>";
    echo "<li>Open XAMPP Control Panel</li>";
    echo "<li>Click 'Start' next to MySQL if it's not running</li>";
    echo "<li>Wait for it to show green 'Running' status</li>";
    echo "<li>Refresh this page</li>";
    echo "</ol>";
}

echo "<p><a href='landing.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🏠 Back to Landing</a></p>";
?>
