<?php
// MySQL Fix Script - Diagnose and fix MySQL startup issues

echo "<!DOCTYPE html>
<html>
<head>
    <title>MySQL Fix - HealthSure</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); max-width: 800px; margin: 0 auto; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .warning { background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .step { background: #e3f2fd; padding: 15px; border-left: 4px solid #2196f3; margin: 15px 0; }
        .btn { display: inline-block; padding: 10px 20px; margin: 5px; text-decoration: none; border-radius: 5px; color: white; }
        .btn-primary { background: #007bff; }
        .btn-danger { background: #dc3545; }
        .btn-success { background: #28a745; }
        .code { background: #f8f9fa; padding: 10px; border-radius: 5px; font-family: monospace; margin: 10px 0; }
    </style>
</head>
<body>
<div class='container'>
<h1>🔧 MySQL Connection Fix</h1>";

// Check if MySQL process is running
$mysql_running = false;
$output = [];
exec('tasklist /FI "IMAGENAME eq mysqld.exe" 2>NUL', $output);

if (count($output) > 1) {
    echo "<div class='success'>✅ MySQL process is running</div>";
    $mysql_running = true;
} else {
    echo "<div class='error'>❌ MySQL process is NOT running</div>";
}

// Check port 3306
$port_output = [];
exec('netstat -an | findstr :3306', $port_output);

if (!empty($port_output)) {
    echo "<div class='success'>✅ Port 3306 is active</div>";
} else {
    echo "<div class='error'>❌ Port 3306 is not listening</div>";
}

// If MySQL is not running, provide solutions
if (!$mysql_running) {
    echo "<div class='step'>
        <h3>🚨 MySQL is Not Running - Here's How to Fix It:</h3>
        
        <h4>Solution 1: Start MySQL in XAMPP</h4>
        <ol>
            <li>Open <strong>XAMPP Control Panel</strong></li>
            <li>Find the <strong>MySQL</strong> row</li>
            <li>Click the <strong>'Start'</strong> button</li>
            <li>Wait for it to show <strong>green 'Running'</strong> status</li>
        </ol>
        
        <h4>Solution 2: If MySQL Won't Start</h4>
        <p><strong>Error: Port 3306 in use</strong></p>
        <div class='code'>
            1. Open Command Prompt as Administrator<br>
            2. Run: netstat -ano | findstr :3306<br>
            3. Kill the process using that port<br>
            4. Or restart your computer
        </div>
        
        <p><strong>Error: MySQL service conflict</strong></p>
        <div class='code'>
            1. Open Services (Win+R, type: services.msc)<br>
            2. Find any MySQL services<br>
            3. Stop them and set to 'Manual'<br>
            4. Restart XAMPP
        </div>
        
        <h4>Solution 3: Reset MySQL</h4>
        <div class='warning'>
            <strong>⚠️ This will delete all databases!</strong><br>
            Only use if nothing else works:
            <ol>
                <li>Stop MySQL in XAMPP</li>
                <li>Go to: C:\\xampp\\mysql\\data\\</li>
                <li>Backup the folder</li>
                <li>Delete everything except 'mysql' and 'performance_schema' folders</li>
                <li>Start MySQL in XAMPP</li>
            </ol>
        </div>
    </div>";
} else {
    // MySQL is running, test connection
    echo "<div class='step'>
        <h3>🔍 Testing MySQL Connection</h3>";
    
    $configs = [
        ['user' => 'root', 'pass' => '', 'desc' => 'Default XAMPP (no password)'],
        ['user' => 'root', 'pass' => 'root', 'desc' => 'Root with password'],
    ];
    
    $working_config = null;
    
    foreach ($configs as $config) {
        echo "<p>Testing {$config['desc']}... ";
        try {
            $pdo = new PDO("mysql:host=localhost", $config['user'], $config['pass']);
            echo "<span style='color: green; font-weight: bold;'>SUCCESS!</span></p>";
            $working_config = $config;
            break;
        } catch (PDOException $e) {
            echo "<span style='color: red; font-weight: bold;'>FAILED</span><br>";
            echo "Error: " . $e->getMessage() . "</p>";
        }
    }
    
    if ($working_config) {
        echo "<div class='success'>✅ Found working MySQL configuration!</div>";
        
        // Create database and update config
        try {
            $pdo = new PDO("mysql:host=localhost", $working_config['user'], $working_config['pass']);
            $pdo->exec("CREATE DATABASE IF NOT EXISTS healthsure_db");
            
            // Update database.php
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
            
            file_put_contents(__DIR__ . '/config/database.php', $config_content);
            
            echo "<div class='success'>✅ Database configuration updated!</div>";
            echo "<p><a href='quick_setup.php' class='btn btn-success'>🚀 Run Setup Now</a></p>";
            
        } catch (PDOException $e) {
            echo "<div class='error'>❌ Failed to create database: " . $e->getMessage() . "</div>";
        }
    }
    
    echo "</div>";
}

echo "<div class='step'>
    <h3>🎯 Next Steps</h3>
    <p>After fixing MySQL:</p>
    <p>
        <a href='fix_mysql.php' class='btn btn-primary'>🔄 Refresh This Page</a>
        <a href='quick_setup.php' class='btn btn-success'>⚙️ Run Setup</a>
        <a href='http://localhost/phpmyadmin' class='btn btn-primary' target='_blank'>🗄️ Test phpMyAdmin</a>
    </p>
</div>";

echo "</div></body></html>";
?>
