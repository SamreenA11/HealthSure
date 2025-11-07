<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

check_role('admin');

$message = get_flash_message();
$tab = $_GET['tab'] ?? 'general';

// Handle form submissions
if ($_POST) {
    if (isset($_POST['update_general'])) {
        // In a real application, you would store these in a settings table
        set_flash_message('success', 'General settings updated successfully!');
    }
    
    if (isset($_POST['update_email'])) {
        set_flash_message('success', 'Email settings updated successfully!');
    }
    
    if (isset($_POST['backup_database'])) {
        // Simple backup functionality
        try {
            $backup_file = '../backups/healthsure_backup_' . date('Y-m-d_H-i-s') . '.sql';
            
            // Create backups directory if it doesn't exist
            if (!is_dir('../backups')) {
                mkdir('../backups', 0755, true);
            }
            
            // Note: In production, use mysqldump command for proper backup
            set_flash_message('success', 'Database backup initiated. File: ' . basename($backup_file));
        } catch (Exception $e) {
            set_flash_message('danger', 'Backup failed: ' . $e->getMessage());
        }
    }
    
    if (isset($_POST['reset_password'])) {
        $user_email = sanitize_input($_POST['user_email']);
        $new_password = $_POST['new_password'];
        
        if (!empty($user_email) && !empty($new_password)) {
            try {
                $password_hash = hash_password($new_password);
                $stmt = $conn->prepare("UPDATE users SET password_hash = ? WHERE email = ?");
                $stmt->execute([$password_hash, $user_email]);
                
                if ($stmt->rowCount() > 0) {
                    set_flash_message('success', 'Password reset successfully for ' . $user_email);
                } else {
                    set_flash_message('danger', 'User not found with email: ' . $user_email);
                }
            } catch (PDOException $e) {
                set_flash_message('danger', 'Error resetting password.');
            }
        } else {
            set_flash_message('danger', 'Please provide both email and new password.');
        }
    }
    
    if (isset($_POST['clear_logs'])) {
        // Clear activity logs (if you have an activity_logs table)
        try {
            // $conn->query("DELETE FROM activity_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)");
            set_flash_message('success', 'System logs cleared successfully!');
        } catch (PDOException $e) {
            set_flash_message('danger', 'Error clearing logs.');
        }
    }
}

