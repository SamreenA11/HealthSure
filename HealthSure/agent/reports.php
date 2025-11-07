<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

check_role('agent');

$agent_info = get_agent_info($_SESSION['user_id'], $conn);
$message = get_flash_message();
$report_type = $_GET['type'] ?? 'overview';
$date_from = $_GET['date_from'] ?? date('Y-m-01'); // First day of current month
$date_to = $_GET['date_to'] ?? date('Y-m-d'); // Today

// Get agent's performance statistics
try {
    // Overall statistics
    $stmt = $conn->prepare("SELECT 
                           COUNT(DISTINCT c.customer_id) as total_customers,
                           COUNT(DISTINCT ph.holder_id) as total_policies,
                           COALESCE(SUM(py.amount), 0) as total_premiums,
                           COUNT(DISTINCT cl.claim_id) as total_claims,
                           COUNT(DISTINCT CASE WHEN cl.status = 'approved' THEN cl.claim_id END) as approved_claims
                           FROM customers c
                           LEFT JOIN policy_holders ph ON c.customer_id = ph.customer_id
                           LEFT JOIN payments py ON ph.holder_id = py.holder_id AND py.payment_type = 'premium'
                           LEFT JOIN claims cl ON ph.holder_id = cl.holder_id
                           WHERE c.agent_id = ?");
    $stmt->execute([$agent_info['agent_id']]);
    $overall_stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Monthly performance
    $stmt = $conn->prepare("SELECT 
                           DATE_FORMAT(py.payment_date, '%Y-%m') as month,
                           COUNT(DISTINCT ph.customer_id) as customers,
                           COUNT(DISTINCT ph.holder_id) as policies,
                           SUM(py.amount) as premium_collected
                           FROM customers c
                           JOIN policy_holders ph ON c.customer_id = ph.customer_id
                           JOIN payments py ON ph.holder_id = py.holder_id
                           WHERE c.agent_id = ? AND py.payment_type = 'premium'
                           AND py.payment_date BETWEEN ? AND ?
                           GROUP BY DATE_FORMAT(py.payment_date, '%Y-%m')
                           ORDER BY month DESC");
    $stmt->execute([$agent_info['agent_id'], $date_from, $date_to]);
    $monthly_performance = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Policy type breakdown
    $stmt = $conn->prepare("SELECT 
                           p.policy_type,
                           COUNT(ph.holder_id) as policy_count,
                           SUM(ph.premium_amount) as total_premiums,
                           AVG(ph.premium_amount) as avg_premium
                           FROM customers c
                           JOIN policy_holders ph ON c.customer_id = ph.customer_id
                           JOIN policies p ON ph.policy_id = p.policy_id
                           WHERE c.agent_id = ?
                           GROUP BY p.policy_type
                           ORDER BY policy_count DESC");
    $stmt->execute([$agent_info['agent_id']]);
    $policy_breakdown = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Customer acquisition over time
    $stmt = $conn->prepare("SELECT 
                           DATE_FORMAT(c.created_at, '%Y-%m') as month,
                           COUNT(*) as new_customers
                           FROM customers c
                           WHERE c.agent_id = ? AND c.created_at BETWEEN ? AND ?
                           GROUP BY DATE_FORMAT(c.created_at, '%Y-%m')
                           ORDER BY month DESC");
    $stmt->execute([$agent_info['agent_id'], $date_from, $date_to]);
    $customer_acquisition = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Claims handled
    $stmt = $conn->prepare("SELECT 
                           cl.status,
                           COUNT(*) as claim_count,
                           AVG(cl.claim_amount) as avg_amount,
                           SUM(CASE WHEN cl.status = 'approved' THEN cl.approved_amount ELSE 0 END) as total_approved
                           FROM customers c
                           JOIN policy_holders ph ON c.customer_id = ph.customer_id
                           JOIN claims cl ON ph.holder_id = cl.holder_id
                           WHERE c.agent_id = ? AND cl.created_at BETWEEN ? AND ?
                           GROUP BY cl.status
                           ORDER BY claim_count DESC");
    $stmt->execute([$agent_info['agent_id'], $date_from, $date_to]);
    $claims_analysis = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Top customers by premium
    $stmt = $conn->prepare("SELECT 
                           c.customer_id, c.first_name, c.last_name,
                           COUNT(ph.holder_id) as policy_count,
                           SUM(py.amount) as total_premiums
                           FROM customers c
                           JOIN policy_holders ph ON c.customer_id = ph.customer_id
                           LEFT JOIN payments py ON ph.holder_id = py.holder_id AND py.payment_type = 'premium'
                           WHERE c.agent_id = ?
                           GROUP BY c.customer_id
                           HAVING total_premiums > 0
                           ORDER BY total_premiums DESC
                           LIMIT 10");
    $stmt->execute([$agent_info['agent_id']]);
    $top_customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $overall_stats = ['total_customers' => 0, 'total_policies' => 0, 'total_premiums' => 0, 'total_claims' => 0, 'approved_claims' => 0];
    $monthly_performance = [];
    $policy_breakdown = [];
    $customer_acquisition = [];
    $claims_analysis = [];
    $top_customers = [];
    set_flash_message('warning', 'Unable to load performance data.');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Performance Reports - HealthSure</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/animations.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <?php include 'includes/header.php'; ?>
        
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>My Performance Reports</h1>
                <button onclick="window.print()" class="btn btn-outline">Print Report</button>
            </div>
            
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message['type']; ?>">
                    <?php echo $message['message']; ?>
                </div>
            <?php endif; ?>
            
            <!-- Date Range Filter -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="d-flex align-items-center" style="gap: 1rem;">
                        <input type="hidden" name="type" value="<?php echo $report_type; ?>">
                        <div>
                            <label for="date_from" class="form-label">From Date:</label>
                            <input type="date" id="date_from" name="date_from" class="form-control" value="<?php echo $date_from; ?>">
                        </div>
                        <div>
                            <label for="date_to" class="form-label">To Date:</label>
                            <input type="date" id="date_to" name="date_to" class="form-control" value="<?php echo $date_to; ?>">
                        </div>
                        <div style="margin-top: 1.5rem;">
                            <button type="submit" class="btn btn-primary">Update Report</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Report Navigation -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex" style="gap: 1rem;">
                        <a href="?type=overview" class="btn <?php echo $report_type === 'overview' ? 'btn-primary' : 'btn-outline'; ?>">Overview</a>
                        <a href="?type=performance" class="btn <?php echo $report_type === 'performance' ? 'btn-primary' : 'btn-outline'; ?>">Performance</a>
                        <a href="?type=customers" class="btn <?php echo $report_type === 'customers' ? 'btn-primary' : 'btn-outline'; ?>">Customers</a>
                        <a href="?type=policies" class="btn <?php echo $report_type === 'policies' ? 'btn-primary' : 'btn-outline'; ?>">Policies</a>
                    </div>
                </div>
            </div>
            
            <?php if ($report_type === 'overview'): ?>
                <!-- Overview Report -->
                <div class="stats-grid mb-4">
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $overall_stats['total_customers']; ?></div>
                        <div class="stat-label">Total Customers</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $overall_stats['total_policies']; ?></div>
                        <div class="stat-label">Policies Sold</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo format_currency($overall_stats['total_premiums']); ?></div>
                        <div class="stat-label">Premium Collected</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $overall_stats['total_claims']; ?></div>
                        <div class="stat-label">Claims Handled</div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-6">
                        <div class="card">
                            <div class="card-header">
                                <h3>Agent Information</h3>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <strong>Name:</strong><br>
                                    <?php echo htmlspecialchars($agent_info['first_name'] . ' ' . $agent_info['last_name']); ?>
                                </div>
                                <div class="mb-3">
                                    <strong>Email:</strong><br>
                                    <?php echo htmlspecialchars($agent_info['email']); ?>
                                </div>
                                <?php if ($agent_info['branch']): ?>
                                    <div class="mb-3">
                                        <strong>Branch:</strong><br>
                                        <?php echo htmlspecialchars($agent_info['branch']); ?>
                                    </div>
                                <?php endif; ?>
                                <?php if ($agent_info['license_number']): ?>
                                    <div class="mb-3">
                                        <strong>License Number:</strong><br>
                                        <?php echo htmlspecialchars($agent_info['license_number']); ?>
                                    </div>
                                <?php endif; ?>
                                <div class="mb-3">
                                    <strong>Joined:</strong><br>
                                    <?php echo format_date($agent_info['created_at']); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-6">
                        <div class="card">
                            <div class="card-header">
                                <h3>Performance Summary</h3>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between">
                                        <span>Customers Served:</span>
                                        <strong><?php echo $overall_stats['total_customers']; ?></strong>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between">
                                        <span>Policies Sold:</span>
                                        <strong><?php echo $overall_stats['total_policies']; ?></strong>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between">
                                        <span>Claims Approved:</span>
                                        <strong style="color: var(--success-color);"><?php echo $overall_stats['approved_claims']; ?></strong>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between">
                                        <span>Avg Premium per Policy:</span>
                                        <strong>
                                            <?php 
                                            $avg_premium = $overall_stats['total_policies'] > 0 ? 
                                                $overall_stats['total_premiums'] / $overall_stats['total_policies'] : 0;
                                            echo format_currency($avg_premium);
                                            ?>
                                        </strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
            <?php elseif ($report_type === 'performance'): ?>
                <!-- Performance Report -->
                <div class="card">
                    <div class="card-header">
                        <h3>Monthly Performance</h3>
                    </div>
                    <div class="card-body">
                        <?php if (empty($monthly_performance)): ?>
                            <p class="text-light">No performance data available for the selected period.</p>
                        <?php else: ?>
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Month</th>
                                        <th>Customers</th>
                                        <th>Policies</th>
                                        <th>Premium Collected</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($monthly_performance as $month): ?>
                                        <tr>
                                            <td><?php echo date('M Y', strtotime($month['month'] . '-01')); ?></td>
                                            <td><?php echo $month['customers']; ?></td>
                                            <td><?php echo $month['policies']; ?></td>
                                            <td><?php echo format_currency($month['premium_collected']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Claims Analysis -->
                <div class="card">
                    <div class="card-header">
                        <h3>Claims Analysis</h3>
                    </div>
                    <div class="card-body">
                        <?php if (empty($claims_analysis)): ?>
                            <p class="text-light">No claims data available for the selected period.</p>
                        <?php else: ?>
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Status</th>
                                        <th>Count</th>
                                        <th>Average Amount</th>
                                        <th>Total Approved</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($claims_analysis as $claim): ?>
                                        <tr>
                                            <td>
                                                <span class="badge badge-<?php echo $claim['status'] === 'approved' ? 'success' : ($claim['status'] === 'rejected' ? 'danger' : 'warning'); ?>">
                                                    <?php echo ucfirst($claim['status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo $claim['claim_count']; ?></td>
                                            <td><?php echo format_currency($claim['avg_amount']); ?></td>
                                            <td><?php echo format_currency($claim['total_approved']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
                
            <?php elseif ($report_type === 'customers'): ?>
                <!-- Customer Reports -->
                <div class="row">
                    <div class="col-6">
                        <div class="card">
                            <div class="card-header">
                                <h3>Customer Acquisition</h3>
                            </div>
                            <div class="card-body">
                                <?php if (empty($customer_acquisition)): ?>
                                    <p class="text-light">No customer acquisition data for the selected period.</p>
                                <?php else: ?>
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Month</th>
                                                <th>New Customers</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($customer_acquisition as $month): ?>
                                                <tr>
                                                    <td><?php echo date('M Y', strtotime($month['month'] . '-01')); ?></td>
                                                    <td><?php echo $month['new_customers']; ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-6">
                        <div class="card">
                            <div class="card-header">
                                <h3>Top Customers by Premium</h3>
                            </div>
                            <div class="card-body">
                                <?php if (empty($top_customers)): ?>
                                    <p class="text-light">No customer premium data available.</p>
                                <?php else: ?>
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Customer</th>
                                                <th>Policies</th>
                                                <th>Total Premium</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($top_customers as $customer): ?>
                                                <tr>
                                                    <td>
                                                        <a href="customers.php?action=view&customer_id=<?php echo $customer['customer_id']; ?>">
                                                            <?php echo htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']); ?>
                                                        </a>
                                                    </td>
                                                    <td><?php echo $customer['policy_count']; ?></td>
                                                    <td><?php echo format_currency($customer['total_premiums']); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
            <?php elseif ($report_type === 'policies'): ?>
                <!-- Policy Reports -->
                <div class="card">
                    <div class="card-header">
                        <h3>Policy Type Breakdown</h3>
                    </div>
                    <div class="card-body">
                        <?php if (empty($policy_breakdown)): ?>
                            <p class="text-light">No policy data available.</p>
                        <?php else: ?>
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Policy Type</th>
                                        <th>Policies Sold</th>
                                        <th>Total Premiums</th>
                                        <th>Average Premium</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($policy_breakdown as $policy): ?>
                                        <tr>
                                            <td><span class="badge badge-primary"><?php echo ucfirst($policy['policy_type']); ?></span></td>
                                            <td><?php echo $policy['policy_count']; ?></td>
                                            <td><?php echo format_currency($policy['total_premiums']); ?></td>
                                            <td><?php echo format_currency($policy['avg_premium']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="../assets/js/main.js"></script>
</body>
</html>
