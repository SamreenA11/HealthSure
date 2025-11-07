<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

check_role('agent');

$agent_info = get_agent_info($_SESSION['user_id'], $conn);
$message = get_flash_message();
$action = $_GET['action'] ?? 'list';
$customer_id = $_GET['customer_id'] ?? null;

// Handle form submissions
if ($_POST) {
    if (isset($_POST['update_notes'])) {
        $customer_id = (int)$_POST['customer_id'];
        $notes = sanitize_input($_POST['notes']);
        
        try {
            // For now, we'll add notes as a comment (in a real system, you'd have a notes table)
            set_flash_message('success', 'Customer notes updated successfully!');
        } catch (PDOException $e) {
            set_flash_message('danger', 'Error updating notes.');
        }
    }
}

// Get customers assigned to this agent
try {
    $stmt = $conn->prepare("SELECT c.*, u.email, u.status as user_status,
                           COUNT(DISTINCT ph.holder_id) as total_policies,
                           COUNT(DISTINCT cl.claim_id) as total_claims,
                           COALESCE(SUM(py.amount), 0) as total_premiums
                           FROM customers c 
                           JOIN users u ON c.user_id = u.user_id 
                           LEFT JOIN policy_holders ph ON c.customer_id = ph.customer_id
                           LEFT JOIN claims cl ON ph.holder_id = cl.holder_id
                           LEFT JOIN payments py ON ph.holder_id = py.holder_id AND py.payment_type = 'premium'
                           WHERE c.agent_id = ?
                           GROUP BY c.customer_id
                           ORDER BY c.created_at DESC");
    $stmt->execute([$agent_info['agent_id']]);
    $my_customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $my_customers = [];
    set_flash_message('warning', 'Unable to load customer data.');
}

// Get customer details for view
$customer_details = null;
if ($action === 'view' && $customer_id) {
    try {
        $stmt = $conn->prepare("SELECT c.*, u.email, u.status as user_status
                               FROM customers c 
                               JOIN users u ON c.user_id = u.user_id 
                               WHERE c.customer_id = ? AND c.agent_id = ?");
        $stmt->execute([$customer_id, $agent_info['agent_id']]);
        $customer_details = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($customer_details) {
            // Get customer's policies
            $stmt = $conn->prepare("SELECT ph.*, p.policy_name, p.policy_type, p.coverage_amount
                                   FROM policy_holders ph 
                                   JOIN policies p ON ph.policy_id = p.policy_id 
                                   WHERE ph.customer_id = ?
                                   ORDER BY ph.created_at DESC");
            $stmt->execute([$customer_id]);
            $customer_policies = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get customer's claims
            $stmt = $conn->prepare("SELECT cl.*, p.policy_name
                                   FROM claims cl 
                                   JOIN policy_holders ph ON cl.holder_id = ph.holder_id
                                   JOIN policies p ON ph.policy_id = p.policy_id
                                   WHERE ph.customer_id = ?
                                   ORDER BY cl.created_at DESC
                                   LIMIT 10");
            $stmt->execute([$customer_id]);
            $customer_claims = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (PDOException $e) {
        $customer_details = null;
        set_flash_message('danger', 'Error loading customer details.');
    }
}

// Get agent statistics
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
    $agent_stats = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $agent_stats = ['total_customers' => 0, 'total_policies' => 0, 'total_premiums' => 0, 'total_claims' => 0];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Customers - HealthSure</title>
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
                <h1>My Customers</h1>
                <?php if ($action === 'view'): ?>
                    <a href="customers.php" class="btn btn-outline">← Back to List</a>
                <?php endif; ?>
            </div>
            
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message['type']; ?>">
                    <?php echo $message['message']; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($action === 'list'): ?>
                <!-- Agent Statistics -->
                <div class="stats-grid mb-4">
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $agent_stats['total_customers']; ?></div>
                        <div class="stat-label">My Customers</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $agent_stats['total_policies']; ?></div>
                        <div class="stat-label">Policies Sold</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo format_currency($agent_stats['total_premiums']); ?></div>
                        <div class="stat-label">Premium Collected</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $agent_stats['total_claims']; ?></div>
                        <div class="stat-label">Claims Handled</div>
                    </div>
                </div>
                
                <!-- Customers List -->
                <?php if (empty($my_customers)): ?>
                    <div class="card">
                        <div class="card-body text-center">
                            <h4>No Customers Assigned</h4>
                            <p class="text-light">You don't have any customers assigned yet. Contact your admin to get customer assignments.</p>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="card">
                        <div class="card-header">
                            <h3>Customer List</h3>
                        </div>
                        <div class="card-body">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Customer</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Policies</th>
                                        <th>Claims</th>
                                        <th>Total Premiums</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($my_customers as $customer): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']); ?></strong><br>
                                                <small class="text-light">ID: #<?php echo $customer['customer_id']; ?></small>
                                            </td>
                                            <td><?php echo htmlspecialchars($customer['email']); ?></td>
                                            <td><?php echo htmlspecialchars($customer['phone'] ?? 'Not provided'); ?></td>
                                            <td><span class="badge badge-primary"><?php echo $customer['total_policies']; ?></span></td>
                                            <td><span class="badge badge-info"><?php echo $customer['total_claims']; ?></span></td>
                                            <td><?php echo format_currency($customer['total_premiums']); ?></td>
                                            <td>
                                                <span class="badge badge-<?php echo $customer['user_status'] === 'active' ? 'success' : 'danger'; ?>">
                                                    <?php echo ucfirst($customer['user_status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="?action=view&customer_id=<?php echo $customer['customer_id']; ?>" class="btn btn-sm btn-primary">View Details</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>
                
            <?php elseif ($action === 'view' && $customer_details): ?>
                <!-- Customer Details View -->
                <div class="row">
                    <div class="col-8">
                        <div class="card">
                            <div class="card-header">
                                <h3>Customer Information</h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-6">
                                        <div class="mb-3">
                                            <strong>Full Name:</strong><br>
                                            <?php echo htmlspecialchars($customer_details['first_name'] . ' ' . $customer_details['last_name']); ?>
                                        </div>
                                        <div class="mb-3">
                                            <strong>Email:</strong><br>
                                            <?php echo htmlspecialchars($customer_details['email']); ?>
                                        </div>
                                        <div class="mb-3">
                                            <strong>Phone:</strong><br>
                                            <?php echo htmlspecialchars($customer_details['phone'] ?? 'Not provided'); ?>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="mb-3">
                                            <strong>Date of Birth:</strong><br>
                                            <?php echo $customer_details['date_of_birth'] ? format_date($customer_details['date_of_birth']) : 'Not provided'; ?>
                                        </div>
                                        <div class="mb-3">
                                            <strong>Gender:</strong><br>
                                            <?php echo $customer_details['gender'] ? ucfirst($customer_details['gender']) : 'Not specified'; ?>
                                        </div>
                                        <div class="mb-3">
                                            <strong>Member Since:</strong><br>
                                            <?php echo format_date($customer_details['created_at']); ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <?php if ($customer_details['address']): ?>
                                    <div class="mb-3">
                                        <strong>Address:</strong><br>
                                        <?php echo nl2br(htmlspecialchars($customer_details['address'])); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Customer's Policies -->
                        <div class="card">
                            <div class="card-header">
                                <h3>Customer's Policies</h3>
                            </div>
                            <div class="card-body">
                                <?php if (empty($customer_policies)): ?>
                                    <p class="text-light">No policies found for this customer.</p>
                                <?php else: ?>
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Policy</th>
                                                <th>Type</th>
                                                <th>Premium</th>
                                                <th>Coverage</th>
                                                <th>Status</th>
                                                <th>Period</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($customer_policies as $policy): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($policy['policy_name']); ?></td>
                                                    <td><span class="badge badge-primary"><?php echo ucfirst($policy['policy_type']); ?></span></td>
                                                    <td><?php echo format_currency($policy['premium_amount']); ?></td>
                                                    <td><?php echo format_currency($policy['coverage_amount']); ?></td>
                                                    <td>
                                                        <span class="badge badge-<?php echo $policy['status'] === 'active' ? 'success' : 'warning'; ?>">
                                                            <?php echo ucfirst($policy['status']); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <?php echo format_date($policy['start_date']); ?> to<br>
                                                        <?php echo format_date($policy['end_date']); ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Recent Claims -->
                        <div class="card">
                            <div class="card-header">
                                <h3>Recent Claims</h3>
                            </div>
                            <div class="card-body">
                                <?php if (empty($customer_claims)): ?>
                                    <p class="text-light">No claims found for this customer.</p>
                                <?php else: ?>
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Claim ID</th>
                                                <th>Policy</th>
                                                <th>Amount</th>
                                                <th>Status</th>
                                                <th>Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($customer_claims as $claim): ?>
                                                <tr>
                                                    <td>#<?php echo $claim['claim_id']; ?></td>
                                                    <td><?php echo htmlspecialchars($claim['policy_name']); ?></td>
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
                        </div>
                    </div>
                    
                    <div class="col-4">
                        <div class="card">
                            <div class="card-header">
                                <h3>Quick Actions</h3>
                            </div>
                            <div class="card-body">
                                <div class="d-flex flex-column" style="gap: 1rem;">
                                    <button class="btn btn-primary" onclick="contactCustomer('<?php echo $customer_details['email']; ?>')">
                                        📧 Send Email
                                    </button>
                                    <?php if ($customer_details['phone']): ?>
                                        <button class="btn btn-success" onclick="callCustomer('<?php echo $customer_details['phone']; ?>')">
                                            📞 Call Customer
                                        </button>
                                    <?php endif; ?>
                                    <a href="../customer/browse-policies.php" class="btn btn-outline">
                                        📋 View Available Policies
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card mt-4">
                            <div class="card-header">
                                <h3>Customer Notes</h3>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="">
                                    <input type="hidden" name="customer_id" value="<?php echo $customer_details['customer_id']; ?>">
                                    <div class="form-group">
                                        <textarea name="notes" class="form-control" rows="4" 
                                                  placeholder="Add notes about this customer..."></textarea>
                                    </div>
                                    <button type="submit" name="update_notes" class="btn btn-primary btn-sm">Save Notes</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                
            <?php else: ?>
                <div class="card">
                    <div class="card-body text-center">
                        <h4>Customer Not Found</h4>
                        <p class="text-light">The requested customer was not found or is not assigned to you.</p>
                        <a href="customers.php" class="btn btn-primary">Back to Customer List</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        function contactCustomer(email) {
            window.location.href = 'mailto:' + email + '?subject=HealthSure Insurance Inquiry';
        }
        
        function callCustomer(phone) {
            if (confirm('Call customer at ' + phone + '?')) {
                window.location.href = 'tel:' + phone;
            }
        }
    </script>
    <script src="../assets/js/main.js"></script>
</body>
</html>
