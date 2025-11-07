<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

check_role('customer');

$customer_info = get_customer_info($_SESSION['user_id'], $conn);

// Get customer's policies
$stmt = $conn->prepare("SELECT ph.*, p.policy_name, p.policy_type, p.coverage_amount, p.duration_years 
                       FROM policy_holders ph 
                       JOIN policies p ON ph.policy_id = p.policy_id 
                       WHERE ph.customer_id = ? 
                       ORDER BY ph.created_at DESC");
$stmt->execute([$customer_info['customer_id']]);
$my_policies = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get recent claims
$stmt = $conn->prepare("SELECT c.*, p.policy_name 
                       FROM claims c 
                       JOIN policy_holders ph ON c.holder_id = ph.holder_id 
                       JOIN policies p ON ph.policy_id = p.policy_id 
                       WHERE ph.customer_id = ? 
                       ORDER BY c.created_at DESC 
                       LIMIT 5");
$stmt->execute([$customer_info['customer_id']]);
$recent_claims = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get payment history
$stmt = $conn->prepare("SELECT py.*, p.policy_name 
                       FROM payments py 
                       JOIN policy_holders ph ON py.holder_id = ph.holder_id 
                       JOIN policies p ON ph.policy_id = p.policy_id 
                       WHERE ph.customer_id = ? 
                       ORDER BY py.created_at DESC 
                       LIMIT 5");
$stmt->execute([$customer_info['customer_id']]);
$recent_payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate statistics
$active_policies = count(array_filter($my_policies, function($p) { return $p['status'] === 'active'; }));
$total_coverage = array_sum(array_column($my_policies, 'coverage_amount'));
$pending_claims = count(array_filter($recent_claims, function($c) { return $c['status'] === 'pending'; }));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard - HealthSure</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/animations.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <?php include 'includes/header.php'; ?>
        
        <div class="container">
            <h1>Welcome, <?php echo htmlspecialchars($customer_info['first_name']); ?>!</h1>
            <p class="text-light">Manage your insurance policies and claims from your dashboard.</p>
            
            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $active_policies; ?></div>
                    <div class="stat-label">Active Policies</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo format_currency($total_coverage); ?></div>
                    <div class="stat-label">Total Coverage</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $pending_claims; ?></div>
                    <div class="stat-label">Pending Claims</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo count($recent_payments); ?></div>
                    <div class="stat-label">Recent Payments</div>
                </div>
            </div>
            
            <div class="row">
                <!-- My Policies -->
                <div class="col-6">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h3>My Policies</h3>
                            <a href="browse-policies.php" class="btn btn-primary btn-sm">Browse Policies</a>
                        </div>
                        <div class="card-body">
                            <?php if (empty($my_policies)): ?>
                                <p class="text-light">You don't have any policies yet.</p>
                                <a href="browse-policies.php" class="btn btn-primary">Get Your First Policy</a>
                            <?php else: ?>
                                <?php foreach (array_slice($my_policies, 0, 3) as $policy): ?>
                                    <div class="d-flex justify-content-between align-items-center mb-3 p-3" style="border: 1px solid var(--border-color); border-radius: 0.375rem;">
                                        <div>
                                            <h5 style="margin: 0;"><?php echo htmlspecialchars($policy['policy_name']); ?></h5>
                                            <p style="margin: 0; color: var(--text-light); font-size: 0.875rem;">
                                                Coverage: <?php echo format_currency($policy['coverage_amount']); ?>
                                            </p>
                                        </div>
                                        <div class="text-right">
                                            <span class="badge badge-<?php echo $policy['status'] === 'active' ? 'success' : 'warning'; ?>">
                                                <?php echo ucfirst($policy['status']); ?>
                                            </span>
                                            <p style="margin: 0; font-size: 0.875rem; color: var(--text-light);">
                                                Expires: <?php echo format_date($policy['end_date']); ?>
                                            </p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <div class="card-footer">
                            <a href="my-policies.php" class="btn btn-outline btn-sm">View All Policies</a>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Claims -->
                <div class="col-6">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h3>Recent Claims</h3>
                            <a href="file-claim.php" class="btn btn-success btn-sm">File New Claim</a>
                        </div>
                        <div class="card-body">
                            <?php if (empty($recent_claims)): ?>
                                <p class="text-light">No claims filed yet.</p>
                            <?php else: ?>
                                <?php foreach (array_slice($recent_claims, 0, 3) as $claim): ?>
                                    <div class="d-flex justify-content-between align-items-center mb-3 p-3" style="border: 1px solid var(--border-color); border-radius: 0.375rem;">
                                        <div>
                                            <h6 style="margin: 0;">Claim #<?php echo $claim['claim_id']; ?></h6>
                                            <p style="margin: 0; color: var(--text-light); font-size: 0.875rem;">
                                                <?php echo htmlspecialchars($claim['policy_name']); ?>
                                            </p>
                                        </div>
                                        <div class="text-right">
                                            <span class="badge badge-<?php echo $claim['status'] === 'approved' ? 'success' : ($claim['status'] === 'rejected' ? 'danger' : 'warning'); ?>">
                                                <?php echo ucfirst($claim['status']); ?>
                                            </span>
                                            <p style="margin: 0; font-size: 0.875rem; color: var(--text-light);">
                                                <?php echo format_currency($claim['claim_amount']); ?>
                                            </p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <div class="card-footer">
                            <a href="my-claims.php" class="btn btn-outline btn-sm">View All Claims</a>
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
                            <a href="browse-policies.php" class="btn btn-primary" style="width: 100%;">
                                📋 Browse Policies
                            </a>
                        </div>
                        <div class="col-3">
                            <a href="file-claim.php" class="btn btn-success" style="width: 100%;">
                                📄 File New Claim
                            </a>
                        </div>
                        <div class="col-3">
                            <a href="make-payment.php" class="btn btn-warning" style="width: 100%;">
                                💳 Make Payment
                            </a>
                        </div>
                        <div class="col-3">
                            <a href="support.php" class="btn btn-secondary" style="width: 100%;">
                                🎧 Contact Support
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Recent Payments -->
            <?php if (!empty($recent_payments)): ?>
            <div class="card mt-4">
                <div class="card-header">
                    <h3>Recent Payments</h3>
                </div>
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Policy</th>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_payments as $payment): ?>
                                <tr>
                                    <td><?php echo format_date($payment['payment_date']); ?></td>
                                    <td><?php echo htmlspecialchars($payment['policy_name']); ?></td>
                                    <td><?php echo ucfirst(str_replace('_', ' ', $payment['payment_type'])); ?></td>
                                    <td><?php echo format_currency($payment['amount']); ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo $payment['status'] === 'completed' ? 'success' : 'warning'; ?>">
                                            <?php echo ucfirst($payment['status']); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">
                    <a href="payment-history.php" class="btn btn-outline btn-sm">View Payment History</a>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="../assets/js/main.js"></script>
</body>
</html>