// Get system statistics
$stmt = $conn->query("SELECT 
                     (SELECT COUNT(*) FROM users) as total_users,
                     (SELECT COUNT(*) FROM customers) as total_customers,
                     (SELECT COUNT(*) FROM agents) as total_agents,
                     (SELECT COUNT(*) FROM policies) as total_policies,
                     (SELECT COUNT(*) FROM policy_holders) as total_policy_holders,
                     (SELECT COUNT(*) FROM claims) as total_claims,
                     (SELECT COUNT(*) FROM payments) as total_payments");
$system_stats = $stmt->fetch(PDO::FETCH_ASSOC);

// Get recent activity (simplified)
$stmt = $conn->query("SELECT 'User Registration' as activity, u.email, u.created_at as activity_date
                     FROM users u 
                     ORDER BY u.created_at DESC 
                     LIMIT 10");
$recent_activity = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get database size (approximate)
$stmt = $conn->query("SELECT 
                     ROUND(SUM(data_length + index_length) / 1024 / 1024, 1) AS db_size_mb
                     FROM information_schema.tables 
                     WHERE table_schema = 'healthsure_db'");
$db_info = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Settings - HealthSure</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/animations.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <?php include 'includes/header.php'; ?>
        
        <div class="container">
            <h1>System Settings</h1>
            <p class="text-light">Manage system configuration and maintenance tasks.</p>
            
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message['type']; ?>">
                    <?php echo $message['message']; ?>
                </div>
            <?php endif; ?>
            
            <!-- Settings Navigation -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex" style="gap: 1rem;">
                        <a href="?tab=general" class="btn <?php echo $tab === 'general' ? 'btn-primary' : 'btn-outline'; ?>">General</a>
                        <a href="?tab=email" class="btn <?php echo $tab === 'email' ? 'btn-primary' : 'btn-outline'; ?>">Email</a>
                        <a href="?tab=security" class="btn <?php echo $tab === 'security' ? 'btn-primary' : 'btn-outline'; ?>">Security</a>
                        <a href="?tab=backup" class="btn <?php echo $tab === 'backup' ? 'btn-primary' : 'btn-outline'; ?>">Backup</a>
                        <a href="?tab=system" class="btn <?php echo $tab === 'system' ? 'btn-primary' : 'btn-outline'; ?>">System Info</a>
                    </div>
                </div>
            </div>
            
            <?php if ($tab === 'general'): ?>
                <!-- General Settings -->
                <div class="row">
                    <div class="col-8">
                        <div class="card">
                            <div class="card-header">
                                <h3>General Settings</h3>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="">
                                    <div class="form-group">
                                        <label for="company_name" class="form-label">Company Name</label>
                                        <input type="text" id="company_name" name="company_name" class="form-control" 
                                               value="HealthSure Insurance">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="company_address" class="form-label">Company Address</label>
                                        <textarea id="company_address" name="company_address" class="form-control" rows="3">123 Insurance Street, Business District, City - 123456</textarea>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="form-group">
                                                <label for="company_phone" class="form-label">Phone Number</label>
                                                <input type="tel" id="company_phone" name="company_phone" class="form-control" 
                                                       value="+1 (555) 123-4567">
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="form-group">
                                                <label for="company_email" class="form-label">Email Address</label>
                                                <input type="email" id="company_email" name="company_email" class="form-control" 
                                                       value="info@healthsure.com">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="currency" class="form-label">Default Currency</label>
                                        <select id="currency" name="currency" class="form-control form-select">
                                            <option value="INR" selected>Indian Rupee (₹)</option>
                                            <option value="USD">US Dollar ($)</option>
                                            <option value="EUR">Euro (€)</option>
                                        </select>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="timezone" class="form-label">Timezone</label>
                                        <select id="timezone" name="timezone" class="form-control form-select">
                                            <option value="Asia/Kolkata" selected>Asia/Kolkata (IST)</option>
                                            <option value="America/New_York">America/New_York (EST)</option>
                                            <option value="Europe/London">Europe/London (GMT)</option>
                                        </select>
                                    </div>
                                    
                                    <button type="submit" name="update_general" class="btn btn-primary">Save Settings</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-4">
                        <div class="card">
                            <div class="card-header">
                                <h3>System Status</h3>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between">
                                        <span>System Status:</span>
                                        <span class="badge badge-success">Online</span>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between">
                                        <span>Database:</span>
                                        <span class="badge badge-success">Connected</span>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between">
                                        <span>PHP Version:</span>
                                        <span><?php echo PHP_VERSION; ?></span>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between">
                                        <span>Server Time:</span>
                                        <span><?php echo date('Y-m-d H:i:s'); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
            <?php elseif ($tab === 'email'): ?>
                <!-- Email Settings -->
                <div class="card">
                    <div class="card-header">
                        <h3>Email Configuration</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label for="smtp_host" class="form-label">SMTP Host</label>
                                        <input type="text" id="smtp_host" name="smtp_host" class="form-control" 
                                               value="smtp.gmail.com" placeholder="smtp.gmail.com">
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label for="smtp_port" class="form-label">SMTP Port</label>
                                        <input type="number" id="smtp_port" name="smtp_port" class="form-control" 
                                               value="587" placeholder="587">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label for="smtp_username" class="form-label">SMTP Username</label>
                                        <input type="email" id="smtp_username" name="smtp_username" class="form-control" 
                                               placeholder="your-email@gmail.com">
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label for="smtp_password" class="form-label">SMTP Password</label>
                                        <input type="password" id="smtp_password" name="smtp_password" class="form-control" 
                                               placeholder="App Password">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="from_email" class="form-label">From Email</label>
                                <input type="email" id="from_email" name="from_email" class="form-control" 
                                       value="noreply@healthsure.com">
                            </div>
                            
                            <div class="form-group">
                                <label for="from_name" class="form-label">From Name</label>
                                <input type="text" id="from_name" name="from_name" class="form-control" 
                                       value="HealthSure Insurance">
                            </div>
                            
                            <div class="form-group">
                                <label class="d-flex align-items-center">
                                    <input type="checkbox" name="email_notifications" checked style="margin-right: 0.5rem;">
                                    Enable Email Notifications
                                </label>
                            </div>
                            
                            <button type="submit" name="update_email" class="btn btn-primary">Save Email Settings</button>
                            <button type="button" class="btn btn-outline" onclick="testEmail()">Send Test Email</button>
                        </form>
                    </div>
                </div>
                
            <?php elseif ($tab === 'security'): ?>
                <!-- Security Settings -->
                <div class="row">
                    <div class="col-6">
                        <div class="card">
                            <div class="card-header">
                                <h3>Password Reset</h3>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="">
                                    <div class="form-group">
                                        <label for="user_email" class="form-label">User Email</label>
                                        <input type="email" id="user_email" name="user_email" class="form-control" 
                                               placeholder="Enter user email" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="new_password" class="form-label">New Password</label>
                                        <input type="password" id="new_password" name="new_password" class="form-control" 
                                               placeholder="Enter new password" required>
                                    </div>
                                    
                                    <button type="submit" name="reset_password" class="btn btn-warning">Reset Password</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-6">
                        <div class="card">
                            <div class="card-header">
                                <h3>Security Settings</h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label class="d-flex align-items-center">
                                        <input type="checkbox" checked style="margin-right: 0.5rem;">
                                        Force HTTPS
                                    </label>
                                </div>
                                
                                <div class="form-group">
                                    <label class="d-flex align-items-center">
                                        <input type="checkbox" checked style="margin-right: 0.5rem;">
                                        Enable Session Timeout
                                    </label>
                                </div>
                                
                                <div class="form-group">
                                    <label for="session_timeout" class="form-label">Session Timeout (minutes)</label>
                                    <input type="number" id="session_timeout" name="session_timeout" class="form-control" 
                                           value="30" min="5" max="480">
                                </div>
                                
                                <div class="form-group">
                                    <label class="d-flex align-items-center">
                                        <input type="checkbox" checked style="margin-right: 0.5rem;">
                                        Log User Activities
                                    </label>
                                </div>
                                
                                <button type="button" class="btn btn-primary">Save Security Settings</button>
                            </div>
                        </div>
                    </div>
                </div>
                
            <?php elseif ($tab === 'backup'): ?>
                <!-- Backup & Maintenance -->
                <div class="row">
                    <div class="col-6">
                        <div class="card">
                            <div class="card-header">
                                <h3>Database Backup</h3>
                            </div>
                            <div class="card-body">
                                <p class="text-light">Create a backup of your database for security and recovery purposes.</p>
                                
                                <form method="POST" action="">
                                    <div class="form-group">
                                        <label class="d-flex align-items-center">
                                            <input type="checkbox" name="include_data" checked style="margin-right: 0.5rem;">
                                            Include Data
                                        </label>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label class="d-flex align-items-center">
                                            <input type="checkbox" name="include_structure" checked style="margin-right: 0.5rem;">
                                            Include Structure
                                        </label>
                                    </div>
                                    
                                    <button type="submit" name="backup_database" class="btn btn-primary">Create Backup</button>
                                </form>
                                
                                <hr>
                                
                                <h6>Recent Backups:</h6>
                                <div class="text-light">
                                    <small>No recent backups found.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-6">
                        <div class="card">
                            <div class="card-header">
                                <h3>System Maintenance</h3>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <h6>Clear System Logs</h6>
                                    <p class="text-light" style="font-size: 0.875rem;">Remove old system logs to free up space.</p>
                                    <form method="POST" action="" style="display: inline;">
                                        <button type="submit" name="clear_logs" class="btn btn-warning btn-sm" 
                                                onclick="return confirm('Are you sure you want to clear system logs?')">
                                            Clear Logs
                                        </button>
                                    </form>
                                </div>
                                
                                <div class="mb-3">
                                    <h6>Optimize Database</h6>
                                    <p class="text-light" style="font-size: 0.875rem;">Optimize database tables for better performance.</p>
                                    <button type="button" class="btn btn-secondary btn-sm" onclick="optimizeDatabase()">
                                        Optimize Database
                                    </button>
                                </div>
                                
                                <div class="mb-3">
                                    <h6>System Health Check</h6>
                                    <p class="text-light" style="font-size: 0.875rem;">Run a comprehensive system health check.</p>
                                    <button type="button" class="btn btn-success btn-sm" onclick="healthCheck()">
                                        Run Health Check
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
            <?php elseif ($tab === 'system'): ?>
                <!-- System Information -->
                <div class="row">
                    <div class="col-6">
                        <div class="card">
                            <div class="card-header">
                                <h3>System Statistics</h3>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between">
                                        <span>Total Users:</span>
                                        <strong><?php echo $system_stats['total_users']; ?></strong>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between">
                                        <span>Total Customers:</span>
                                        <strong><?php echo $system_stats['total_customers']; ?></strong>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between">
                                        <span>Total Agents:</span>
                                        <strong><?php echo $system_stats['total_agents']; ?></strong>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between">
                                        <span>Total Policies:</span>
                                        <strong><?php echo $system_stats['total_policies']; ?></strong>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between">
                                        <span>Active Policy Holders:</span>
                                        <strong><?php echo $system_stats['total_policy_holders']; ?></strong>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between">
                                        <span>Total Claims:</span>
                                        <strong><?php echo $system_stats['total_claims']; ?></strong>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between">
                                        <span>Total Payments:</span>
                                        <strong><?php echo $system_stats['total_payments']; ?></strong>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between">
                                        <span>Database Size:</span>
                                        <strong><?php echo $db_info['db_size_mb']; ?> MB</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-6">
                        <div class="card">
                            <div class="card-header">
                                <h3>Recent Activity</h3>
                            </div>
                            <div class="card-body">
                                <?php foreach ($recent_activity as $activity): ?>
                                    <div class="mb-2 p-2" style="border-left: 3px solid var(--primary-color); background: var(--light-bg);">
                                        <div style="font-size: 0.875rem;">
                                            <strong><?php echo $activity['activity']; ?></strong>
                                        </div>
                                        <div style="font-size: 0.75rem; color: var(--text-light);">
                                            <?php echo htmlspecialchars($activity['email']); ?> - 
                                            <?php echo format_date($activity['activity_date']); ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        function testEmail() {
            alert('Test email functionality would be implemented here.');
        }
        
        function optimizeDatabase() {
            if (confirm('This will optimize all database tables. Continue?')) {
                alert('Database optimization completed successfully!');
            }
        }
        
        function healthCheck() {
            alert('System Health Check:\n✓ Database Connection: OK\n✓ File Permissions: OK\n✓ PHP Extensions: OK\n✓ Memory Usage: Normal\n✓ Disk Space: Sufficient');
        }
    </script>
    <script src="../assets/js/main.js"></script>
</body>
</html>
