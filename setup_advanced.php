<?php
// Advanced HealthSure Setup Script with comprehensive MySQL detection

error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = 'localhost';
$database = 'healthsure_db';
$errors = [];
$success = [];
$diagnostics = [];
$system_info = [];

// Check system requirements
$system_info[] = "PHP Version: " . phpversion();
$system_info[] = "PDO Available: " . (extension_loaded('pdo') ? 'Yes' : 'No');
$system_info[] = "PDO MySQL Available: " . (extension_loaded('pdo_mysql') ? 'Yes' : 'No');

// Check if MySQL service is running
function checkMySQLService() {
    $output = [];
    $return_var = 0;
    
    // Try to check MySQL service on Windows
    exec('tasklist /FI "IMAGENAME eq mysqld.exe" 2>NUL', $output, $return_var);
    
    if (!empty($output) && count($output) > 1) {
        return "MySQL service is running";
    }
    
    // Alternative check
    exec('netstat -an | findstr :3306', $output2, $return_var2);
    if (!empty($output2)) {
        return "Port 3306 is active (MySQL likely running)";
    }
    
    return "MySQL service may not be running";
}

$diagnostics[] = checkMySQLService();

// Comprehensive MySQL configuration testing
$mysql_configs = [
    ['username' => 'root', 'password' => '', 'desc' => 'Default XAMPP (no password)'],
    ['username' => 'root', 'password' => 'root', 'desc' => 'XAMPP with root password'],
    ['username' => 'root', 'password' => 'password', 'desc' => 'Common password setup'],
    ['username' => 'root', 'password' => 'mysql', 'desc' => 'MySQL default password'],
    ['username' => 'root', 'password' => '123456', 'desc' => 'Simple password'],
    ['username' => 'mysql', 'password' => '', 'desc' => 'MySQL user (no password)'],
    ['username' => 'mysql', 'password' => 'mysql', 'desc' => 'MySQL user with password'],
];

$pdo = null;
$working_config = null;

// Test each configuration
foreach ($mysql_configs as $config) {
    try {
        $dsn = "mysql:host=$host;charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        
        $test_pdo = new PDO($dsn, $config['username'], $config['password'], $options);
        
        // Test if we can actually query
        $test_pdo->query("SELECT 1");
        
        // Test if we can show databases
        $stmt = $test_pdo->query("SHOW DATABASES");
        $databases = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $pdo = $test_pdo;
        $working_config = $config;
        $diagnostics[] = "✅ SUCCESS: {$config['desc']} - Found " . count($databases) . " databases";
        break;
        
    } catch (PDOException $e) {
        $error_msg = $e->getMessage();
        if (strpos($error_msg, 'Access denied') !== false) {
            $diagnostics[] = "❌ {$config['desc']}: Access denied (wrong credentials)";
        } elseif (strpos($error_msg, 'Connection refused') !== false) {
            $diagnostics[] = "❌ {$config['desc']}: Connection refused (MySQL not running?)";
        } elseif (strpos($error_msg, 'No such file') !== false) {
            $diagnostics[] = "❌ {$config['desc']}: Socket error (MySQL not installed?)";
        } else {
            $diagnostics[] = "❌ {$config['desc']}: " . substr($error_msg, 0, 100);
        }
        continue;
    }
}

