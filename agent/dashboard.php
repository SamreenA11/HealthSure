<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

check_role('agent');

$agent_info = get_agent_info($_SESSION['user_id'], $conn);

// Get agent's statistics
try {
    // Total customers assigned to this agent
    $stmt = $conn->prepare("SELECT COUNT(*) as total_customers FROM customers WHERE agent_id = ?");
    $stmt->execute([$agent_info['agent_id']]);
    $total_customers = $stmt->fetch()['total_customers'];
    
    // Active policies sold by this agent
    $stmt = $conn->prepare("SELECT COUNT(*) as active_policies FROM policy_holders ph 
                           JOIN customers c ON ph.customer_id = c.customer_id 
                           WHERE c.agent_id = ? AND ph.status = 'active'");
    $stmt->execute([$agent_info['agent_id']]);
    $active_policies = $stmt->fetch()['active_policies'];
    
    // Total premium collected
    $stmt = $conn->prepare("SELECT COALESCE(SUM(py.amount), 0) as total_premiums 
                           FROM payments py 
                           JOIN policy_holders ph ON py.holder_id = ph.holder_id 
                           JOIN customers c ON ph.customer_id = c.customer_id 
                           WHERE c.agent_id = ? AND py.payment_type = 'premium'");
    $stmt->execute([$agent_info['agent_id']]);
    $total_premiums = $stmt->fetch()['total_premiums'];
    
    // Claims handled
    $stmt = $conn->prepare("SELECT COUNT(*) as total_claims FROM claims cl 
                           JOIN policy_holders ph ON cl.holder_id = ph.holder_id 
                           JOIN customers c ON ph.customer_id = c.customer_id 
                           WHERE c.agent_id = ?");
    $stmt->execute([$agent_info['agent_id']]);
    $total_claims = $stmt->fetch()['total_claims'];
    
    // Recent customers
    $stmt = $conn->prepare("SELECT c.*, u.email FROM customers c 
                           JOIN users u ON c.user_id = u.user_id 
                           WHERE c.agent_id = ? 
                           ORDER BY c.created_at DESC LIMIT 5");
    $stmt->execute([$agent_info['agent_id']]);
    $recent_customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $total_customers = 0;
    $active_policies = 0;
    $total_premiums = 0;
    $total_claims = 0;
    $recent_customers = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agent Dashboard - HealthSure</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/animations.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Agent Dashboard Specific Styles */
        .quick-actions-btn {
            width: 100%;
            padding: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            font-weight: 600;
            border-radius: var(--border-radius);
            transition: all 0.3s ease;
            text-decoration: none;
            min-height: 60px;
        }
        
        .quick-actions-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }
        
        .quick-actions-btn span:first-child {
            font-size: 1.25rem;
        }
        
        .agent-avatar {
            width: 80px;
            height: 80px;
            background: var(--primary-color);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin: 0 auto 1rem;
            font-weight: bold;
            box-shadow: var(--shadow);
        }
        
        /* Responsive Design for Quick Actions */
        @media (max-width: 768px) {
            .quick-actions-row .col-3 {
                flex: 0 0 50%;
                max-width: 50%;
                margin-bottom: 1rem;
            }
            
            .quick-actions-btn {
                padding: 0.75rem;
                font-size: 0.875rem;
                min-height: 50px;
            }
            
            .quick-actions-btn span:first-child {
                font-size: 1rem;
            }
        }
        
        @media (max-width: 480px) {
            .quick-actions-row .col-3 {
                flex: 0 0 100%;
                max-width: 100%;
            }
            
            .quick-actions-btn {
                padding: 1rem;
                font-size: 1rem;
            }
        }
        
        /* Performance Summary Styling */
        .performance-item {
            display: flex;
            justify-content: between;
            align-items: center;
            padding: 0.5rem 0;
            border-bottom: 1px solid var(--border-color);
        }
        
        .performance-item:last-child {
            border-bottom: none;
        }
        
        /* Enhanced Table Styling */
        .table-hover tbody tr:hover {
            background-color: var(--light-bg);
            cursor: pointer;
        }
        
        /* Empty State Styling */
        .empty-state {
            text-align: center;
            padding: 3rem 2rem;
        }
        
        .empty-state-icon {
            font-size: 4rem;
            opacity: 0.3;
            margin-bottom: 1rem;
            display: block;
        }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <?php include 'includes/header.php'; ?>
        
        <div class="container">
            <h1>Welcome, <?php echo htmlspecialchars($agent_info['first_name']); ?>!</h1>
            <p class="text-light">Manage your customers and track your sales performance.</p>
            
            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $total_customers; ?></div>
                    <div class="stat-label">My Customers</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $active_policies; ?></div>
                    <div class="stat-label">Active Policies</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo format_currency($total_premiums); ?></div>
                    <div class="stat-label">Premium Collected</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $total_claims; ?></div>
                    <div class="stat-label">Claims Handled</div>
                </div>
            </div>
            
            <!-- Quick Actions Section - Full Width -->
            <div class="card mb-4">
                <div class="card-header">
                    <h3>Quick Actions</h3>
                </div>
                <div class="card-body">
                    <div class="row quick-actions-row">
                        <div class="col-3">
                            <a href="customers.php" class="btn btn-primary quick-actions-btn">
                                <span>👥</span>
                                <span>Manage Customers</span>
                            </a>
                        </div>
                        <div class="col-3">
                            <a href="../customer/browse-policies.php" class="btn btn-success quick-actions-btn">
                                <span>📋</span>
                                <span>View Policies</span>
                            </a>
                        </div>
                        <div class="col-3">
                            <a href="reports.php" class="btn btn-info quick-actions-btn">
                                <span>📊</span>
                                <span>My Performance</span>
                            </a>
                        </div>
                        <div class="col-3">
                            <a href="support.php" class="btn btn-warning quick-actions-btn">
                                <span>🎧</span>
                                <span>Contact Support</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Recent Customers -->
                <div class="col-8">
                    <div class="card">
                        <div class="card-header">
                            <h3>My Recent Customers</h3>
                        </div>
                        <div class="card-body">
                            <?php if (empty($recent_customers)): ?>
                                <div class="empty-state">
                                    <span class="empty-state-icon">👥</span>
                                    <h5>No Customers Assigned</h5>
                                    <p class="text-light">You don't have any customers assigned yet. Contact your admin to get customer assignments.</p>
                                    <a href="support.php" class="btn btn-primary btn-sm">Contact Support</a>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Customer</th>
                                                <th>Email</th>
                                                <th>Phone</th>
                                                <th>Joined</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recent_customers as $customer): ?>
                                                <tr>
                                                    <td>
                                                        <strong><?php echo htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']); ?></strong><br>
                                                        <small class="text-light">ID: #<?php echo $customer['customer_id']; ?></small>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($customer['email']); ?></td>
                                                    <td><?php echo htmlspecialchars($customer['phone'] ?? 'Not provided'); ?></td>
                                                    <td><?php echo format_date($customer['created_at']); ?></td>
                                                    <td>
                                                        <a href="customers.php?action=view&customer_id=<?php echo $customer['customer_id']; ?>" class="btn btn-sm btn-outline">View</a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="card-footer">
                            <a href="customers.php" class="btn btn-primary btn-sm">View All Customers</a>
                        </div>
                    </div>
                </div>
                
                <!-- Agent Information -->
                <div class="col-4">
                    <div class="card">
                        <div class="card-header">
                            <h3>Agent Information</h3>
                        </div>
                        <div class="card-body">
                            <div class="text-center mb-3">
                                <div class="agent-avatar">
                                    <?php echo strtoupper(substr($agent_info['first_name'], 0, 1)); ?>
                                </div>
                                <h5><?php echo htmlspecialchars($agent_info['first_name'] . ' ' . $agent_info['last_name']); ?></h5>
                                <span class="badge badge-success">Active Agent</span>
                            </div>
                            
                            <div class="mb-3">
                                <strong>📧 Email:</strong><br>
                                <a href="mailto:<?php echo htmlspecialchars($agent_info['email']); ?>">
                                    <?php echo htmlspecialchars($agent_info['email']); ?>
                                </a>
                            </div>
                            
                            <?php if ($agent_info['branch']): ?>
                                <div class="mb-3">
                                    <strong>🏢 Branch:</strong><br>
                                    <?php echo htmlspecialchars($agent_info['branch']); ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($agent_info['license_number']): ?>
                                <div class="mb-3">
                                    <strong>📄 License:</strong><br>
                                    <?php echo htmlspecialchars($agent_info['license_number']); ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="mb-3">
                                <strong>📅 Joined:</strong><br>
                                <?php echo format_date($agent_info['created_at']); ?>
                            </div>
                        </div>
                        <div class="card-footer">
                            <a href="profile.php" class="btn btn-outline btn-sm" style="width: 100%;">Edit Profile</a>
                        </div>
                    </div>
                    
                    <!-- Performance Summary -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h3>Performance Summary</h3>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>Customers:</span>
                                    <strong style="color: var(--primary-color);"><?php echo $total_customers; ?></strong>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>Active Policies:</span>
                                    <strong style="color: var(--success-color);"><?php echo $active_policies; ?></strong>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>Claims Handled:</span>
                                    <strong style="color: var(--warning-color);"><?php echo $total_claims; ?></strong>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>Premium Collected:</span>
                                    <strong style="color: var(--success-color);"><?php echo format_currency($total_premiums); ?></strong>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <a href="reports.php" class="btn btn-primary btn-sm" style="width: 100%;">View Detailed Reports</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="../assets/js/main.js"></script>
</body>
</html>
