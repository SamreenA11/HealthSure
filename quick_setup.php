<?php
// Quick Database Setup - Creates database and tables automatically

$host = 'localhost';
$database = 'healthsure_db';

// Try different MySQL configurations
$mysql_configs = [
    ['username' => 'root', 'password' => ''],
    ['username' => 'root', 'password' => 'root'],
    ['username' => 'root', 'password' => 'password'],
    ['username' => 'root', 'password' => 'mysql'],
];

$success = false;
$working_config = null;

foreach ($mysql_configs as $config) {
    try {
        // First connect without database to create it
        $pdo = new PDO("mysql:host=$host", $config['username'], $config['password']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Create database
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$database` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        
        // Connect to the database
        $pdo = new PDO("mysql:host=$host;dbname=$database", $config['username'], $config['password']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Create users table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS users (
                user_id INT PRIMARY KEY AUTO_INCREMENT,
                email VARCHAR(255) UNIQUE NOT NULL,
                password_hash VARCHAR(255) NOT NULL,
                role ENUM('admin', 'agent', 'customer') NOT NULL,
                first_name VARCHAR(100) NOT NULL,
                last_name VARCHAR(100) NOT NULL,
                phone VARCHAR(20),
                address TEXT,
                status ENUM('active', 'blocked') DEFAULT 'active',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ");
        
        // Create policies table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS policies (
                policy_id INT PRIMARY KEY AUTO_INCREMENT,
                policy_number VARCHAR(50) UNIQUE NOT NULL,
                policy_type ENUM('health', 'life', 'family') NOT NULL,
                policy_name VARCHAR(255) NOT NULL,
                description TEXT,
                premium_amount DECIMAL(10,2) NOT NULL,
                coverage_amount DECIMAL(12,2) NOT NULL,
                duration_months INT NOT NULL,
                is_active BOOLEAN DEFAULT TRUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
        
        // Create customer_policies table
        $pdo->exec("
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
                FOREIGN KEY (customer_id) REFERENCES users(user_id) ON DELETE CASCADE,
                FOREIGN KEY (policy_id) REFERENCES policies(policy_id) ON DELETE CASCADE,
                FOREIGN KEY (agent_id) REFERENCES users(user_id) ON DELETE SET NULL
            )
        ");
        
        // Insert default admin user (if not exists)
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
        $stmt->execute(['admin@healthsure.com']);
        if ($stmt->fetchColumn() == 0) {
            $admin_password = password_hash('password', PASSWORD_DEFAULT);
            $pdo->prepare("INSERT INTO users (email, password_hash, role, first_name, last_name) VALUES (?, ?, 'admin', 'System', 'Administrator')")
                ->execute(['admin@healthsure.com', $admin_password]);
        }
        
        // Insert sample policies (if not exists)
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM policies");
        $stmt->execute();
        if ($stmt->fetchColumn() == 0) {
            $policies = [
                ['POL001', 'health', 'Basic Health Insurance', 'Basic health coverage for individuals', 500.00, 50000.00, 12],
                ['POL002', 'life', 'Term Life Insurance', 'Term life insurance coverage', 300.00, 100000.00, 60],
                ['POL003', 'family', 'Family Health Plan', 'Comprehensive family health coverage', 800.00, 75000.00, 12]
            ];
            
            $stmt = $pdo->prepare("INSERT INTO policies (policy_number, policy_type, policy_name, description, premium_amount, coverage_amount, duration_months) VALUES (?, ?, ?, ?, ?, ?, ?)");
            foreach ($policies as $policy) {
                $stmt->execute($policy);
            }
        }
        
        $success = true;
        $working_config = $config;
        break;
        
    } catch (PDOException $e) {
        continue;
    }
}

// Update database.php with working configuration
if ($success && $working_config) {
    $config_content = "<?php
class Database {
    private \$host = 'localhost';
    private \$db_name = 'healthsure_db';
    private \$username = '{$working_config['username']}';
    private \$password = '{$working_config['password']}';
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
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quick Setup - HealthSure</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
        .container { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); max-width: 600px; margin: 0 auto; }
        .success { color: #28a745; font-weight: bold; }
        .error { color: #dc3545; font-weight: bold; }
        .btn { display: inline-block; padding: 10px 20px; margin: 10px 5px; text-decoration: none; border-radius: 5px; color: white; }
        .btn-primary { background: #007bff; }
        .btn-success { background: #28a745; }
    </style>
</head>
<body>
<div class="container">
    <h2>🚀 HealthSure Quick Setup</h2>
    
    <?php if ($success): ?>
        <div class="success">
            <h3>✅ Setup Completed Successfully!</h3>
            <p>Database and tables created with working MySQL configuration:</p>
            <ul>
                <li>Username: <strong><?php echo $working_config['username']; ?></strong></li>
                <li>Password: <strong><?php echo empty($working_config['password']) ? '(no password)' : '(password set)'; ?></strong></li>
                <li>Database: <strong>healthsure_db</strong></li>
            </ul>
            
            <h4>Default Admin Account:</h4>
            <ul>
                <li>Email: <strong>admin@healthsure.com</strong></li>
                <li>Password: <strong>password</strong></li>
            </ul>
            
            <p>
                <a href="index.php" class="btn btn-success">🏠 Go to Application</a>
                <a href="auth/login.php" class="btn btn-primary">🔐 Login</a>
            </p>
        </div>
    <?php else: ?>
        <div class="error">
            <h3>❌ Setup Failed</h3>
            <p>Could not connect to MySQL with any common configuration.</p>
            <p><strong>Please ensure:</strong></p>
            <ul>
                <li>XAMPP is running</li>
                <li>MySQL service is started (green in XAMPP Control Panel)</li>
                <li>No firewall blocking port 3306</li>
            </ul>
            
            <p>
                <a href="quick_setup.php" class="btn btn-primary">🔄 Retry</a>
                <a href="check_mysql.php" class="btn btn-primary">🔍 Check MySQL</a>
            </p>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
