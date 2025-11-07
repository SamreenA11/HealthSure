<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

check_role('admin');

$message = get_flash_message();
$action = $_GET['action'] ?? 'list';
$claim_id = $_GET['claim_id'] ?? null;
$status_filter = $_GET['status'] ?? 'all';

// Handle form submissions
if ($_POST) {
    if (isset($_POST['process_claim'])) {
        $claim_id = (int)$_POST['claim_id'];
        $status = $_POST['status'];
        $approved_amount = (float)($_POST['approved_amount'] ?? 0);
        $admin_notes = sanitize_input($_POST['admin_notes']);
        
        try {
            $conn->beginTransaction();
            
            // Update claim status
            $stmt = $conn->prepare("UPDATE claims SET status = ?, approved_amount = ?, admin_notes = ?, processed_by = ?, processed_date = CURDATE() WHERE claim_id = ?");
            $stmt->execute([$status, $approved_amount, $admin_notes, $_SESSION['user_id'], $claim_id]);
            
            // If approved, create payment record
            if ($status === 'approved' && $approved_amount > 0) {
                // Get claim details
                $stmt = $conn->prepare("SELECT holder_id FROM claims WHERE claim_id = ?");
                $stmt->execute([$claim_id]);
                $holder_id = $stmt->fetch()['holder_id'];
                
                // Create payment record
                $stmt = $conn->prepare("INSERT INTO payments (holder_id, claim_id, payment_type, amount, payment_method, payment_date, status) VALUES (?, ?, 'claim_settlement', ?, 'bank_transfer', CURDATE(), 'completed')");
                $stmt->execute([$holder_id, $claim_id, $approved_amount]);
            }
            
            $conn->commit();
            set_flash_message('success', 'Claim processed successfully!');
            redirect('claims.php');
        } catch (PDOException $e) {
            $conn->rollBack();
            set_flash_message('danger', 'Error processing claim: ' . $e->getMessage());
        }
    }
}

// Build query based on status filter
$where_clause = "";
$params = [];
if ($status_filter !== 'all') {
    $where_clause = "WHERE c.status = ?";
    $params[] = $status_filter;
}

