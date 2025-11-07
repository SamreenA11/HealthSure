<?php
echo "<!DOCTYPE html>
<html>
<head>
    <title>MySQL Authentication Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); max-width: 600px; margin: 0 auto; }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .test { padding: 10px; margin: 5px 0; border-radius: 5px; }
        .btn { display: inline-block; padding: 10px 20px; margin: 5px; text-decoration: none; border-radius: 5px; color: white; background: #007bff; }
    </style>
</head>
<body>
<div class='container'>
<h2>🔍 MySQL Authentication Test</h2>
<p><strong>MySQL is running!</strong> Testing different credentials...</p>";

$configs = [
    ['user' => 'root', 'pass' => '', 'desc' => 'Default XAMPP (no password)'],
    ['user' => 'root', 'pass' => 'root', 'desc' => 'Root with password "root"'],
    ['user' => 'root', 'pass' => 'password', 'desc' => 'Root with password "password"'],
    ['user' => 'root', 'pass' => 'mysql', 'desc' => 'Root with password "mysql"'],
    ['user' => 'root', 'pass' => '123456', 'desc' => 'Root with password "123456"'],
];

$working_config = null;

foreach ($configs as $config) {
    echo "<div class='test' style='border-left: 4px solid ";
    
    try {
        $pdo = new PDO("mysql:host=localhost", $config['user'], $config['pass']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Test query
        $stmt = $pdo->query("SELECT VERSION() as version, USER() as user");
        $result = $stmt->fetch();
        
        echo "#28a745;'>
            <strong style='color: #28a745;'>✅ SUCCESS: {$config['desc']}</strong><br>
            MySQL Version: {$result['version']}<br>
            Connected as: {$result['user']}
        </div>";
        
        $working_config = $config;
        break;
        
    } catch (PDOException $e) {
        echo "#dc3545;'>
            <strong style='color: #dc3545;'>❌ FAILED: {$config['desc']}</strong><br>
            Error: " . $e->getMessage() . "
        </div>";
    }
}

if ($working_config) {
    echo "<div class='success'>
        <h3>🎉 Found Working Configuration!</h3>
        <p>Username: <strong>{$working_config['user']}</strong></p>
        <p>Password: <strong>" . (empty($working_config['pass']) ? '(no password)' : $working_config['pass']) . "</strong></p>
    </div>";
    
    // Try to create database
    try {
        $pdo = new PDO("mysql:host=localhost", $working_config['user'], $working_config['pass']);
        $pdo->exec("CREATE DATABASE IF NOT EXISTS healthsure_db");
        echo "<div class='success'>✅ Database 'healthsure_db' created successfully!</div>";
        
        // Update database config
        $config_content = "<?php
class Database {
    private \$host = 'localhost';
    private \$db_name = 'healthsure_db';
    private \$username = '{$working_config['user']}';
    private \$password = '{$working_config['pass']}';
    private \$conn;

    public function getConnection() {
        \$this->conn = null;
        try {
            \$this->conn = new PDO(\"mysql:host=\" . \$this->host . \";dbname=\" . \$this->db_name . \";charset=utf8mb4\", 
                                \$this->username, \$this->password);
            \$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException \$exception) {
            die(\"Connection error: \" . \$exception->getMessage());
        }
        return \$this->conn;
    }
    
    public static function connect() {
        \$database = new Database();
        return \$database->getConnection();
    }
}

// Global database connection
\$database = new Database();
\$conn = \$database->getConnection();
?>";
        
        file_put_contents(__DIR__ . '/config/database.php', $config_content);
        echo "<div class='success'>✅ Database configuration updated!</div>";
        
        echo "<p><a href='quick_setup.php' class='btn'>🚀 Run Setup Now</a></p>";
        
    } catch (PDOException $e) {
        echo "<div class='error'>❌ Failed to create database: " . $e->getMessage() . "</div>";
    }
    
} else {
    echo "<div class='error'>
        <h3>❌ No Working Configuration Found</h3>
        <p>This usually means MySQL root user has a password that's not common.</p>
        <p><strong>Try resetting MySQL password:</strong></p>
        <ol>
            <li>Stop MySQL in XAMPP Control Panel</li>
            <li>Open XAMPP Shell (button in control panel)</li>
            <li>Run: <code>mysql_reset_root_password.bat</code></li>
            <li>Start MySQL again</li>
        </ol>
    </div>";
}

echo "<p><a href='test_mysql_auth.php' class='btn'>🔄 Test Again</a></p>";
echo "</div></body></html>";
?>
