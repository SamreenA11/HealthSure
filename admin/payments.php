<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

check_role('admin');

$message = get_flash_message();
$action = $_GET['action'] ?? 'list';
$payment_id = $_GET['payment_id'] ?? null;
$type_filter = $_GET['type'] ?? 'all';

// Handle form submissions
if ($_POST) {
    if (isset($_POST['add_payment'])) {
        $holder_id = (int)$_POST['holder_id'];
        $payment_type = $_POST['payment_type'];
        $amount = (float)$_POST['amount'];
        $payment_method = $_POST['payment_method'];
        $payment_date = $_POST['payment_date'];
        $transaction_id = sanitize_input($_POST['transaction_id']);
        
        try {
            $stmt = $conn->prepare("INSERT INTO payments (holder_id, payment_type, amount, payment_method, payment_date, transaction_id, status) VALUES (?, ?, ?, ?, ?, ?, 'completed')");
            $stmt->execute([$holder_id, $payment_type, $amount, $payment_method, $payment_date, $transaction_id]);
            
            set_flash_message('success', 'Payment recorded successfully!');
            redirect('payments.php');
        } catch (PDOException $e) {
            set_flash_message('danger', 'Error recording payment: ' . $e->getMessage());
        }
    }
    
    if (isset($_POST['update_status'])) {
        $payment_id = (int)$_POST['payment_id'];
        $status = $_POST['status'];
        
        try {
            $stmt = $conn->prepare("UPDATE payments SET status = ? WHERE payment_id = ?");
            $stmt->execute([$status, $payment_id]);
            set_flash_message('success', 'Payment status updated successfully!');
        } catch (PDOException $e) {
            set_flash_message('danger', 'Error updating payment status.');
        }
    }
}

// Build query based on type filter
$where_clause = "";
$params = [];
if ($type_filter !== 'all') {
    $where_clause = "WHERE p.payment_type = ?";
    $params[] = $type_filter;
}