// Get all claims with customer and policy details
try {
    $stmt = $conn->prepare("SELECT c.*, 
                           cu.first_name, cu.last_name, cu.phone,
                           u.email,
                           p.policy_name, p.policy_type,
                           ph.premium_amount, ph.coverage_amount,
                           a.first_name as agent_first_name, a.last_name as agent_last_name,
                           pu.email as processed_by_email
                           FROM claims c
                           JOIN policy_holders ph ON c.holder_id = ph.holder_id
                           JOIN customers cu ON ph.customer_id = cu.customer_id
                           JOIN users u ON cu.user_id = u.user_id
                           JOIN policies p ON ph.policy_id = p.policy_id
                           LEFT JOIN agents a ON cu.agent_id = a.agent_id
                           LEFT JOIN users pu ON c.processed_by = pu.user_id
                           $where_clause
                           ORDER BY c.created_at DESC");
    $stmt->execute($params);
    $claims = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $claims = [];
    set_flash_message('warning', 'No claims data available yet. Create some customers and policies first.');
}

// Get claim details for view/process
$claim_details = null;
if (($action === 'view' || $action === 'process') && $claim_id) {
    $stmt = $conn->prepare("SELECT c.*, 
                           cu.first_name, cu.last_name, cu.phone, cu.address,
                           u.email,
                           p.policy_name, p.policy_type, p.description as policy_description,
                           ph.premium_amount, ph.coverage_amount, ph.start_date, ph.end_date,
                           a.first_name as agent_first_name, a.last_name as agent_last_name,
                           pu.email as processed_by_email
                           FROM claims c
                           JOIN policy_holders ph ON c.holder_id = ph.holder_id
                           JOIN customers cu ON ph.customer_id = cu.customer_id
                           JOIN users u ON cu.user_id = u.user_id
                           JOIN policies p ON ph.policy_id = p.policy_id
                           LEFT JOIN agents a ON cu.agent_id = a.agent_id
                           LEFT JOIN users pu ON c.processed_by = pu.user_id
                           WHERE c.claim_id = ?");
    $stmt->execute([$claim_id]);
    $claim_details = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Get statistics
try {
    $stmt = $conn->query("SELECT 
                         COUNT(*) as total_claims,
                         SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_claims,
                         SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved_claims,
                         SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected_claims,
                         SUM(CASE WHEN status = 'approved' THEN approved_amount ELSE 0 END) as total_approved_amount
                         FROM claims");
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $stats = [
        'total_claims' => 0,
        'pending_claims' => 0,
        'approved_claims' => 0,
        'rejected_claims' => 0,
        'total_approved_amount' => 0
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Claims Management - HealthSure</title>
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
                <h1>Claims Management</h1>
                <div>
                    <a href="?status=pending" class="btn btn-warning btn-sm">Pending Claims</a>
                    <a href="?status=all" class="btn btn-outline btn-sm">All Claims</a>
                </div>
            </div>
            
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message['type']; ?>">
                    <?php echo $message['message']; ?>
                </div>
            <?php endif; ?>
            
            <!-- Statistics Cards -->
            <div class="stats-grid mb-4">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['total_claims']; ?></div>
                    <div class="stat-label">Total Claims</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['pending_claims']; ?></div>
                    <div class="stat-label">Pending Claims</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['approved_claims']; ?></div>
                    <div class="stat-label">Approved Claims</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['rejected_claims']; ?></div>
                    <div class="stat-label">Rejected Claims</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo format_currency($stats['total_approved_amount']); ?></div>
                    <div class="stat-label">Total Approved Amount</div>
                </div>
            </div>
            
            <?php if (($action === 'view' || $action === 'process') && $claim_details): ?>
                <!-- Claim Details View/Process -->
                <div class="row">
                    <div class="col-6">
                        <div class="card">
                            <div class="card-header">
                                <h3>Claim Information</h3>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <strong>Claim ID:</strong><br>
                                    #<?php echo $claim_details['claim_id']; ?>
                                </div>
                                <div class="mb-3">
                                    <strong>Customer:</strong><br>
                                    <?php echo htmlspecialchars($claim_details['first_name'] . ' ' . $claim_details['last_name']); ?>
                                    <br><small><?php echo htmlspecialchars($claim_details['email']); ?></small>
                                    <?php if ($claim_details['phone']): ?>
                                        <br><small><?php echo htmlspecialchars($claim_details['phone']); ?></small>
                                    <?php endif; ?>
                                </div>
                                <div class="mb-3">
                                    <strong>Policy:</strong><br>
                                    <?php echo htmlspecialchars($claim_details['policy_name']); ?>
                                    <br><span class="badge badge-primary"><?php echo ucfirst($claim_details['policy_type']); ?></span>
                                </div>
                                <div class="mb-3">
                                    <strong>Coverage Amount:</strong><br>
                                    <?php echo format_currency($claim_details['coverage_amount']); ?>
                                </div>
                                <div class="mb-3">
                                    <strong>Claim Amount:</strong><br>
                                    <?php echo format_currency($claim_details['claim_amount']); ?>
                                </div>
                                <div class="mb-3">
                                    <strong>Claim Date:</strong><br>
                                    <?php echo format_date($claim_details['claim_date']); ?>
                                </div>
                                <div class="mb-3">
                                    <strong>Status:</strong><br>
                                    <span class="badge badge-<?php echo $claim_details['status'] === 'approved' ? 'success' : ($claim_details['status'] === 'rejected' ? 'danger' : 'warning'); ?>">
                                        <?php echo ucfirst($claim_details['status']); ?>
                                    </span>
                                </div>
                                <?php if ($claim_details['agent_first_name']): ?>
                                    <div class="mb-3">
                                        <strong>Assigned Agent:</strong><br>
                                        <?php echo htmlspecialchars($claim_details['agent_first_name'] . ' ' . $claim_details['agent_last_name']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-6">
                        <div class="card">
                            <div class="card-header">
                                <h3>Claim Details</h3>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <strong>Claim Reason:</strong><br>
                                    <p><?php echo nl2br(htmlspecialchars($claim_details['claim_reason'])); ?></p>
                                </div>
                                
                                <?php if ($claim_details['documents']): ?>
                                    <div class="mb-3">
                                        <strong>Documents:</strong><br>
                                        <p><?php echo nl2br(htmlspecialchars($claim_details['documents'])); ?></p>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($claim_details['status'] !== 'pending'): ?>
                                    <div class="mb-3">
                                        <strong>Processed Date:</strong><br>
                                        <?php echo $claim_details['processed_date'] ? format_date($claim_details['processed_date']) : 'Not processed'; ?>
                                    </div>
                                    
                                    <?php if ($claim_details['approved_amount'] > 0): ?>
                                        <div class="mb-3">
                                            <strong>Approved Amount:</strong><br>
                                            <?php echo format_currency($claim_details['approved_amount']); ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($claim_details['admin_notes']): ?>
                                        <div class="mb-3">
                                            <strong>Admin Notes:</strong><br>
                                            <p><?php echo nl2br(htmlspecialchars($claim_details['admin_notes'])); ?></p>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($claim_details['processed_by_email']): ?>
                                        <div class="mb-3">
                                            <strong>Processed By:</strong><br>
                                            <?php echo htmlspecialchars($claim_details['processed_by_email']); ?>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <?php if ($action === 'process' && $claim_details['status'] === 'pending'): ?>
                            <!-- Process Claim Form -->
                            <div class="card mt-4">
                                <div class="card-header">
                                    <h3>Process Claim</h3>
                                </div>
                                <div class="card-body">
                                    <form method="POST" action="">
                                        <input type="hidden" name="claim_id" value="<?php echo $claim_details['claim_id']; ?>">
                                        
                                        <div class="form-group">
                                            <label for="status" class="form-label">Decision *</label>
                                            <select id="status" name="status" class="form-control form-select" required onchange="toggleApprovedAmount(this.value)">
                                                <option value="">Select Decision</option>
                                                <option value="approved">Approve Claim</option>
                                                <option value="rejected">Reject Claim</option>
                                            </select>
                                        </div>
                                        
                                        <div class="form-group" id="approved_amount_group" style="display: none;">
                                            <label for="approved_amount" class="form-label">Approved Amount (₹)</label>
                                            <input type="number" id="approved_amount" name="approved_amount" class="form-control" 
                                                   step="0.01" max="<?php echo $claim_details['claim_amount']; ?>" 
                                                   value="<?php echo $claim_details['claim_amount']; ?>">
                                            <small class="text-light">Maximum: <?php echo format_currency($claim_details['claim_amount']); ?></small>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="admin_notes" class="form-label">Admin Notes</label>
                                            <textarea id="admin_notes" name="admin_notes" class="form-control" rows="4" 
                                                      placeholder="Add notes about the decision..."></textarea>
                                        </div>
                                        
                                        <div class="d-flex" style="gap: 1rem;">
                                            <button type="submit" name="process_claim" class="btn btn-primary">Process Claim</button>
                                            <a href="claims.php" class="btn btn-outline">Cancel</a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="mt-4">
                    <a href="claims.php" class="btn btn-outline">Back to Claims</a>
                </div>
            <?php else: ?>
                <!-- Claims List -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3>Claims List</h3>
                        <div>
                            <select onchange="window.location.href='?status=' + this.value" class="form-control form-select" style="width: auto; display: inline-block;">
                                <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All Claims</option>
                                <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="approved" <?php echo $status_filter === 'approved' ? 'selected' : ''; ?>>Approved</option>
                                <option value="rejected" <?php echo $status_filter === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                            </select>
                        </div>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Claim ID</th>
                                    <th>Customer</th>
                                    <th>Policy</th>
                                    <th>Claim Amount</th>
                                    <th>Approved Amount</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($claims as $claim): ?>
                                    <tr>
                                        <td>
                                            <strong>#<?php echo $claim['claim_id']; ?></strong>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($claim['first_name'] . ' ' . $claim['last_name']); ?>
                                            <br><small class="text-light"><?php echo htmlspecialchars($claim['email']); ?></small>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($claim['policy_name']); ?>
                                            <br><span class="badge badge-primary"><?php echo ucfirst($claim['policy_type']); ?></span>
                                        </td>
                                        <td><?php echo format_currency($claim['claim_amount']); ?></td>
                                        <td>
                                            <?php if ($claim['approved_amount'] > 0): ?>
                                                <?php echo format_currency($claim['approved_amount']); ?>
                                            <?php else: ?>
                                                <span class="text-light">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge badge-<?php echo $claim['status'] === 'approved' ? 'success' : ($claim['status'] === 'rejected' ? 'danger' : 'warning'); ?>">
                                                <?php echo ucfirst($claim['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo format_date($claim['claim_date']); ?></td>
                                        <td>
                                            <a href="?action=view&claim_id=<?php echo $claim['claim_id']; ?>" class="btn btn-sm btn-outline">View</a>
                                            <?php if ($claim['status'] === 'pending'): ?>
                                                <a href="?action=process&claim_id=<?php echo $claim['claim_id']; ?>" class="btn btn-sm btn-primary">Process</a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        
                        <?php if (empty($claims)): ?>
                            <div class="text-center py-4">
                                <p class="text-light">No claims found.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        function toggleApprovedAmount(status) {
            const approvedAmountGroup = document.getElementById('approved_amount_group');
            if (status === 'approved') {
                approvedAmountGroup.style.display = 'block';
                document.getElementById('approved_amount').required = true;
            } else {
                approvedAmountGroup.style.display = 'none';
                document.getElementById('approved_amount').required = false;
            }
        }
    </script>
    <script src="../assets/js/main.js"></script>
</body>
</html>
