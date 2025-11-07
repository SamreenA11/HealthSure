<?php
// Simple MySQL Service Checker for XAMPP

header('Content-Type: text/html; charset=utf-8');

echo "<!DOCTYPE html>
<html>
<head>
    <title>MySQL Service Checker</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
        .container { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .success { color: #28a745; font-weight: bold; }
        .error { color: #dc3545; font-weight: bold; }
        .info { color: #007bff; }
        .code { background: #f8f9fa; padding: 10px; border-radius: 5px; font-family: monospace; margin: 10px 0; }
        .btn { display: inline-block; padding: 10px 20px; margin: 5px; text-decoration: none; border-radius: 5px; }
        .btn-primary { background: #007bff; color: white; }
        .btn-success { background: #28a745; color: white; }
    </style>
</head>
<body>
<div class='container'>
<h2>🔍 MySQL Service Status Checker</h2>";

// Check if MySQL extension is loaded
echo "<h3>PHP MySQL Extensions</h3>";
echo "<div class='code'>";
echo "PDO Extension: " . (extension_loaded('pdo') ? "<span class='success'>✅ Loaded</span>" : "<span class='error'>❌ Not Loaded</span>") . "<br>";
echo "PDO MySQL Driver: " . (extension_loaded('pdo_mysql') ? "<span class='success'>✅ Loaded</span>" : "<span class='error'>❌ Not Loaded</span>") . "<br>";
echo "MySQL Extension: " . (extension_loaded('mysql') ? "<span class='success'>✅ Loaded</span>" : "<span class='info'>ℹ️ Not Loaded (deprecated)</span>") . "<br>";
echo "MySQLi Extension: " . (extension_loaded('mysqli') ? "<span class='success'>✅ Loaded</span>" : "<span class='error'>❌ Not Loaded</span>") . "<br>";
echo "</div>";

// Check MySQL process
echo "<h3>MySQL Process Status</h3>";
echo "<div class='code'>";

$mysql_running = false;

// Method 1: Check for mysqld process
$output = [];
exec('tasklist /FI "IMAGENAME eq mysqld.exe" 2>NUL', $output);
if (count($output) > 1) {
    echo "MySQL Process: <span class='success'>✅ mysqld.exe is running</span><br>";
    $mysql_running = true;
} else {
    echo "MySQL Process: <span class='error'>❌ mysqld.exe not found</span><br>";
}

// Method 2: Check port 3306
$output2 = [];
exec('netstat -an | findstr :3306', $output2);
if (!empty($output2)) {
    echo "Port 3306: <span class='success'>✅ Active and listening</span><br>";
    $mysql_running = true;
} else {
    echo "Port 3306: <span class='error'>❌ Not listening</span><br>";
}

echo "</div>";

// Test MySQL connection
echo "<h3>MySQL Connection Test</h3>";
echo "<div class='code'>";

$configs = [
    ['user' => 'root', 'pass' => ''],
    ['user' => 'root', 'pass' => 'root'],
    ['user' => 'root', 'pass' => 'password']
];

$connection_success = false;
foreach ($configs as $config) {
    try {
        $pdo = new PDO("mysql:host=localhost", $config['user'], $config['pass']);
        echo "Connection with user '{$config['user']}': <span class='success'>✅ SUCCESS</span><br>";
        
        // Test query
        $stmt = $pdo->query("SELECT VERSION() as version");
        $version = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "MySQL Version: <span class='info'>{$version['version']}</span><br>";
        
        $connection_success = true;
        break;
    } catch (PDOException $e) {
        echo "Connection with user '{$config['user']}': <span class='error'>❌ " . $e->getMessage() . "</span><br>";
    }
}

echo "</div>";

// Recommendations
echo "<h3>📋 Recommendations</h3>";

if ($mysql_running && $connection_success) {
    echo "<div class='success'>✅ MySQL is working correctly! You can proceed with the setup.</div>";
    echo "<p><a href='setup_advanced.php' class='btn btn-success'>🚀 Run Advanced Setup</a></p>";
} elseif ($mysql_running && !$connection_success) {
    echo "<div class='error'>⚠️ MySQL is running but connection failed. This usually means:</div>";
    echo "<ul>
        <li>MySQL root user has a different password</li>
        <li>MySQL is configured for different authentication</li>
        <li>Try resetting MySQL password in XAMPP</li>
    </ul>";
} else {
    echo "<div class='error'>❌ MySQL is not running. Please:</div>";
    echo "<ul>
        <li>Open XAMPP Control Panel</li>
        <li>Click 'Start' next to MySQL</li>
        <li>Wait for it to turn green</li>
        <li>If it fails, check the logs</li>
    </ul>";
}

echo "<h3>🛠️ Quick Actions</h3>";
echo "<p>
    <a href='check_mysql.php' class='btn btn-primary'>🔄 Refresh Check</a>
    <a href='setup_advanced.php' class='btn btn-primary'>⚙️ Advanced Setup</a>
    <a href='http://localhost/phpmyadmin' class='btn btn-primary' target='_blank'>🗄️ phpMyAdmin</a>
    <a href='landing.php' class='btn btn-primary'>🏠 Back to Landing</a>
</p>";

echo "</div></body></html>";
?>
