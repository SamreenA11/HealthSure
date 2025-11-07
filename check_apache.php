<?php
echo "<!DOCTYPE html>
<html>
<head>
    <title>Apache & phpMyAdmin Diagnostic</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); max-width: 800px; margin: 0 auto; }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .warning { background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .info { background: #e3f2fd; color: #1976d2; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .btn { display: inline-block; padding: 10px 20px; margin: 5px; text-decoration: none; border-radius: 5px; color: white; }
        .btn-primary { background: #007bff; }
        .btn-success { background: #28a745; }
        .btn-warning { background: #ffc107; color: #212529; }
        .test-item { padding: 10px; margin: 5px 0; border-radius: 5px; border-left: 4px solid #ccc; }
    </style>
</head>
<body>
<div class='container'>
<h1>🔍 Apache & phpMyAdmin Diagnostic</h1>";

// Check PHP info
echo "<div class='info'>
    <h3>📊 System Information</h3>
    <p><strong>PHP Version:</strong> " . phpversion() . "</p>
    <p><strong>Server Software:</strong> " . $_SERVER['SERVER_SOFTWARE'] . "</p>
    <p><strong>Document Root:</strong> " . $_SERVER['DOCUMENT_ROOT'] . "</p>
    <p><strong>Current Script:</strong> " . $_SERVER['SCRIPT_FILENAME'] . "</p>
</div>";

// Test different phpMyAdmin URLs
$phpmyadmin_urls = [
    'http://localhost/phpmyadmin/',
    'http://localhost/phpMyAdmin/',
    'http://localhost/pma/',
    'http://127.0.0.1/phpmyadmin/',
    'http://127.0.0.1/phpMyAdmin/',
];

echo "<div class='info'>
    <h3>🔗 Testing phpMyAdmin URLs</h3>";

foreach ($phpmyadmin_urls as $url) {
    echo "<div class='test-item'>";
    echo "<strong>Testing:</strong> <a href='$url' target='_blank'>$url</a><br>";
    
    // Check if URL responds
    $context = stream_context_create([
        'http' => [
            'timeout' => 5,
            'method' => 'HEAD'
        ]
    ]);
    
    $headers = @get_headers($url, 1, $context);
    
    if ($headers && strpos($headers[0], '200') !== false) {
        echo "<span style='color: #28a745; font-weight: bold;'>✅ ACCESSIBLE</span>";
    } elseif ($headers && strpos($headers[0], '403') !== false) {
        echo "<span style='color: #ffc107; font-weight: bold;'>⚠️ FORBIDDEN (may still work)</span>";
    } else {
        echo "<span style='color: #dc3545; font-weight: bold;'>❌ NOT ACCESSIBLE</span>";
    }
    echo "</div>";
}

echo "</div>";

// Check if phpMyAdmin directory exists
$possible_paths = [
    $_SERVER['DOCUMENT_ROOT'] . '/phpmyadmin',
    $_SERVER['DOCUMENT_ROOT'] . '/phpMyAdmin',
    $_SERVER['DOCUMENT_ROOT'] . '/pma',
    'C:/xampp/phpMyAdmin',
    'C:/xampp/htdocs/phpmyadmin',
    'C:/xampp/htdocs/phpMyAdmin',
];

echo "<div class='info'>
    <h3>📁 Checking phpMyAdmin Installation</h3>";

$found_phpmyadmin = false;
foreach ($possible_paths as $path) {
    if (is_dir($path)) {
        echo "<div class='success'>✅ Found phpMyAdmin at: <strong>$path</strong></div>";
        $found_phpmyadmin = true;
        
        // Check if index.php exists
        if (file_exists($path . '/index.php')) {
            echo "<div class='success'>✅ index.php exists in phpMyAdmin directory</div>";
        } else {
            echo "<div class='error'>❌ index.php missing in phpMyAdmin directory</div>";
        }
        break;
    }
}

if (!$found_phpmyadmin) {
    echo "<div class='error'>❌ phpMyAdmin directory not found in common locations</div>";
}

echo "</div>";

// Test MySQL connection
echo "<div class='info'>
    <h3>🗄️ Testing MySQL Connection</h3>";

$mysql_configs = [
    ['user' => 'root', 'pass' => '', 'desc' => 'Default (no password)'],
    ['user' => 'root', 'pass' => 'root', 'desc' => 'Password: root'],
];

$mysql_working = false;
foreach ($mysql_configs as $config) {
    try {
        $pdo = new PDO(\"mysql:host=localhost\", \$config['user'], \$config['pass']);
        echo \"<div class='success'>✅ MySQL connection works with {$config['desc']}</div>\";
        \$mysql_working = true;
        break;
    } catch (PDOException \$e) {
        echo \"<div class='error'>❌ MySQL failed with {$config['desc']}: \" . \$e->getMessage() . \"</div>\";
    }
}

echo \"</div>\";

// Solutions
echo \"<div class='warning'>
    <h3>🛠️ Solutions</h3>\";

if (!\$found_phpmyadmin) {
    echo \"<h4>phpMyAdmin Not Found:</h4>
    <ol>
        <li><strong>Check XAMPP Installation:</strong> Ensure you downloaded the full XAMPP package</li>
        <li><strong>Reinstall XAMPP:</strong> Download from <a href='https://www.apachefriends.org/' target='_blank'>apachefriends.org</a></li>
        <li><strong>Manual Install:</strong> Download phpMyAdmin separately and extract to htdocs</li>
    </ol>\";
} else {
    echo \"<h4>phpMyAdmin Found but Not Accessible:</h4>
    <ol>
        <li><strong>Try Direct URLs:</strong> Click the links above to test different URLs</li>
        <li><strong>Check Apache Config:</strong> Look for alias settings in httpd.conf</li>
        <li><strong>Clear Browser Cache:</strong> Try Ctrl+F5 or incognito mode</li>
    </ol>\";
}

if (!\$mysql_working) {
    echo \"<h4>MySQL Connection Issues:</h4>
    <ol>
        <li><strong>Start MySQL:</strong> Ensure MySQL is running in XAMPP Control Panel</li>
        <li><strong>Check Credentials:</strong> Try different root passwords</li>
        <li><strong>Reset Password:</strong> Use XAMPP's password reset tools</li>
    </ol>\";
}

echo \"</div>\";

// Quick actions
echo \"<div class='info'>
    <h3>🎯 Quick Actions</h3>
    <p>
        <a href='http://localhost/' class='btn btn-primary' target='_blank'>🏠 Test localhost</a>
        <a href='http://localhost/dashboard/' class='btn btn-primary' target='_blank'>📊 XAMPP Dashboard</a>
        <a href='test_mysql_auth.php' class='btn btn-success'>🔍 Test MySQL Auth</a>
        <a href='check_apache.php' class='btn btn-warning'>🔄 Refresh This Test</a>
    </p>
</div>\";

echo \"</div></body></html>\";
?>
