<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

check_role('admin');

$message = get_flash_message();
$report_type = $_GET['type'] ?? 'overview';
$date_from = $_GET['date_from'] ?? date('Y-m-01'); // First day of current month
$date_to = $_GET['date_to'] ?? date('Y-m-d'); // Today

// Overview Statistics
$stmt = $conn->query("SELECT 
                     (SELECT COUNT(*) FROM customers) as total_customers,
                     (SELECT COUNT(*) FROM agents) as total_agents,
                     (SELECT COUNT(*) FROM policies WHERE status = 'active') as active_policies,
                     (SELECT COUNT(*) FROM policy_holders WHERE status = 'active') as active_policy_holders,
                     (SELECT COUNT(*) FROM claims) as total_claims,
                     (SELECT COUNT(*) FROM claims WHERE status = 'pending') as pending_claims,
                     (SELECT COUNT(*) FROM claims WHERE status = 'approved') as approved_claims,
                     (SELECT COUNT(*) FROM claims WHERE status = 'rejected') as rejected_claims,
                     (SELECT COALESCE(SUM(amount), 0) FROM payments WHERE payment_type = 'premium') as total_premiums,
                     (SELECT COALESCE(SUM(amount), 0) FROM payments WHERE payment_type = 'claim_settlement') as total_settlements");
$overview_stats = $stmt->fetch(PDO::FETCH_ASSOC);

// Monthly Revenue Report
$stmt = $conn->prepare("SELECT 
                       DATE_FORMAT(payment_date, '%Y-%m') as month,
                       SUM(CASE WHEN payment_type = 'premium' THEN amount ELSE 0 END) as premium_income,
                       SUM(CASE WHEN payment_type = 'claim_settlement' THEN amount ELSE 0 END) as claim_payouts,
                       COUNT(CASE WHEN payment_type = 'premium' THEN 1 END) as premium_count,
                       COUNT(CASE WHEN payment_type = 'claim_settlement' THEN 1 END) as claim_count
                       FROM payments 
                       WHERE payment_date BETWEEN ? AND ?
                       GROUP BY DATE_FORMAT(payment_date, '%Y-%m')
                       ORDER BY month DESC");
$stmt->execute([$date_from, $date_to]);
$monthly_revenue = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Policy Type Distribution
$stmt = $conn->query("SELECT 
                     p.policy_type,
                     COUNT(ph.holder_id) as policy_count,
                     SUM(ph.premium_amount) as total_premiums,
                     AVG(ph.premium_amount) as avg_premium,
                     SUM(p.coverage_amount) as total_coverage
                     FROM policies p
                     LEFT JOIN policy_holders ph ON p.policy_id = ph.policy_id AND ph.status = 'active'
                     WHERE p.status = 'active'
                     GROUP BY p.policy_type
                     ORDER BY policy_count DESC");
$policy_distribution = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Agent Performance Report
$stmt = $conn->prepare("SELECT 
                       a.agent_id, a.first_name, a.last_name, a.branch,
                       COUNT(DISTINCT c.customer_id) as total_customers,
                       COUNT(DISTINCT ph.holder_id) as policies_sold,
                       COALESCE(SUM(py.amount), 0) as premium_collected,
                       COUNT(DISTINCT cl.claim_id) as claims_handled
                       FROM agents a
                       LEFT JOIN customers c ON a.agent_id = c.agent_id
                       LEFT JOIN policy_holders ph ON c.customer_id = ph.customer_id
                       LEFT JOIN payments py ON ph.holder_id = py.holder_id AND py.payment_type = 'premium' AND py.payment_date BETWEEN ? AND ?
                       LEFT JOIN claims cl ON ph.holder_id = cl.holder_id AND cl.created_at BETWEEN ? AND ?
                       GROUP BY a.agent_id
                       ORDER BY premium_collected DESC");
$stmt->execute([$date_from, $date_to, $date_from, $date_to]);
$agent_performance = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Claims Analysis
$stmt = $conn->prepare("SELECT 
                       p.policy_type,
                       COUNT(*) as total_claims,
                       SUM(CASE WHEN c.status = 'approved' THEN 1 ELSE 0 END) as approved_claims,
                       SUM(CASE WHEN c.status = 'rejected' THEN 1 ELSE 0 END) as rejected_claims,
                       AVG(c.claim_amount) as avg_claim_amount,
                       SUM(CASE WHEN c.status = 'approved' THEN c.approved_amount ELSE 0 END) as total_approved_amount,
                       ROUND((SUM(CASE WHEN c.status = 'approved' THEN 1 ELSE 0 END) / COUNT(*)) * 100, 2) as approval_rate
                       FROM claims c
                       JOIN policy_holders ph ON c.holder_id = ph.holder_id
                       JOIN policies p ON ph.policy_id = p.policy_id
                       WHERE c.created_at BETWEEN ? AND ?
                       GROUP BY p.policy_type
                       ORDER BY total_claims DESC");
$stmt->execute([$date_from, $date_to]);
$claims_analysis = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Customer Growth Report
$stmt = $conn->prepare("SELECT 
                       DATE_FORMAT(created_at, '%Y-%m') as month,
                       COUNT(*) as new_customers
                       FROM customers
                       WHERE created_at BETWEEN ? AND ?
                       GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                       ORDER BY month DESC");
$stmt->execute([$date_from, $date_to]);
$customer_growth = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Top Customers by Premium
$stmt = $conn->prepare("SELECT 
                       c.customer_id, c.first_name, c.last_name,
                       u.email,
                       COUNT(ph.holder_id) as total_policies,
                       SUM(py.amount) as total_premiums_paid,
                       COUNT(cl.claim_id) as total_claims
                       FROM customers c
                       JOIN users u ON c.user_id = u.user_id
                       LEFT JOIN policy_holders ph ON c.customer_id = ph.customer_id
                       LEFT JOIN payments py ON ph.holder_id = py.holder_id AND py.payment_type = 'premium'
                       LEFT JOIN claims cl ON ph.holder_id = cl.holder_id
                       GROUP BY c.customer_id
                       HAVING total_premiums_paid > 0
                       ORDER BY total_premiums_paid DESC
                       LIMIT 10");
$stmt->execute();
$top_customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports & Analytics - HealthSure</title>
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
                <h1>Reports & Analytics</h1>
                <div>
                    <button onclick="window.print()" class="btn btn-outline">Print Report</button>
                    <button onclick="exportToCSV()" class="btn btn-primary">Export CSV</button>
                </div>
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
                        <a href="?type=revenue" class="btn <?php echo $report_type === 'revenue' ? 'btn-primary' : 'btn-outline'; ?>">Revenue</a>
                        <a href="?type=policies" class="btn <?php echo $report_type === 'policies' ? 'btn-primary' : 'btn-outline'; ?>">Policies</a>
                        <a href="?type=agents" class="btn <?php echo $report_type === 'agents' ? 'btn-primary' : 'btn-outline'; ?>">Agents</a>
                        <a href="?type=claims" class="btn <?php echo $report_type === 'claims' ? 'btn-primary' : 'btn-outline'; ?>">Claims</a>
                        <a href="?type=customers" class="btn <?php echo $report_type === 'customers' ? 'btn-primary' : 'btn-outline'; ?>">Customers</a>
                    </div>
                </div>
            </div>
            
            <?php if ($report_type === 'overview'): ?>
                <!-- Overview Report -->
                <div class="stats-grid mb-4">
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $overview_stats['total_customers']; ?></div>
                        <div class="stat-label">Total Customers</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $overview_stats['total_agents']; ?></div>
                        <div class="stat-label">Total Agents</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $overview_stats['active_policy_holders']; ?></div>
                        <div class="stat-label">Active Policies</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $overview_stats['total_claims']; ?></div>
                        <div class="stat-label">Total Claims</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo format_currency($overview_stats['total_premiums']); ?></div>
                        <div class="stat-label">Total Premiums</div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-6">
                        <div class="card">
                            <div class="card-header">
                                <h3>Claims Summary</h3>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between">
                                        <span>Pending Claims:</span>
                                        <strong><?php echo $overview_stats['pending_claims']; ?></strong>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between">
                                        <span>Approved Claims:</span>
                                        <strong style="color: var(--success-color);"><?php echo $overview_stats['approved_claims']; ?></strong>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between">
                                        <span>Rejected Claims:</span>
                                        <strong style="color: var(--danger-color);"><?php echo $overview_stats['rejected_claims']; ?></strong>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between">
                                        <span>Total Settlements:</span>
                                        <strong><?php echo format_currency($overview_stats['total_settlements']); ?></strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-6">
                        <div class="card">
                            <div class="card-header">
                                <h3>Policy Distribution</h3>
                            </div>
                            <div class="card-body">
                                <?php foreach ($policy_distribution as $policy): ?>
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span><?php echo ucfirst($policy['policy_type']); ?> Insurance</span>
                                            <div>
                                                <span class="badge badge-primary"><?php echo $policy['policy_count']; ?></span>
                                                <small class="text-light"><?php echo format_currency($policy['total_premiums']); ?></small>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
            <?php elseif ($report_type === 'revenue'): ?>
                <!-- Revenue Report -->
                <div class="card">
                    <div class="card-header">
                        <h3>Monthly Revenue Report</h3>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Month</th>
                                    <th>Premium Income</th>
                                    <th>Claim Payouts</th>
                                    <th>Net Revenue</th>
                                    <th>Premium Count</th>
                                    <th>Claim Count</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($monthly_revenue as $month): ?>
                                    <?php $net_revenue = $month['premium_income'] - $month['claim_payouts']; ?>
                                    <tr>
                                        <td><?php echo date('M Y', strtotime($month['month'] . '-01')); ?></td>
                                        <td style="color: var(--success-color);"><?php echo format_currency($month['premium_income']); ?></td>
                                        <td style="color: var(--danger-color);"><?php echo format_currency($month['claim_payouts']); ?></td>
                                        <td style="color: <?php echo $net_revenue >= 0 ? 'var(--success-color)' : 'var(--danger-color)'; ?>;">
                                            <?php echo format_currency($net_revenue); ?>
                                        </td>
                                        <td><?php echo $month['premium_count']; ?></td>
                                        <td><?php echo $month['claim_count']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
            <?php elseif ($report_type === 'policies'): ?>
                <!-- Policy Report -->
                <div class="card">
                    <div class="card-header">
                        <h3>Policy Analysis Report</h3>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Policy Type</th>
                                    <th>Active Policies</th>
                                    <th>Total Premiums</th>
                                    <th>Average Premium</th>
                                    <th>Total Coverage</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($policy_distribution as $policy): ?>
                                    <tr>
                                        <td><span class="badge badge-primary"><?php echo ucfirst($policy['policy_type']); ?></span></td>
                                        <td><?php echo $policy['policy_count']; ?></td>
                                        <td><?php echo format_currency($policy['total_premiums']); ?></td>
                                        <td><?php echo format_currency($policy['avg_premium']); ?></td>
                                        <td><?php echo format_currency($policy['total_coverage']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
            <?php elseif ($report_type === 'agents'): ?>
                <!-- Agent Performance Report -->
                <div class="card">
                    <div class="card-header">
                        <h3>Agent Performance Report</h3>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Agent</th>
                                    <th>Branch</th>
                                    <th>Customers</th>
                                    <th>Policies Sold</th>
                                    <th>Premium Collected</th>
                                    <th>Claims Handled</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($agent_performance as $agent): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($agent['first_name'] . ' ' . $agent['last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($agent['branch'] ?? 'Not specified'); ?></td>
                                        <td><?php echo $agent['total_customers']; ?></td>
                                        <td><?php echo $agent['policies_sold']; ?></td>
                                        <td><?php echo format_currency($agent['premium_collected']); ?></td>
                                        <td><?php echo $agent['claims_handled']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
            <?php elseif ($report_type === 'claims'): ?>
                <!-- Claims Analysis Report -->
                <div class="card">
                    <div class="card-header">
                        <h3>Claims Analysis Report</h3>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Policy Type</th>
                                    <th>Total Claims</th>
                                    <th>Approved</th>
                                    <th>Rejected</th>
                                    <th>Approval Rate</th>
                                    <th>Avg Claim Amount</th>
                                    <th>Total Approved Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($claims_analysis as $claim): ?>
                                    <tr>
                                        <td><span class="badge badge-primary"><?php echo ucfirst($claim['policy_type']); ?></span></td>
                                        <td><?php echo $claim['total_claims']; ?></td>
                                        <td style="color: var(--success-color);"><?php echo $claim['approved_claims']; ?></td>
                                        <td style="color: var(--danger-color);"><?php echo $claim['rejected_claims']; ?></td>
                                        <td>
                                            <span class="badge badge-<?php echo $claim['approval_rate'] >= 70 ? 'success' : ($claim['approval_rate'] >= 50 ? 'warning' : 'danger'); ?>">
                                                <?php echo $claim['approval_rate']; ?>%
                                            </span>
                                        </td>
                                        <td><?php echo format_currency($claim['avg_claim_amount']); ?></td>
                                        <td><?php echo format_currency($claim['total_approved_amount']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
            <?php elseif ($report_type === 'customers'): ?>
                <!-- Customer Reports -->
                <div class="row">
                    <div class="col-6">
                        <div class="card">
                            <div class="card-header">
                                <h3>Customer Growth</h3>
                            </div>
                            <div class="card-body">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Month</th>
                                            <th>New Customers</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($customer_growth as $growth): ?>
                                            <tr>
                                                <td><?php echo date('M Y', strtotime($growth['month'] . '-01')); ?></td>
                                                <td><?php echo $growth['new_customers']; ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-6">
                        <div class="card">
                            <div class="card-header">
                                <h3>Top Customers by Premium</h3>
                            </div>
                            <div class="card-body">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Customer</th>
                                            <th>Policies</th>
                                            <th>Total Premiums</th>
                                            <th>Claims</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($top_customers as $customer): ?>
                                            <tr>
                                                <td>
                                                    <?php echo htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']); ?>
                                                    <br><small class="text-light"><?php echo htmlspecialchars($customer['email']); ?></small>
                                                </td>
                                                <td><?php echo $customer['total_policies']; ?></td>
                                                <td><?php echo format_currency($customer['total_premiums_paid']); ?></td>
                                                <td><?php echo $customer['total_claims']; ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        function exportToCSV() {
            // Simple CSV export functionality
            const table = document.querySelector('.table');
            if (!table) return;
            
            let csv = [];
            const rows = table.querySelectorAll('tr');
            
            for (let i = 0; i < rows.length; i++) {
                const row = [];
                const cols = rows[i].querySelectorAll('td, th');
                
                for (let j = 0; j < cols.length; j++) {
                    let text = cols[j].innerText.replace(/"/g, '""');
                    row.push('"' + text + '"');
                }
                
                csv.push(row.join(','));
            }
            
            const csvFile = new Blob([csv.join('\n')], { type: 'text/csv' });
            const downloadLink = document.createElement('a');
            downloadLink.download = 'healthsure_report_<?php echo date('Y-m-d'); ?>.csv';
            downloadLink.href = window.URL.createObjectURL(csvFile);
            downloadLink.style.display = 'none';
            document.body.appendChild(downloadLink);
            downloadLink.click();
            document.body.removeChild(downloadLink);
        }
    </script>
    <script src="../assets/js/main.js"></script>
</body>
</html>
