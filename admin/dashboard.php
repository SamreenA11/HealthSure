<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

check_role('admin');

// Get dashboard statistics
try {
    // Total counts
    $stmt = $conn->query("SELECT COUNT(*) as total_customers FROM customers");
    $total_customers = $stmt->fetch()['total_customers'];
    
    $stmt = $conn->query("SELECT COUNT(*) as total_agents FROM agents");
    $total_agents = $stmt->fetch()['total_agents'];
    
    $stmt = $conn->query("SELECT COUNT(*) as total_policies FROM policies WHERE status = 'active'");
    $total_policies = $stmt->fetch()['total_policies'];
    
    $stmt = $conn->query("SELECT COUNT(*) as active_policy_holders FROM policy_holders WHERE status = 'active'");
    $active_policy_holders = $stmt->fetch()['active_policy_holders'];
    
    $stmt = $conn->query("SELECT COUNT(*) as pending_claims FROM claims WHERE status = 'pending'");
    $pending_claims = $stmt->fetch()['pending_claims'];
    
    // Premium collection this month
    $stmt = $conn->query("SELECT COALESCE(SUM(amount), 0) as monthly_premium 
                         FROM payments 
                         WHERE payment_type = 'premium' 
                         AND MONTH(payment_date) = MONTH(CURRENT_DATE()) 
                         AND YEAR(payment_date) = YEAR(CURRENT_DATE())");
    $monthly_premium = $stmt->fetch()['monthly_premium'];
    
    // Recent activities
    $stmt = $conn->query("SELECT c.*, cu.first_name, cu.last_name 
                         FROM claims c 
                         JOIN policy_holders ph ON c.holder_id = ph.holder_id 
                         JOIN customers cu ON ph.customer_id = cu.customer_id 
                         ORDER BY c.created_at DESC 
                         LIMIT 5");
    $recent_claims = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Policy distribution
    $stmt = $conn->query("SELECT policy_type, COUNT(*) as count 
                         FROM policies 
                         WHERE status = 'active' 
                         GROUP BY policy_type");
    $policy_distribution = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $error = "Database error occurred";
    // Set default values if database queries fail
    $total_customers = 0;
    $total_agents = 0;
    $total_policies = 0;
    $active_policy_holders = 0;
    $pending_claims = 0;
    $monthly_premium = 0;
    $recent_claims = [];
    $policy_distribution = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - HealthSure</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/animations.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <?php include 'includes/header.php'; ?>
        
        <div class="container">
            <h1>Admin Dashboard</h1>
            <p class="text-light">Welcome back! Here's what's happening with your insurance system.</p>
            
            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?php echo number_format($total_customers); ?></div>
                    <div class="stat-label">Total Customers</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo number_format($total_agents); ?></div>
                    <div class="stat-label">Total Agents</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo number_format($active_policy_holders); ?></div>
                    <div class="stat-label">Active Policies</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo number_format($pending_claims); ?></div>
                    <div class="stat-label">Pending Claims</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo format_currency($monthly_premium); ?></div>
                    <div class="stat-label">Monthly Premium</div>
                </div>
            </div>
            
            <div class="row">
                <!-- Recent Claims -->
                <div class="col-8">
                    <div class="card">
                        <div class="card-header">
                            <h3>Recent Claims</h3>
                        </div>
                        <div class="card-body">
                            <?php if (empty($recent_claims)): ?>
                                <p class="text-light">No recent claims found.</p>
                            <?php else: ?>
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Claim ID</th>
                                            <th>Customer</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recent_claims as $claim): ?>
                                            <tr>
                                                <td>#<?php echo $claim['claim_id']; ?></td>
                                                <td><?php echo htmlspecialchars($claim['first_name'] . ' ' . $claim['last_name']); ?></td>
                                                <td><?php echo format_currency($claim['claim_amount']); ?></td>
                                                <td>
                                                    <span class="badge badge-<?php echo $claim['status'] === 'approved' ? 'success' : ($claim['status'] === 'rejected' ? 'danger' : 'warning'); ?>">
                                                        <?php echo ucfirst($claim['status']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo format_date($claim['claim_date']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php endif; ?>
                        </div>
                        <div class="card-footer">
                            <a href="claims.php" class="btn btn-primary btn-sm">View All Claims</a>
                        </div>
                    </div>
                </div>
                
                <!-- Policy Distribution -->
                <div class="col-4">
                    <div class="card">
                        <div class="card-header">
                            <h3>Policy Distribution</h3>
                        </div>
                        <div class="card-body">
                            <?php if (empty($policy_distribution)): ?>
                                <p class="text-light">No policies found.</p>
                            <?php else: ?>
                                <?php foreach ($policy_distribution as $policy): ?>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span><?php echo ucfirst($policy['policy_type']); ?> Insurance</span>
                                        <span class="badge badge-primary"><?php echo $policy['count']; ?></span>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <div class="card-footer">
                            <a href="policies.php" class="btn btn-primary btn-sm">Manage Policies</a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="card mt-4">
                <div class="card-header">
                    <h3>Quick Actions</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-3">
                            <a href="policies.php?action=add" class="btn btn-primary" style="width: 100%;">Add New Policy</a>
                        </div>
                        <div class="col-3">
                            <a href="agents.php?action=add" class="btn btn-success" style="width: 100%;">Register Agent</a>
                        </div>
                        <div class="col-3">
                            <a href="claims.php?status=pending" class="btn btn-warning" style="width: 100%;">Review Claims</a>
                        </div>
                        <div class="col-3">
                            <a href="reports.php" class="btn btn-secondary" style="width: 100%;">Generate Reports</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="../assets/js/main.js"></script>
</body>
</html>
