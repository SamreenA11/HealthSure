<?php
// MySQL Troubleshooting Tool for XAMPP

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MySQL Troubleshoot - HealthSure</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); max-width: 800px; margin: 0 auto; }
        .step { background: #e3f2fd; border-left: 4px solid #2196f3; padding: 15px; margin: 15px 0; }
        .success { color: #28a745; font-weight: bold; }
        .error { color: #dc3545; font-weight: bold; }
        .warning { color: #ffc107; font-weight: bold; }
        .btn { display: inline-block; padding: 10px 20px; margin: 5px; text-decoration: none; border-radius: 5px; color: white; }
        .btn-primary { background: #007bff; }
        .btn-success { background: #28a745; }
        .btn-warning { background: #ffc107; color: #212529; }
        .code { background: #f8f9fa; padding: 10px; border-radius: 5px; font-family: monospace; margin: 10px 0; }
        .check-item { margin: 10px 0; padding: 10px; border-radius: 5px; }
        .check-pass { background: #d4edda; color: #155724; }
        .check-fail { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
<div class="container">
    <h1>🔧 MySQL Troubleshooting Guide</h1>
    
    <div class="step">
        <h3>📋 Step 1: Check XAMPP Control Panel</h3>
        <p>Open XAMPP Control Panel and check MySQL status:</p>
        <ul>
            <li><strong>Green "Running"</strong> = MySQL is working ✅</li>
            <li><strong>Red or no status</strong> = MySQL is not running ❌</li>
        </ul>
        <p>If MySQL is not running, click the <strong>"Start"</strong> button.</p>
    </div>

    <div class="step">
        <h3>🚫 Step 2: Common MySQL Start Issues</h3>
        
        <h4>Issue A: Port 3306 Already in Use</h4>
        <div class="code">
            Error: MySQL shutdown unexpectedly<br>
            Port 3306 in use by another application
        </div>
        <p><strong>Solution:</strong></p>
        <ol>
            <li>Open Command Prompt as Administrator</li>
            <li>Run: <code>netstat -ano | findstr :3306</code></li>
            <li>Kill the process using port 3306</li>
            <li>Or change MySQL port in XAMPP config</li>
        </ol>

        <h4>Issue B: MySQL Service Conflict</h4>
        <div class="code">
            Error: MySQL service already exists
        </div>
        <p><strong>Solution:</strong></p>
        <ol>
            <li>Open Services (services.msc)</li>
            <li>Stop any existing MySQL services</li>
            <li>Set them to "Manual" or "Disabled"</li>
            <li>Restart XAMPP</li>
        </ol>

        <h4>Issue C: Corrupted MySQL Data</h4>
        <div class="code">
            Error: InnoDB initialization failed
        </div>
        <p><strong>Solution:</strong></p>
        <ol>
            <li>Backup <code>C:\xampp\mysql\data</code> folder</li>
            <li>Delete everything except <code>mysql</code> and <code>performance_schema</code> folders</li>
            <li>Restart MySQL in XAMPP</li>
        </ol>
    </div>

    <div class="step">
        <h3>🔍 Step 3: Test MySQL Connection</h3>
        <p>Once MySQL is running, test these connection methods:</p>
        
        <?php
        $connection_tests = [
            ['desc' => 'Default XAMPP (no password)', 'user' => 'root', 'pass' => ''],
            ['desc' => 'Root with password "root"', 'user' => 'root', 'pass' => 'root'],
            ['desc' => 'Root with password "password"', 'user' => 'root', 'pass' => 'password'],
        ];
        
        foreach ($connection_tests as $test) {
            echo "<div class='check-item ";
            try {
                $pdo = new PDO("mysql:host=localhost", $test['user'], $test['pass']);
                echo "check-pass'>✅ {$test['desc']} - <strong>SUCCESS</strong>";
                $stmt = $pdo->query("SELECT VERSION()");
                $version = $stmt->fetchColumn();
                echo "<br>MySQL Version: $version";
            } catch (PDOException $e) {
                echo "check-fail'>❌ {$test['desc']} - <strong>FAILED</strong>";
                echo "<br>Error: " . $e->getMessage();
            }
            echo "</div>";
        }
        ?>
    </div>

    <div class="step">
        <h3>⚡ Step 4: Quick Fixes</h3>
        
        <h4>Option A: Reset MySQL Password</h4>
        <p>If you know MySQL is running but password is wrong:</p>
        <ol>
            <li>Stop MySQL in XAMPP</li>
            <li>Open XAMPP Shell</li>
            <li>Run: <code>mysqld --skip-grant-tables</code></li>
            <li>In another shell: <code>mysql -u root</code></li>
            <li>Run: <code>UPDATE mysql.user SET authentication_string='' WHERE User='root';</code></li>
            <li>Run: <code>FLUSH PRIVILEGES;</code></li>
            <li>Restart MySQL normally</li>
        </ol>

        <h4>Option B: Use phpMyAdmin</h4>
        <p>Test if MySQL works through web interface:</p>
        <p><a href="http://localhost/phpmyadmin" class="btn btn-primary" target="_blank">🗄️ Open phpMyAdmin</a></p>
        <p>If phpMyAdmin works, MySQL is running correctly!</p>

        <h4>Option C: Reinstall XAMPP</h4>
        <p>If nothing else works:</p>
        <ol>
            <li>Backup your HealthSure project folder</li>
            <li>Uninstall XAMPP completely</li>
            <li>Download fresh XAMPP from apachefriends.org</li>
            <li>Install and start MySQL</li>
            <li>Restore your project</li>
        </ol>
    </div>

    <div class="step">
        <h3>🎯 Next Steps</h3>
        <p>Once MySQL is working:</p>
        <p>
            <a href="quick_setup.php" class="btn btn-success">🔄 Retry Quick Setup</a>
            <a href="check_mysql.php" class="btn btn-primary">🔍 Check MySQL Status</a>
            <a href="http://localhost/phpmyadmin" class="btn btn-warning" target="_blank">🗄️ Test phpMyAdmin</a>
        </p>
    </div>

    <div class="step">
        <h3>📞 Still Need Help?</h3>
        <p>If MySQL still won't start, check:</p>
        <ul>
            <li><strong>XAMPP Logs:</strong> Click "Logs" button next to MySQL in XAMPP Control Panel</li>
            <li><strong>Windows Event Viewer:</strong> Look for MySQL-related errors</li>
            <li><strong>Antivirus:</strong> Temporarily disable and try starting MySQL</li>
            <li><strong>Windows Defender:</strong> Add XAMPP folder to exclusions</li>
        </ul>
    </div>
</div>
</body>
</html>