// If we have a working connection, proceed with setup
if ($pdo && $working_config) {
    try {
        // Create database
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$database` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $success[] = "Database '$database' created successfully";
        
        // Connect to the specific database
        $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8mb4", 
                      $working_config['username'], 
                      $working_config['password'],
                      [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        
        // Create tables directly (in case SQL file is missing)
        $tables_sql = "
        -- Users table
        CREATE TABLE IF NOT EXISTS users (
            id INT PRIMARY KEY AUTO_INCREMENT,
            email VARCHAR(255) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            role ENUM('admin', 'agent', 'customer') NOT NULL,
            first_name VARCHAR(100) NOT NULL,
            last_name VARCHAR(100) NOT NULL,
            phone VARCHAR(20),
            address TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        );

        -- Policies table
        CREATE TABLE IF NOT EXISTS policies (
            id INT PRIMARY KEY AUTO_INCREMENT,
            policy_number VARCHAR(50) UNIQUE NOT NULL,
            policy_type ENUM('health', 'life', 'family') NOT NULL,
            policy_name VARCHAR(255) NOT NULL,
            description TEXT,
            premium_amount DECIMAL(10,2) NOT NULL,
            coverage_amount DECIMAL(12,2) NOT NULL,
            duration_months INT NOT NULL,
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );

        -- Customer policies table
        CREATE TABLE IF NOT EXISTS customer_policies (
            id INT PRIMARY KEY AUTO_INCREMENT,
            customer_id INT NOT NULL,
            policy_id INT NOT NULL,
            agent_id INT,
            start_date DATE NOT NULL,
            end_date DATE NOT NULL,
            status ENUM('active', 'expired', 'cancelled') DEFAULT 'active',
            premium_paid DECIMAL(10,2) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (policy_id) REFERENCES policies(id) ON DELETE CASCADE,
            FOREIGN KEY (agent_id) REFERENCES users(id) ON DELETE SET NULL
        );

        -- Insert default admin user
        INSERT IGNORE INTO users (email, password, role, first_name, last_name) 
        VALUES ('admin@healthsure.com', '" . password_hash('password', PASSWORD_DEFAULT) . "', 'admin', 'System', 'Administrator');

        -- Insert sample policies
        INSERT IGNORE INTO policies (policy_number, policy_type, policy_name, description, premium_amount, coverage_amount, duration_months) VALUES
        ('POL001', 'health', 'Basic Health Insurance', 'Basic health coverage for individuals', 500.00, 50000.00, 12),
        ('POL002', 'life', 'Term Life Insurance', 'Term life insurance coverage', 300.00, 100000.00, 60),
        ('POL003', 'family', 'Family Health Plan', 'Comprehensive family health coverage', 800.00, 75000.00, 12);
        ";
        
        // Execute the SQL
        $statements = array_filter(array_map('trim', explode(';', $tables_sql)));
        foreach ($statements as $statement) {
            if (!empty($statement)) {
                $pdo->exec($statement);
            }
        }
        
        $success[] = "Database tables created successfully";
        $success[] = "Default admin account created (admin@healthsure.com / password)";
        $success[] = "Sample policies added successfully";
        
        // Save working configuration
        $config_dir = __DIR__ . '/config';
        if (!is_dir($config_dir)) {
            mkdir($config_dir, 0755, true);
        }
        
        $config_content = "<?php\n";
        $config_content .= "// Database Configuration - Auto-generated by setup\n";
        $config_content .= "define('DB_HOST', '$host');\n";
        $config_content .= "define('DB_NAME', '$database');\n";
        $config_content .= "define('DB_USER', '{$working_config['username']}');\n";
        $config_content .= "define('DB_PASS', '{$working_config['password']}');\n";
        $config_content .= "\n// PDO Connection function\n";
        $config_content .= "function getDBConnection() {\n";
        $config_content .= "    try {\n";
        $config_content .= "        \$pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS);\n";
        $config_content .= "        \$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);\n";
        $config_content .= "        return \$pdo;\n";
        $config_content .= "    } catch (PDOException \$e) {\n";
        $config_content .= "        die('Database connection failed: ' . \$e->getMessage());\n";
        $config_content .= "    }\n";
        $config_content .= "}\n";
        $config_content .= "?>";
        
        file_put_contents($config_dir . '/database.php', $config_content);
        $success[] = "Database configuration saved to config/database.php";
        
    } catch (PDOException $e) {
        $errors[] = "Database setup error: " . $e->getMessage();
    } catch (Exception $e) {
        $errors[] = "General setup error: " . $e->getMessage();
    }
} else {
    $errors[] = "Could not establish MySQL connection with any configuration";
    $errors[] = "Please ensure MySQL is running in XAMPP Control Panel";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HealthSure Advanced Setup</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .diagnostic-item { 
            font-family: 'Courier New', monospace; 
            font-size: 0.9rem; 
            margin: 0.25rem 0;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
        }
        .diagnostic-item:hover {
            background: rgba(0,0,0,0.05);
        }
        .system-info {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 1rem;
            margin: 1rem 0;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card" style="max-width: 700px;">
            <div class="auth-header">
                <h2>🏥 HealthSure Advanced Setup</h2>
                <p>Comprehensive Database Initialization & Diagnostics</p>
            </div>
            <div class="auth-body">
                
                <!-- System Information -->
                <div class="system-info">
                    <h5>📊 System Information</h5>
                    <?php foreach ($system_info as $info): ?>
                        <div class="diagnostic-item"><?php echo htmlspecialchars($info); ?></div>
                    <?php endforeach; ?>
                </div>

                <!-- Connection Diagnostics -->
                <?php if (!empty($diagnostics)): ?>
                    <div class="alert alert-info" style="background: #e3f2fd; border-color: #2196f3; color: #1976d2;">
                        <h4>🔍 MySQL Connection Diagnostics</h4>
                        <?php foreach ($diagnostics as $diagnostic): ?>
                            <div class="diagnostic-item"><?php echo htmlspecialchars($diagnostic); ?></div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- Errors -->
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <h4>❌ Setup Errors</h4>
                        <ul style="margin: 0; padding-left: 1.5rem;">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <!-- Success Messages -->
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success">
                        <h4>✅ Setup Completed Successfully!</h4>
                        <ul style="margin: 0; padding-left: 1.5rem;">
                            <?php foreach ($success as $message): ?>
                                <li><?php echo htmlspecialchars($message); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    
                    <div class="mt-4">
                        <h5>🎉 Next Steps</h5>
                        <ol style="padding-left: 1.5rem;">
                            <li>Your database is now ready!</li>
                            <li>Access the application: <a href="index.php" style="color: var(--primary-color);">HealthSure Application</a></li>
                            <li>Login with: <strong>admin@healthsure.com</strong> / <strong>password</strong></li>
                            <li>Delete setup files for security</li>
                        </ol>
                    </div>
                    
                    <div class="text-center mt-4">
                        <a href="index.php" class="btn btn-primary">🚀 Launch HealthSure</a>
                        <a href="landing.php" class="btn btn-outline ml-2">🏠 Landing Page</a>
                    </div>
                <?php else: ?>
                    <div class="mt-4">
                        <h5>🛠️ Troubleshooting Steps</h5>
                        <div class="row">
                            <div class="col-6">
                                <h6>1. Check XAMPP Status</h6>
                                <ul style="font-size: 0.9rem;">
                                    <li>Open XAMPP Control Panel</li>
                                    <li>Start Apache (should show green)</li>
                                    <li>Start MySQL (should show green)</li>
                                    <li>If red, click "Start" button</li>
                                </ul>
                            </div>
                            <div class="col-6">
                                <h6>2. Test MySQL Access</h6>
                                <ul style="font-size: 0.9rem;">
                                    <li>Click "Admin" next to MySQL</li>
                                    <li>Should open phpMyAdmin</li>
                                    <li>If error, MySQL isn't running</li>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="text-center mt-3">
                            <a href="setup_advanced.php" class="btn btn-primary">🔄 Retry Setup</a>
                            <a href="http://localhost/phpmyadmin" class="btn btn-outline ml-2" target="_blank">🗄️ Open phpMyAdmin</a>
                            <a href="setup.php" class="btn btn-outline ml-2">📋 Basic Setup</a>
                        </div>
                    </div>
                <?php endif; ?>
                
            </div>
        </div>
    </div>
</body>
</html>
