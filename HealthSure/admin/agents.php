<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

check_role('admin');

$message = get_flash_message();
$action = $_GET['action'] ?? 'list';
$agent_id = $_GET['agent_id'] ?? null;

// Handle form submissions
if ($_POST) {
    if (isset($_POST['add_agent'])) {
        $first_name = sanitize_input($_POST['first_name']);
        $last_name = sanitize_input($_POST['last_name']);
        $email = sanitize_input($_POST['email']);
        $phone = sanitize_input($_POST['phone']);
        $branch = sanitize_input($_POST['branch']);
        $license_number = sanitize_input($_POST['license_number']);
        $hire_date = $_POST['hire_date'];
        $password = $_POST['password'];
        
        // Validation
        if (empty($first_name) || empty($last_name) || empty($email) || empty($password)) {
            set_flash_message('danger', 'Please fill in all required fields');
        } elseif (!validate_email($email)) {
            set_flash_message('danger', 'Please enter a valid email address');
        } else {
            try {
                // Check if email already exists
                $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
                $stmt->execute([$email]);
                if ($stmt->fetch()) {
                    set_flash_message('danger', 'Email address already registered');
                } else {
                    $conn->beginTransaction();
                    
                    // Create user account
                    $password_hash = hash_password($password);
                    $stmt = $conn->prepare("INSERT INTO users (email, password_hash, role) VALUES (?, ?, 'agent')");
                    $stmt->execute([$email, $password_hash]);
                    $user_id = $conn->lastInsertId();
                    
                    // Create agent profile
                    $stmt = $conn->prepare("INSERT INTO agents (user_id, first_name, last_name, phone, branch, license_number, hire_date) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$user_id, $first_name, $last_name, $phone, $branch, $license_number, $hire_date]);
                    
                    $conn->commit();
                    set_flash_message('success', 'Agent created successfully!');
                    redirect('agents.php');
                }
            } catch (PDOException $e) {
                $conn->rollBack();
                set_flash_message('danger', 'Error creating agent: ' . $e->getMessage());
            }
        }
    }
    
    if (isset($_POST['update_status'])) {
        $user_id = (int)$_POST['user_id'];
        $status = $_POST['status'];
        
        try {
            $stmt = $conn->prepare("UPDATE users SET status = ? WHERE user_id = ?");
            $stmt->execute([$status, $user_id]);
            set_flash_message('success', 'Agent status updated successfully!');
        } catch (PDOException $e) {
            set_flash_message('danger', 'Error updating agent status.');
        }
    }
}

// Get all agents with their performance data
$stmt = $conn->query("SELECT a.*, u.email, u.status as user_status, u.created_at as registration_date,
                     (SELECT COUNT(*) FROM customers c WHERE c.agent_id = a.agent_id) as total_customers,
                     (SELECT COUNT(*) FROM policy_holders ph JOIN customers c ON ph.customer_id = c.customer_id WHERE c.agent_id = a.agent_id AND ph.status = 'active') as active_policies,
                     (SELECT COALESCE(SUM(py.amount), 0) FROM payments py JOIN policy_holders ph ON py.holder_id = ph.holder_id JOIN customers c ON ph.customer_id = c.customer_id WHERE c.agent_id = a.agent_id AND py.payment_type = 'premium') as total_premiums
                     FROM agents a 
                     JOIN users u ON a.user_id = u.user_id 
                     ORDER BY a.created_at DESC");
$agents = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get agent details for view
$agent_details = null;
if ($action === 'view' && $agent_id) {
    $stmt = $conn->prepare("SELECT a.*, u.email, u.status as user_status, u.created_at as registration_date
                           FROM agents a 
                           JOIN users u ON a.user_id = u.user_id 
                           WHERE a.agent_id = ?");
    $stmt->execute([$agent_id]);
    $agent_details = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get agent's customers
    $stmt = $conn->prepare("SELECT c.*, u.email,
                           (SELECT COUNT(*) FROM policy_holders ph WHERE ph.customer_id = c.customer_id AND ph.status = 'active') as active_policies
                           FROM customers c 
                           JOIN users u ON c.user_id = u.user_id 
                           WHERE c.agent_id = ? 
                           ORDER BY c.created_at DESC");
    $stmt->execute([$agent_id]);
    $agent_customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get monthly performance
    $stmt = $conn->prepare("SELECT 
                           DATE_FORMAT(ph.created_at, '%Y-%m') as month,
                           COUNT(*) as policies_sold,
                           SUM(ph.premium_amount) as premium_collected
                           FROM policy_holders ph 
                           JOIN customers c ON ph.customer_id = c.customer_id 
                           WHERE c.agent_id = ? 
                           GROUP BY DATE_FORMAT(ph.created_at, '%Y-%m') 
                           ORDER BY month DESC 
                           LIMIT 12");
    $stmt->execute([$agent_id]);
    $monthly_performance = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agent Management - HealthSure</title>
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
                <h1>Agent Management</h1>
                <a href="?action=add" class="btn btn-primary">Add New Agent</a>
            </div>
            
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message['type']; ?>">
                    <?php echo $message['message']; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($action === 'add'): ?>
                <!-- Add Agent Form -->
                <div class="card">
                    <div class="card-header">
                        <h3>Add New Agent</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label for="first_name" class="form-label">First Name *</label>
                                        <input type="text" id="first_name" name="first_name" class="form-control" 
                                               value="<?php echo $_POST['first_name'] ?? ''; ?>" required>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label for="last_name" class="form-label">Last Name *</label>
                                        <input type="text" id="last_name" name="last_name" class="form-control" 
                                               value="<?php echo $_POST['last_name'] ?? ''; ?>" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label for="email" class="form-label">Email Address *</label>
                                        <input type="email" id="email" name="email" class="form-control" 
                                               value="<?php echo $_POST['email'] ?? ''; ?>" required>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label for="phone" class="form-label">Phone Number</label>
                                        <input type="tel" id="phone" name="phone" class="form-control" 
                                               value="<?php echo $_POST['phone'] ?? ''; ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-4">
                                    <div class="form-group">
                                        <label for="branch" class="form-label">Branch</label>
                                        <input type="text" id="branch" name="branch" class="form-control" 
                                               value="<?php echo $_POST['branch'] ?? ''; ?>">
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="form-group">
                                        <label for="license_number" class="form-label">License Number</label>
                                        <input type="text" id="license_number" name="license_number" class="form-control" 
                                               value="<?php echo $_POST['license_number'] ?? ''; ?>">
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="form-group">
                                        <label for="hire_date" class="form-label">Hire Date</label>
                                        <input type="date" id="hire_date" name="hire_date" class="form-control" 
                                               value="<?php echo $_POST['hire_date'] ?? date('Y-m-d'); ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="password" class="form-label">Password *</label>
                                <input type="password" id="password" name="password" class="form-control" required>
                                <small class="text-light">Minimum 6 characters</small>
                            </div>
                            
                            <div class="d-flex" style="gap: 1rem;">
                                <button type="submit" name="add_agent" class="btn btn-primary">Create Agent</button>
                                <a href="agents.php" class="btn btn-outline">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            <?php elseif ($action === 'view' && $agent_details): ?>
                <!-- Agent Details View -->
                <div class="row">
                    <div class="col-4">
                        <div class="card">
                            <div class="card-header">
                                <h3>Agent Information</h3>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <strong>Name:</strong><br>
                                    <?php echo htmlspecialchars($agent_details['first_name'] . ' ' . $agent_details['last_name']); ?>
                                </div>
                                <div class="mb-3">
                                    <strong>Email:</strong><br>
                                    <?php echo htmlspecialchars($agent_details['email']); ?>
                                </div>
                                <div class="mb-3">
                                    <strong>Phone:</strong><br>
                                    <?php echo htmlspecialchars($agent_details['phone'] ?? 'Not provided'); ?>
                                </div>
                                <div class="mb-3">
                                    <strong>Branch:</strong><br>
                                    <?php echo htmlspecialchars($agent_details['branch'] ?? 'Not specified'); ?>
                                </div>
                                <div class="mb-3">
                                    <strong>License Number:</strong><br>
                                    <?php echo htmlspecialchars($agent_details['license_number'] ?? 'Not provided'); ?>
                                </div>
                                <div class="mb-3">
                                    <strong>Hire Date:</strong><br>
                                    <?php echo $agent_details['hire_date'] ? format_date($agent_details['hire_date']) : 'Not specified'; ?>
                                </div>
                                <div class="mb-3">
                                    <strong>Registration Date:</strong><br>
                                    <?php echo format_date($agent_details['registration_date']); ?>
                                </div>
                                <div class="mb-3">
                                    <strong>Status:</strong><br>
                                    <span class="badge badge-<?php echo $agent_details['user_status'] === 'active' ? 'success' : 'danger'; ?>">
                                        <?php echo ucfirst($agent_details['user_status']); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Performance Summary -->
                        <div class="card mt-4">
                            <div class="card-header">
                                <h3>Performance Summary</h3>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <strong>Total Customers:</strong><br>
                                    <?php echo count($agent_customers); ?>
                                </div>
                                <div class="mb-3">
                                    <strong>Active Policies:</strong><br>
                                    <?php echo array_sum(array_column($agent_customers, 'active_policies')); ?>
                                </div>
                                <div class="mb-3">
                                    <strong>Total Premium Collected:</strong><br>
                                    <?php 
                                    $total_premium = 0;
                                    foreach ($monthly_performance as $month) {
                                        $total_premium += $month['premium_collected'];
                                    }
                                    echo format_currency($total_premium);
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-8">
                        <!-- Agent's Customers -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h3>Assigned Customers</h3>
                            </div>
                            <div class="card-body">
                                <?php if (empty($agent_customers)): ?>
                                    <p class="text-light">No customers assigned yet.</p>
                                <?php else: ?>
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Customer</th>
                                                <th>Email</th>
                                                <th>Active Policies</th>
                                                <th>Registration Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($agent_customers as $customer): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($customer['email']); ?></td>
                                                    <td><?php echo $customer['active_policies']; ?></td>
                                                    <td><?php echo format_date($customer['created_at']); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Monthly Performance -->
                        <div class="card">
                            <div class="card-header">
                                <h3>Monthly Performance</h3>
                            </div>
                            <div class="card-body">
                                <?php if (empty($monthly_performance)): ?>
                                    <p class="text-light">No performance data available.</p>
                                <?php else: ?>
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Month</th>
                                                <th>Policies Sold</th>
                                                <th>Premium Collected</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($monthly_performance as $month): ?>
                                                <tr>
                                                    <td><?php echo date('M Y', strtotime($month['month'] . '-01')); ?></td>
                                                    <td><?php echo $month['policies_sold']; ?></td>
                                                    <td><?php echo format_currency($month['premium_collected']); ?></td>
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
                    <a href="agents.php" class="btn btn-outline">Back to Agents</a>
                </div>
            <?php else: ?>
                <!-- Agents List -->
                <div class="card">
                    <div class="card-header">
                        <h3>All Agents</h3>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Agent</th>
                                    <th>Contact</th>
                                    <th>Branch</th>
                                    <th>Customers</th>
                                    <th>Active Policies</th>
                                    <th>Total Premiums</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($agents as $agent): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($agent['first_name'] . ' ' . $agent['last_name']); ?></strong>
                                            <br><small class="text-light">ID: <?php echo $agent['agent_id']; ?></small>
                                            <?php if ($agent['license_number']): ?>
                                                <br><small class="text-light">License: <?php echo htmlspecialchars($agent['license_number']); ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($agent['email']); ?>
                                            <?php if ($agent['phone']): ?>
                                                <br><small><?php echo htmlspecialchars($agent['phone']); ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($agent['branch'] ?? 'Not specified'); ?></td>
                                        <td><?php echo $agent['total_customers']; ?></td>
                                        <td><?php echo $agent['active_policies']; ?></td>
                                        <td><?php echo format_currency($agent['total_premiums']); ?></td>
                                        <td>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="user_id" value="<?php echo $agent['user_id']; ?>">
                                                <select name="status" onchange="this.form.submit()" class="form-control form-select" style="width: auto;">
                                                    <option value="active" <?php echo $agent['user_status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                                    <option value="blocked" <?php echo $agent['user_status'] === 'blocked' ? 'selected' : ''; ?>>Blocked</option>
                                                </select>
                                                <input type="hidden" name="update_status" value="1">
                                            </form>
                                        </td>
                                        <td>
                                            <a href="?action=view&agent_id=<?php echo $agent['agent_id']; ?>" class="btn btn-sm btn-primary">View</a>
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
