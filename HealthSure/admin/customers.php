<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

check_role('admin');

$message = get_flash_message();
$action = $_GET['action'] ?? 'list';
$customer_id = $_GET['customer_id'] ?? null;

// Handle form submissions
if ($_POST) {
    if (isset($_POST['update_status'])) {
        $user_id = (int)$_POST['user_id'];
        $status = $_POST['status'];
        
        try {
            $stmt = $conn->prepare("UPDATE users SET status = ? WHERE user_id = ?");
            $stmt->execute([$status, $user_id]);
            set_flash_message('success', 'Customer status updated successfully!');
        } catch (PDOException $e) {
            set_flash_message('danger', 'Error updating customer status.');
        }
    }
    
    if (isset($_POST['assign_agent'])) {
        $customer_id = (int)$_POST['customer_id'];
        $agent_id = $_POST['agent_id'] ? (int)$_POST['agent_id'] : null;
        
        try {
            $stmt = $conn->prepare("UPDATE customers SET agent_id = ? WHERE customer_id = ?");
            $stmt->execute([$agent_id, $customer_id]);
            set_flash_message('success', 'Agent assigned successfully!');
        } catch (PDOException $e) {
            set_flash_message('danger', 'Error assigning agent.');
        }
    }
}

// Get all customers with their details
$stmt = $conn->query("SELECT c.*, u.email, u.status as user_status, u.created_at as registration_date,
                     a.first_name as agent_first_name, a.last_name as agent_last_name,
                     (SELECT COUNT(*) FROM policy_holders ph WHERE ph.customer_id = c.customer_id AND ph.status = 'active') as active_policies,
                     (SELECT COUNT(*) FROM claims cl JOIN policy_holders ph ON cl.holder_id = ph.holder_id WHERE ph.customer_id = c.customer_id) as total_claims,
                     (SELECT COALESCE(SUM(py.amount), 0) FROM payments py JOIN policy_holders ph ON py.holder_id = ph.holder_id WHERE ph.customer_id = c.customer_id AND py.payment_type = 'premium') as total_premiums
                     FROM customers c 
                     JOIN users u ON c.user_id = u.user_id 
                     LEFT JOIN agents a ON c.agent_id = a.agent_id 
                     ORDER BY c.created_at DESC");
$customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get all agents for assignment
$stmt = $conn->query("SELECT agent_id, first_name, last_name FROM agents ORDER BY first_name, last_name");
$agents = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get customer details for view
$customer_details = null;
if ($action === 'view' && $customer_id) {
    $stmt = $conn->prepare("SELECT c.*, u.email, u.status as user_status, u.created_at as registration_date,
                           a.first_name as agent_first_name, a.last_name as agent_last_name
                           FROM customers c 
                           JOIN users u ON c.user_id = u.user_id 
                           LEFT JOIN agents a ON c.agent_id = a.agent_id 
                           WHERE c.customer_id = ?");
    $stmt->execute([$customer_id]);
    $customer_details = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get customer's policies
    $stmt = $conn->prepare("SELECT ph.*, p.policy_name, p.policy_type, p.coverage_amount 
                           FROM policy_holders ph 
                           JOIN policies p ON ph.policy_id = p.policy_id 
                           WHERE ph.customer_id = ? 
                           ORDER BY ph.created_at DESC");
    $stmt->execute([$customer_id]);
    $customer_policies = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get customer's claims
    $stmt = $conn->prepare("SELECT c.*, p.policy_name 
                           FROM claims c 
                           JOIN policy_holders ph ON c.holder_id = ph.holder_id 
                           JOIN policies p ON ph.policy_id = p.policy_id 
                           WHERE ph.customer_id = ? 
                           ORDER BY c.created_at DESC");
    $stmt->execute([$customer_id]);
    $customer_claims = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Management - HealthSure</title>
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
                <h1>Customer Management</h1>
                <div>
                    <a href="?action=export" class="btn btn-outline">Export Data</a>
                </div>
            </div>
            
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message['type']; ?>">
                    <?php echo $message['message']; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($action === 'view' && $customer_details): ?>
                <!-- Customer Details View -->
                <div class="row">
                    <div class="col-4">
                        <div class="card">
                            <div class="card-header">
                                <h3>Customer Information</h3>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <strong>Name:</strong><br>
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
                                <div class="mb-3">
                                    <strong>Date of Birth:</strong><br>
                                    <?php echo $customer_details['date_of_birth'] ? format_date($customer_details['date_of_birth']) : 'Not provided'; ?>
                                </div>
                                <div class="mb-3">
                                    <strong>Gender:</strong><br>
                                    <?php echo $customer_details['gender'] ? ucfirst($customer_details['gender']) : 'Not specified'; ?>
                                </div>
                                <div class="mb-3">
                                    <strong>Address:</strong><br>
                                    <?php echo htmlspecialchars($customer_details['address'] ?? 'Not provided'); ?>
                                </div>
                                <div class="mb-3">
                                    <strong>Registration Date:</strong><br>
                                    <?php echo format_date($customer_details['registration_date']); ?>
                                </div>
                                <div class="mb-3">
                                    <strong>Status:</strong><br>
                                    <span class="badge badge-<?php echo $customer_details['user_status'] === 'active' ? 'success' : 'danger'; ?>">
                                        <?php echo ucfirst($customer_details['user_status']); ?>
                                    </span>
                                </div>
                                <div class="mb-3">
                                    <strong>Assigned Agent:</strong><br>
                                    <?php if ($customer_details['agent_first_name']): ?>
                                        <?php echo htmlspecialchars($customer_details['agent_first_name'] . ' ' . $customer_details['agent_last_name']); ?>
                                    <?php else: ?>
                                        <span class="text-light">No agent assigned</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-8">
                        <!-- Customer Policies -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h3>Active Policies</h3>
                            </div>
                            <div class="card-body">
                                <?php if (empty($customer_policies)): ?>
                                    <p class="text-light">No policies found.</p>
                                <?php else: ?>
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Policy</th>
                                                <th>Type</th>
                                                <th>Coverage</th>
                                                <th>Premium</th>
                                                <th>Status</th>
                                                <th>End Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($customer_policies as $policy): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($policy['policy_name']); ?></td>
                                                    <td><span class="badge badge-primary"><?php echo ucfirst($policy['policy_type']); ?></span></td>
                                                    <td><?php echo format_currency($policy['coverage_amount']); ?></td>
                                                    <td><?php echo format_currency($policy['premium_amount']); ?></td>
                                                    <td>
                                                        <span class="badge badge-<?php echo $policy['status'] === 'active' ? 'success' : 'warning'; ?>">
                                                            <?php echo ucfirst($policy['status']); ?>
                                                        </span>
                                                    </td>
                                                    <td><?php echo format_date($policy['end_date']); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Customer Claims -->
                        <div class="card">
                            <div class="card-header">
                                <h3>Claims History</h3>
                            </div>
                            <div class="card-body">
                                <?php if (empty($customer_claims)): ?>
                                    <p class="text-light">No claims found.</p>
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
                </div>
                
                <div class="mt-4">
                    <a href="customers.php" class="btn btn-outline">Back to Customers</a>
                </div>
            <?php else: ?>
                <!-- Customers List -->
                <div class="card">
                    <div class="card-header">
                        <h3>All Customers</h3>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Customer</th>
                                    <th>Contact</th>
                                    <th>Agent</th>
                                    <th>Policies</th>
                                    <th>Claims</th>
                                    <th>Total Premiums</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($customers as $customer): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']); ?></strong>
                                            <br><small class="text-light">ID: <?php echo $customer['customer_id']; ?></small>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($customer['email']); ?>
                                            <?php if ($customer['phone']): ?>
                                                <br><small><?php echo htmlspecialchars($customer['phone']); ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($customer['agent_first_name']): ?>
                                                <?php echo htmlspecialchars($customer['agent_first_name'] . ' ' . $customer['agent_last_name']); ?>
                                            <?php else: ?>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="customer_id" value="<?php echo $customer['customer_id']; ?>">
                                                    <select name="agent_id" onchange="this.form.submit()" class="form-control form-select" style="width: auto;">
                                                        <option value="">Assign Agent</option>
                                                        <?php foreach ($agents as $agent): ?>
                                                            <option value="<?php echo $agent['agent_id']; ?>">
                                                                <?php echo htmlspecialchars($agent['first_name'] . ' ' . $agent['last_name']); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                    <input type="hidden" name="assign_agent" value="1">
                                                </form>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo $customer['active_policies']; ?></td>
                                        <td><?php echo $customer['total_claims']; ?></td>
                                        <td><?php echo format_currency($customer['total_premiums']); ?></td>
                                        <td>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="user_id" value="<?php echo $customer['user_id']; ?>">
                                                <select name="status" onchange="this.form.submit()" class="form-control form-select" style="width: auto;">
                                                    <option value="active" <?php echo $customer['user_status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                                    <option value="blocked" <?php echo $customer['user_status'] === 'blocked' ? 'selected' : ''; ?>>Blocked</option>
                                                </select>
                                                <input type="hidden" name="update_status" value="1">
                                            </form>
                                        </td>
                                        <td>
                                            <a href="?action=view&customer_id=<?php echo $customer['customer_id']; ?>" class="btn btn-sm btn-primary">View</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <script src="../assets/js/main.js"></script>
</body>
</html>