// Get all payments with customer and policy details
try {
    $stmt = $conn->prepare("SELECT p.*, 
                           cu.first_name, cu.last_name,
                           u.email,
                           po.policy_name, po.policy_type,
                           ph.premium_amount, ph.coverage_amount,
                           c.claim_id, c.claim_amount
                           FROM payments p
                           JOIN policy_holders ph ON p.holder_id = ph.holder_id
                           JOIN customers cu ON ph.customer_id = cu.customer_id
                           JOIN users u ON cu.user_id = u.user_id
                           JOIN policies po ON ph.policy_id = po.policy_id
                           LEFT JOIN claims c ON p.claim_id = c.claim_id
                           $where_clause
                           ORDER BY p.created_at DESC");
    $stmt->execute($params);
    $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $payments = [];
    set_flash_message('warning', 'No payment data available yet. Create some customers and policies first.');
}

// Get payment details for view
$payment_details = null;
if ($action === 'view' && $payment_id) {
    $stmt = $conn->prepare("SELECT p.*, 
                           cu.first_name, cu.last_name, cu.phone, cu.address,
                           u.email,
                           po.policy_name, po.policy_type, po.description as policy_description,
                           ph.premium_amount, ph.coverage_amount, ph.start_date, ph.end_date,
                           c.claim_id, c.claim_amount, c.claim_reason
                           FROM payments p
                           JOIN policy_holders ph ON p.holder_id = ph.holder_id
                           JOIN customers cu ON ph.customer_id = cu.customer_id
                           JOIN users u ON cu.user_id = u.user_id
                           JOIN policies po ON ph.policy_id = po.policy_id
                           LEFT JOIN claims c ON p.claim_id = c.claim_id
                           WHERE p.payment_id = ?");
    $stmt->execute([$payment_id]);
    $payment_details = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Get policy holders for adding payments
try {
    $stmt = $conn->query("SELECT ph.holder_id, 
                         CONCAT(c.first_name, ' ', c.last_name, ' - ', p.policy_name) as display_name
                         FROM policy_holders ph
                         JOIN customers c ON ph.customer_id = c.customer_id
                         JOIN policies p ON ph.policy_id = p.policy_id
                         WHERE ph.status = 'active'
                         ORDER BY c.first_name, c.last_name");
    $policy_holders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $policy_holders = [];
}

// Get statistics
try {
    $stmt = $conn->query("SELECT 
                         COUNT(*) as total_payments,
                         SUM(CASE WHEN payment_type = 'premium' THEN amount ELSE 0 END) as total_premiums,
                         SUM(CASE WHEN payment_type = 'claim_settlement' THEN amount ELSE 0 END) as total_settlements,
                         SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_payments,
                         SUM(CASE WHEN payment_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN amount ELSE 0 END) as monthly_total
                         FROM payments");
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $stats = [
        'total_payments' => 0,
        'total_premiums' => 0,
        'total_settlements' => 0,
        'pending_payments' => 0,
        'monthly_total' => 0
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Management - HealthSure</title>
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
                <h1>Payment Management</h1>
                <a href="?action=add" class="btn btn-primary">Record Payment</a>
            </div>
            
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message['type']; ?>">
                    <?php echo $message['message']; ?>
                </div>
            <?php endif; ?>
            
            <!-- Statistics Cards -->
            <div class="stats-grid mb-4">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['total_payments']; ?></div>
                    <div class="stat-label">Total Payments</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo format_currency($stats['total_premiums']); ?></div>
                    <div class="stat-label">Premium Collected</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo format_currency($stats['total_settlements']); ?></div>
                    <div class="stat-label">Claims Settled</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['pending_payments']; ?></div>
                    <div class="stat-label">Pending Payments</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo format_currency($stats['monthly_total']); ?></div>
                    <div class="stat-label">Last 30 Days</div>
                </div>
            </div>
            
            <?php if ($action === 'add'): ?>
                <!-- Add Payment Form -->
                <div class="card">
                    <div class="card-header">
                        <h3>Record New Payment</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label for="holder_id" class="form-label">Policy Holder *</label>
                                        <select id="holder_id" name="holder_id" class="form-control form-select" required>
                                            <option value="">Select Policy Holder</option>
                                            <?php foreach ($policy_holders as $holder): ?>
                                                <option value="<?php echo $holder['holder_id']; ?>">
                                                    <?php echo htmlspecialchars($holder['display_name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label for="payment_type" class="form-label">Payment Type *</label>
                                        <select id="payment_type" name="payment_type" class="form-control form-select" required>
                                            <option value="">Select Type</option>
                                            <option value="premium">Premium Payment</option>
                                            <option value="claim_settlement">Claim Settlement</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-4">
                                    <div class="form-group">
                                        <label for="amount" class="form-label">Amount (₹) *</label>
                                        <input type="number" id="amount" name="amount" class="form-control" 
                                               step="0.01" required>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="form-group">
                                        <label for="payment_method" class="form-label">Payment Method *</label>
                                        <select id="payment_method" name="payment_method" class="form-control form-select" required>
                                            <option value="">Select Method</option>
                                            <option value="cash">Cash</option>
                                            <option value="card">Credit/Debit Card</option>
                                            <option value="bank_transfer">Bank Transfer</option>
                                            <option value="online">Online Payment</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="form-group">
                                        <label for="payment_date" class="form-label">Payment Date *</label>
                                        <input type="date" id="payment_date" name="payment_date" class="form-control" 
                                               value="<?php echo date('Y-m-d'); ?>" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="transaction_id" class="form-label">Transaction ID</label>
                                <input type="text" id="transaction_id" name="transaction_id" class="form-control" 
                                       placeholder="Bank reference number or transaction ID">
                            </div>
                            
                            <div class="d-flex" style="gap: 1rem;">
                                <button type="submit" name="add_payment" class="btn btn-primary">Record Payment</button>
                                <a href="payments.php" class="btn btn-outline">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            <?php elseif ($action === 'view' && $payment_details): ?>
                <!-- Payment Details View -->
                <div class="row">
                    <div class="col-6">
                        <div class="card">
                            <div class="card-header">
                                <h3>Payment Information</h3>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <strong>Payment ID:</strong><br>
                                    #<?php echo $payment_details['payment_id']; ?>
                                </div>
                                <div class="mb-3">
                                    <strong>Customer:</strong><br>
                                    <?php echo htmlspecialchars($payment_details['first_name'] . ' ' . $payment_details['last_name']); ?>
                                    <br><small><?php echo htmlspecialchars($payment_details['email']); ?></small>
                                </div>
                                <div class="mb-3">
                                    <strong>Policy:</strong><br>
                                    <?php echo htmlspecialchars($payment_details['policy_name']); ?>
                                    <br><span class="badge badge-primary"><?php echo ucfirst($payment_details['policy_type']); ?></span>
                                </div>
                                <div class="mb-3">
                                    <strong>Payment Type:</strong><br>
                                    <span class="badge badge-<?php echo $payment_details['payment_type'] === 'premium' ? 'success' : 'warning'; ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $payment_details['payment_type'])); ?>
                                    </span>
                                </div>
                                <div class="mb-3">
                                    <strong>Amount:</strong><br>
                                    <span style="font-size: 1.25rem; font-weight: 600; color: var(--success-color);">
                                        <?php echo format_currency($payment_details['amount']); ?>
                                    </span>
                                </div>
                                <div class="mb-3">
                                    <strong>Payment Method:</strong><br>
                                    <?php echo ucfirst(str_replace('_', ' ', $payment_details['payment_method'])); ?>
                                </div>
                                <div class="mb-3">
                                    <strong>Payment Date:</strong><br>
                                    <?php echo format_date($payment_details['payment_date']); ?>
                                </div>
                                <div class="mb-3">
                                    <strong>Status:</strong><br>
                                    <span class="badge badge-<?php echo $payment_details['status'] === 'completed' ? 'success' : ($payment_details['status'] === 'failed' ? 'danger' : 'warning'); ?>">
                                        <?php echo ucfirst($payment_details['status']); ?>
                                    </span>
                                </div>
                                <?php if ($payment_details['transaction_id']): ?>
                                    <div class="mb-3">
                                        <strong>Transaction ID:</strong><br>
                                        <?php echo htmlspecialchars($payment_details['transaction_id']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-6">
                        <div class="card">
                            <div class="card-header">
                                <h3>Related Information</h3>
                            </div>
                            <div class="card-body">
                                <?php if ($payment_details['payment_type'] === 'claim_settlement' && $payment_details['claim_id']): ?>
                                    <div class="mb-3">
                                        <strong>Related Claim:</strong><br>
                                        <a href="claims.php?action=view&claim_id=<?php echo $payment_details['claim_id']; ?>" class="btn btn-sm btn-outline">
                                            View Claim #<?php echo $payment_details['claim_id']; ?>
                                        </a>
                                    </div>
                                    <div class="mb-3">
                                        <strong>Claim Amount:</strong><br>
                                        <?php echo format_currency($payment_details['claim_amount']); ?>
                                    </div>
                                    <?php if ($payment_details['claim_reason']): ?>
                                        <div class="mb-3">
                                            <strong>Claim Reason:</strong><br>
                                            <p><?php echo nl2br(htmlspecialchars(substr($payment_details['claim_reason'], 0, 200))); ?>...</p>
                                        </div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <div class="mb-3">
                                        <strong>Policy Premium:</strong><br>
                                        <?php echo format_currency($payment_details['premium_amount']); ?>
                                    </div>
                                    <div class="mb-3">
                                        <strong>Coverage Amount:</strong><br>
                                        <?php echo format_currency($payment_details['coverage_amount']); ?>
                                    </div>
                                    <div class="mb-3">
                                        <strong>Policy Period:</strong><br>
                                        <?php echo format_date($payment_details['start_date']); ?> to <?php echo format_date($payment_details['end_date']); ?>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="mb-3">
                                    <strong>Customer Address:</strong><br>
                                    <?php echo htmlspecialchars($payment_details['address'] ?? 'Not provided'); ?>
                                </div>
                                
                                <?php if ($payment_details['phone']): ?>
                                    <div class="mb-3">
                                        <strong>Phone:</strong><br>
                                        <?php echo htmlspecialchars($payment_details['phone']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Payment Receipt -->
                        <div class="card mt-4">
                            <div class="card-header">
                                <h3>Payment Receipt</h3>
                            </div>
                            <div class="card-body">
                                <div class="text-center">
                                    <h4>HealthSure Insurance</h4>
                                    <p>Payment Receipt</p>
                                    <hr>
                                </div>
                                
                                <div class="mb-2">
                                    <strong>Receipt #:</strong> <?php echo $payment_details['payment_id']; ?>
                                </div>
                                <div class="mb-2">
                                    <strong>Date:</strong> <?php echo format_date($payment_details['payment_date']); ?>
                                </div>
                                <div class="mb-2">
                                    <strong>Customer:</strong> <?php echo htmlspecialchars($payment_details['first_name'] . ' ' . $payment_details['last_name']); ?>
                                </div>
                                <div class="mb-2">
                                    <strong>Policy:</strong> <?php echo htmlspecialchars($payment_details['policy_name']); ?>
                                </div>
                                <div class="mb-2">
                                    <strong>Type:</strong> <?php echo ucfirst(str_replace('_', ' ', $payment_details['payment_type'])); ?>
                                </div>
                                <hr>
                                <div class="mb-2" style="font-size: 1.1rem;">
                                    <strong>Amount Paid:</strong> <?php echo format_currency($payment_details['amount']); ?>
                                </div>
                                <div class="mb-2">
                                    <strong>Method:</strong> <?php echo ucfirst(str_replace('_', ' ', $payment_details['payment_method'])); ?>
                                </div>
                                <?php if ($payment_details['transaction_id']): ?>
                                    <div class="mb-2">
                                        <strong>Transaction ID:</strong> <?php echo htmlspecialchars($payment_details['transaction_id']); ?>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="text-center mt-3">
                                    <button onclick="window.print()" class="btn btn-sm btn-primary">Print Receipt</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-4">
                    <a href="payments.php" class="btn btn-outline">Back to Payments</a>
                </div>
            <?php else: ?>
                <!-- Payments List -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3>Payment Transactions</h3>
                        <div>
                            <select onchange="window.location.href='?type=' + this.value" class="form-control form-select" style="width: auto; display: inline-block;">
                                <option value="all" <?php echo $type_filter === 'all' ? 'selected' : ''; ?>>All Payments</option>
                                <option value="premium" <?php echo $type_filter === 'premium' ? 'selected' : ''; ?>>Premium Payments</option>
                                <option value="claim_settlement" <?php echo $type_filter === 'claim_settlement' ? 'selected' : ''; ?>>Claim Settlements</option>
                            </select>
                        </div>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Payment ID</th>
                                    <th>Customer</th>
                                    <th>Policy</th>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th>Method</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($payments as $payment): ?>
                                    <tr>
                                        <td>
                                            <strong>#<?php echo $payment['payment_id']; ?></strong>
                                            <?php if ($payment['transaction_id']): ?>
                                                <br><small class="text-light"><?php echo htmlspecialchars($payment['transaction_id']); ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($payment['first_name'] . ' ' . $payment['last_name']); ?>
                                            <br><small class="text-light"><?php echo htmlspecialchars($payment['email']); ?></small>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($payment['policy_name']); ?>
                                            <br><span class="badge badge-primary"><?php echo ucfirst($payment['policy_type']); ?></span>
                                        </td>
                                        <td>
                                            <span class="badge badge-<?php echo $payment['payment_type'] === 'premium' ? 'success' : 'warning'; ?>">
                                                <?php echo ucfirst(str_replace('_', ' ', $payment['payment_type'])); ?>
                                            </span>
                                        </td>
                                        <td><?php echo format_currency($payment['amount']); ?></td>
                                        <td><?php echo ucfirst(str_replace('_', ' ', $payment['payment_method'])); ?></td>
                                        <td><?php echo format_date($payment['payment_date']); ?></td>
                                        <td>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="payment_id" value="<?php echo $payment['payment_id']; ?>">
                                                <select name="status" onchange="this.form.submit()" class="form-control form-select" style="width: auto;">
                                                    <option value="pending" <?php echo $payment['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                    <option value="completed" <?php echo $payment['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                                    <option value="failed" <?php echo $payment['status'] === 'failed' ? 'selected' : ''; ?>>Failed</option>
                                                </select>
                                                <input type="hidden" name="update_status" value="1">
                                            </form>
                                        </td>
                                        <td>
                                            <a href="?action=view&payment_id=<?php echo $payment['payment_id']; ?>" class="btn btn-sm btn-primary">View</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        
                        <?php if (empty($payments)): ?>
                            <div class="text-center py-4">
                                <p class="text-light">No payments found.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <script src="../assets/js/main.js"></script>
</body>
</html>
