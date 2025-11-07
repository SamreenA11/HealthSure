<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

check_role('agent');

$agent_info = get_agent_info($_SESSION['user_id'], $conn);
$error = '';
$success = '';

if ($_POST) {
    $first_name = sanitize_input($_POST['first_name']);
    $last_name = sanitize_input($_POST['last_name']);
    $phone = sanitize_input($_POST['phone']);
    $branch = sanitize_input($_POST['branch']);
    $license_number = sanitize_input($_POST['license_number']);
    
    if (empty($first_name) || empty($last_name)) {
        $error = 'First name and last name are required';
    } else {
        try {
            $stmt = $conn->prepare("UPDATE agents SET first_name = ?, last_name = ?, phone = ?, branch = ?, license_number = ? WHERE agent_id = ?");
            $stmt->execute([$first_name, $last_name, $phone, $branch, $license_number, $agent_info['agent_id']]);
            
            $success = 'Profile updated successfully!';
            
            // Refresh agent info
            $agent_info = get_agent_info($_SESSION['user_id'], $conn);
        } catch (PDOException $e) {
            $error = 'Failed to update profile. Please try again.';
        }
    }
}

// Get agent performance statistics
try {
    $stmt = $conn->prepare("SELECT 
                           COUNT(DISTINCT c.customer_id) as total_customers,
                           COUNT(DISTINCT ph.holder_id) as total_policies,
                           COALESCE(SUM(py.amount), 0) as total_premiums,
                           COUNT(DISTINCT cl.claim_id) as total_claims
                           FROM customers c
                           LEFT JOIN policy_holders ph ON c.customer_id = ph.customer_id
                           LEFT JOIN payments py ON ph.holder_id = py.holder_id AND py.payment_type = 'premium'
                           LEFT JOIN claims cl ON ph.holder_id = cl.holder_id
                           WHERE c.agent_id = ?");
    $stmt->execute([$agent_info['agent_id']]);
    $performance_stats = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $performance_stats = ['total_customers' => 0, 'total_policies' => 0, 'total_premiums' => 0, 'total_claims' => 0];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - HealthSure</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/animations.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <?php include 'includes/header.php'; ?>
        
        <div class="container">
            <h1>My Profile</h1>
            <p class="text-light">Manage your agent profile information and view your performance statistics.</p>
            
            <div class="row">
                <div class="col-8">
                    <div class="card">
                        <div class="card-header">
                            <h3>Agent Information</h3>
                        </div>
                        <div class="card-body">
                            <?php if ($error): ?>
                                <div class="alert alert-danger"><?php echo $error; ?></div>
                            <?php endif; ?>
                            
                            <?php if ($success): ?>
                                <div class="alert alert-success"><?php echo $success; ?></div>
                            <?php endif; ?>
                            
                            <form method="POST" action="">
                                <div class="row">
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label for="first_name" class="form-label">First Name *</label>
                                            <input type="text" id="first_name" name="first_name" class="form-control" 
                                                   value="<?php echo htmlspecialchars($agent_info['first_name']); ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label for="last_name" class="form-label">Last Name *</label>
                                            <input type="text" id="last_name" name="last_name" class="form-control" 
                                                   value="<?php echo htmlspecialchars($agent_info['last_name']); ?>" required>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" id="email" class="form-control" 
                                           value="<?php echo htmlspecialchars($agent_info['email']); ?>" disabled>
                                    <small class="text-light">Email cannot be changed. Contact admin if needed.</small>
                                </div>
                                
                                <div class="row">
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label for="phone" class="form-label">Phone Number</label>
                                            <input type="tel" id="phone" name="phone" class="form-control" 
                                                   value="<?php echo htmlspecialchars($agent_info['phone'] ?? ''); ?>">
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label for="branch" class="form-label">Branch</label>
                                            <input type="text" id="branch" name="branch" class="form-control" 
                                                   value="<?php echo htmlspecialchars($agent_info['branch'] ?? ''); ?>">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="license_number" class="form-label">License Number</label>
                                    <input type="text" id="license_number" name="license_number" class="form-control" 
                                           value="<?php echo htmlspecialchars($agent_info['license_number'] ?? ''); ?>">
                                </div>
                                
                                <button type="submit" class="btn btn-primary">Update Profile</button>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Performance Overview -->
                    <div class="card">
                        <div class="card-header">
                            <h3>My Performance Overview</h3>
                        </div>
                        <div class="card-body">
                            <div class="stats-grid">
                                <div class="stat-card">
                                    <div class="stat-number"><?php echo $performance_stats['total_customers']; ?></div>
                                    <div class="stat-label">Total Customers</div>
                                </div>
                                <div class="stat-card">
                                    <div class="stat-number"><?php echo $performance_stats['total_policies']; ?></div>
                                    <div class="stat-label">Policies Sold</div>
                                </div>
                                <div class="stat-card">
                                    <div class="stat-number"><?php echo format_currency($performance_stats['total_premiums']); ?></div>
                                    <div class="stat-label">Premium Collected</div>
                                </div>
                                <div class="stat-card">
                                    <div class="stat-number"><?php echo $performance_stats['total_claims']; ?></div>
                                    <div class="stat-label">Claims Handled</div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <a href="reports.php" class="btn btn-outline btn-sm">View Detailed Reports</a>
                        </div>
                    </div>
                </div>
                
                <div class="col-4">
                    <div class="card">
                        <div class="card-header">
                            <h3>Account Information</h3>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <strong>Agent ID:</strong><br>
                                #<?php echo $agent_info['agent_id']; ?>
                            </div>
                            <div class="mb-3">
                                <strong>Account Status:</strong><br>
                                <span class="badge badge-success">Active</span>
                            </div>
                            <div class="mb-3">
                                <strong>Joined Date:</strong><br>
                                <?php echo format_date($agent_info['created_at']); ?>
                            </div>
                            <?php if ($agent_info['hire_date']): ?>
                                <div class="mb-3">
                                    <strong>Hire Date:</strong><br>
                                    <?php echo format_date($agent_info['hire_date']); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="card mt-4">
                        <div class="card-header">
                            <h3>Security</h3>
                        </div>
                        <div class="card-body">
                            <p style="font-size: 0.875rem; color: var(--text-light);">
                                To change your password or update security settings, please contact your administrator.
                            </p>
                            <button class="btn btn-secondary btn-sm" onclick="alert('Contact Admin: admin@healthsure.com')">
                                Contact Admin
                            </button>
                        </div>
                    </div>
                    
                    <div class="card mt-4">
                        <div class="card-header">
                            <h3>Quick Actions</h3>
                        </div>
                        <div class="card-body">
                            <div class="d-flex flex-column" style="gap: 0.5rem;">
                                <a href="customers.php" class="btn btn-outline btn-sm">👥 View My Customers</a>
                                <a href="reports.php" class="btn btn-outline btn-sm">📊 Performance Reports</a>
                                <a href="../customer/browse-policies.php" class="btn btn-outline btn-sm">📋 Browse Policies</a>
                                <a href="support.php" class="btn btn-outline btn-sm">🎧 Contact Support</a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card mt-4">
                        <div class="card-header">
                            <h3>Agent Resources</h3>
                        </div>
                        <div class="card-body">
                            <h6>Helpful Links:</h6>
                            <ul style="font-size: 0.875rem; padding-left: 1rem;">
                                <li><a href="#" onclick="alert('Feature coming soon!')">Agent Training Materials</a></li>
                                <li><a href="#" onclick="alert('Feature coming soon!')">Sales Guidelines</a></li>
                                <li><a href="#" onclick="alert('Feature coming soon!')">Commission Structure</a></li>
                                <li><a href="#" onclick="alert('Feature coming soon!')">Policy Documents</a></li>
                            </ul>
                            
                            <h6 class="mt-3">Contact Information:</h6>
                            <ul style="font-size: 0.875rem; padding-left: 1rem;">
                                <li><strong>Admin:</strong> admin@healthsure.com</li>
                                <li><strong>Support:</strong> +91-1800-123-4567</li>
                                <li><strong>Emergency:</strong> +91-1800-EMERGENCY</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/main.js"></script>
</body>
</html>
