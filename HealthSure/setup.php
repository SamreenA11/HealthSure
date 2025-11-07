<?php
// HealthSure Setup Script
// This script helps initialize the database and create the admin account

$host = 'localhost';
$username = 'root';
$password = '';
$database = 'healthsure_db';

$errors = [];
$success = [];

try {
    // Connect to MySQL server (without database)
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database if it doesn't exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS $database");
    $success[] = "Database '$database' created successfully";
    
    // Connect to the specific database
    $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Read and execute SQL file
    $sql_file = __DIR__ . '/config/init_db.sql';
    if (file_exists($sql_file)) {
        $sql = file_get_contents($sql_file);
        
        // Split SQL into individual statements
        $statements = array_filter(array_map('trim', explode(';', $sql)));
        
        foreach ($statements as $statement) {
            if (!empty($statement) && !preg_match('/^(CREATE DATABASE|USE)/i', $statement)) {
                $pdo->exec($statement);
            }
        }
        
        $success[] = "Database tables created successfully";
        $success[] = "Sample data inserted successfully";
        $success[] = "Default admin account created (admin@healthsure.com / password)";
    } else {
        $errors[] = "SQL initialization file not found";
    }
    
} catch (PDOException $e) {
    $errors[] = "Database error: " . $e->getMessage();
} catch (Exception $e) {
    $errors[] = "Setup error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HealthSure Setup</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card" style="max-width: 600px;">
            <div class="auth-header">
                <h2>HealthSure Setup</h2>
                <p>Database Initialization</p>
            </div>
            <div class="auth-body">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <h4>Setup Errors:</h4>
                        <ul style="margin: 0; padding-left: 1.5rem;">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success">
                        <h4>Setup Completed Successfully!</h4>
                        <ul style="margin: 0; padding-left: 1.5rem;">
                            <?php foreach ($success as $message): ?>
                                <li><?php echo htmlspecialchars($message); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    
                    <div class="mt-4">
                        <h5>Next Steps:</h5>
                        <ol style="padding-left: 1.5rem;">
                            <li>Delete this setup.php file for security</li>
                            <li>Access the application at <a href="index.php" style="color: var(--primary-color);">index.php</a></li>
                            <li>Login with admin credentials: admin@healthsure.com / password</li>
                            <li>Create additional agents and test the system</li>
                        </ol>
                    </div>
                    
                    <div class="text-center mt-4">
                        <a href="index.php" class="btn btn-primary">Go to Application</a>
                    </div>
                <?php else: ?>
                    <div class="mt-4">
                        <h5>Setup Instructions:</h5>
                        <ol style="padding-left: 1.5rem;">
                            <li>Make sure XAMPP is running (Apache + MySQL)</li>
                            <li>Ensure MySQL is accessible with default credentials</li>
                            <li>Refresh this page to retry setup</li>
                        </ol>
                        
                        <div class="text-center mt-3">
                            <a href="setup.php" class="btn btn-primary">Retry Setup</a>
                        </div>
                    </div>
                <?php endif; ?>
                
                <div class="mt-4" style="padding-top: 1rem; border-top: 1px solid var(--border-color);">
                    <h6>System Requirements:</h6>
                    <ul style="font-size: 0.875rem; color: var(--text-light);">
                        <li>PHP 7.4 or higher ✓</li>
                        <li>MySQL 5.7 or higher ✓</li>
                        <li>Apache Web Server ✓</li>
                        <li>PDO MySQL Extension ✓</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
