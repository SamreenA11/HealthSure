<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

check_role('customer');

$customer_info = get_customer_info($_SESSION['user_id'], $conn);
$policy_id = $_GET['policy_id'] ?? null;

// Get customer's active policies
$stmt = $conn->prepare("SELECT ph.*, p.policy_name, p.policy_type, p.coverage_amount 
                       FROM policy_holders ph 
                       JOIN policies p ON ph.policy_id = p.policy_id 
                       WHERE ph.customer_id = ? AND ph.status = 'active'
                       ORDER BY p.policy_name");
$stmt->execute([$customer_info['customer_id']]);
$active_policies = $stmt->fetchAll(PDO::FETCH_ASSOC);

$error = '';
$success = '';

if ($_POST) {
    $holder_id = (int)$_POST['holder_id'];
    $claim_amount = (float)$_POST['claim_amount'];
    $claim_reason = sanitize_input($_POST['claim_reason']);
    $claim_date = $_POST['claim_date'];
    $documents = sanitize_input($_POST['documents']);
    
    // Validation
    if (empty($holder_id) || empty($claim_amount) || empty($claim_reason) || empty($claim_date)) {
        $error = 'Please fill in all required fields';
    } elseif ($claim_amount <= 0) {
        $error = 'Claim amount must be greater than zero';
    } elseif (strtotime($claim_date) > time()) {
        $error = 'Claim date cannot be in the future';
    } else {
        // Verify policy belongs to customer
        $stmt = $conn->prepare("SELECT ph.*, p.coverage_amount FROM policy_holders ph 
                               JOIN policies p ON ph.policy_id = p.policy_id 
                               WHERE ph.holder_id = ? AND ph.customer_id = ? AND ph.status = 'active'");
        $stmt->execute([$holder_id, $customer_info['customer_id']]);
        $policy = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$policy) {
            $error = 'Invalid policy selected';
        } elseif ($claim_amount > $policy['coverage_amount']) {
            $error = 'Claim amount cannot exceed coverage amount of ' . format_currency($policy['coverage_amount']);
        } else {
            try {
                $stmt = $conn->prepare("INSERT INTO claims (holder_id, claim_amount, claim_reason, claim_date, documents, status) VALUES (?, ?, ?, ?, ?, 'pending')");
                $stmt->execute([$holder_id, $claim_amount, $claim_reason, $claim_date, $documents]);
                
                set_flash_message('success', 'Claim filed successfully! Your claim ID is #' . $conn->lastInsertId());
                redirect('my-claims.php');
            } catch (PDOException $e) {
                $error = 'Failed to file claim. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File New Claim - HealthSure</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/animations.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <?php include 'includes/header.php'; ?>
        
        <div class="container">
            <h1>File New Claim</h1>
            <p class="text-light">Submit a claim for your insurance policy. Please provide accurate information for faster processing.</p>
            
            <?php if (empty($active_policies)): ?>
                <div class="card">
                    <div class="card-body text-center">
                        <h4>No Active Policies</h4>
                        <p class="text-light">You don't have any active insurance policies to file a claim against.</p>
                        <a href="browse-policies.php" class="btn btn-primary">Browse Policies</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="row">
                    <div class="col-8">
                        <div class="card">
                            <div class="card-header">
                                <h3>Claim Information</h3>
                            </div>
                            <div class="card-body">
                                <?php if ($error): ?>
                                    <div class="alert alert-danger"><?php echo $error; ?></div>
                                <?php endif; ?>
                                
                                <form method="POST" action="">
                                    <div class="form-group">
                                        <label for="holder_id" class="form-label">Select Policy *</label>
                                        <select id="holder_id" name="holder_id" class="form-control form-select" required onchange="updatePolicyInfo(this.value)">
                                            <option value="">Select a policy</option>
                                            <?php foreach ($active_policies as $policy): ?>
                                                <option value="<?php echo $policy['holder_id']; ?>" 
                                                        data-coverage="<?php echo $policy['coverage_amount']; ?>"
                                                        data-type="<?php echo $policy['policy_type']; ?>"
                                                        <?php echo ($policy_id == $policy['holder_id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($policy['policy_name']); ?> 
                                                    (Coverage: <?php echo format_currency($policy['coverage_amount']); ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    
                                    <div id="policy-info" class="alert alert-info" style="display: none;">
                                        <strong>Policy Details:</strong>
                                        <div id="policy-details"></div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="form-group">
                                                <label for="claim_amount" class="form-label">Claim Amount (₹) *</label>
                                                <input type="number" id="claim_amount" name="claim_amount" class="form-control" 
                                                       step="0.01" min="1" required 
                                                       value="<?php echo $_POST['claim_amount'] ?? ''; ?>">
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="form-group">
                                                <label for="claim_date" class="form-label">Incident Date *</label>
                                                <input type="date" id="claim_date" name="claim_date" class="form-control" 
                                                       max="<?php echo date('Y-m-d'); ?>" required
                                                       value="<?php echo $_POST['claim_date'] ?? date('Y-m-d'); ?>">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="claim_reason" class="form-label">Claim Reason/Description *</label>
                                        <textarea id="claim_reason" name="claim_reason" class="form-control" rows="4" 
                                                  placeholder="Describe the medical condition, treatment, or incident..." required><?php echo $_POST['claim_reason'] ?? ''; ?></textarea>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="documents" class="form-label">Supporting Documents</label>
                                        <textarea id="documents" name="documents" class="form-control" rows="3" 
                                                  placeholder="List the documents you have (medical reports, bills, prescriptions, etc.)"><?php echo $_POST['documents'] ?? ''; ?></textarea>
                                        <small class="text-light">You can upload actual documents after submitting this form.</small>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label class="d-flex align-items-center">
                                            <input type="checkbox" required style="margin-right: 0.5rem;">
                                            I declare that the information provided is true and accurate to the best of my knowledge.
                                        </label>
                                    </div>
                                    
                                    <div class="d-flex" style="gap: 1rem;">
                                        <button type="submit" class="btn btn-primary">Submit Claim</button>
                                        <a href="my-policies.php" class="btn btn-outline">Cancel</a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-4">
                        <div class="card">
                            <div class="card-header">
                                <h3>Claim Guidelines</h3>
                            </div>
                            <div class="card-body">
                                <h6>Required Information:</h6>
                                <ul style="font-size: 0.875rem;">
                                    <li>Valid policy with active status</li>
                                    <li>Accurate claim amount</li>
                                    <li>Detailed description of incident</li>
                                    <li>Supporting medical documents</li>
                                </ul>
                                
                                <h6 class="mt-3">Processing Time:</h6>
                                <ul style="font-size: 0.875rem;">
                                    <li>Initial review: 2-3 business days</li>
                                    <li>Document verification: 5-7 days</li>
                                    <li>Final approval: 10-15 days</li>
                                </ul>
                                
                                <h6 class="mt-3">Important Notes:</h6>
                                <ul style="font-size: 0.875rem;">
                                    <li>Claims must be filed within 30 days</li>
                                    <li>All information must be accurate</li>
                                    <li>False claims will result in policy cancellation</li>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="card mt-4">
                            <div class="card-header">
                                <h3>Need Help?</h3>
                            </div>
                            <div class="card-body">
                                <p style="font-size: 0.875rem;">If you need assistance with filing your claim, please contact our support team.</p>
                                <a href="support.php" class="btn btn-secondary btn-sm" style="width: 100%;">Contact Support</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        function updatePolicyInfo(holderId) {
            const select = document.getElementById('holder_id');
            const option = select.options[select.selectedIndex];
            const infoDiv = document.getElementById('policy-info');
            const detailsDiv = document.getElementById('policy-details');
            
            if (holderId && option) {
                const coverage = option.getAttribute('data-coverage');
                const type = option.getAttribute('data-type');
                
                detailsDiv.innerHTML = `
                    <div>Policy Type: ${type.charAt(0).toUpperCase() + type.slice(1)} Insurance</div>
                    <div>Maximum Coverage: ₹${parseFloat(coverage).toLocaleString('en-IN')}</div>
                `;
                infoDiv.style.display = 'block';
                
                // Update claim amount max value
                document.getElementById('claim_amount').setAttribute('max', coverage);
            } else {
                infoDiv.style.display = 'none';
            }
        }
        
        // Auto-select policy if passed in URL
        <?php if ($policy_id): ?>
            updatePolicyInfo('<?php echo $policy_id; ?>');
        <?php endif; ?>
    </script>
    <script src="../assets/js/main.js"></script>
</body>
</html>
