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
    $payment_type = $_POST['payment_type'];
    $amount = (float)$_POST['amount'];
    $payment_method = $_POST['payment_method'];
    $transaction_id = sanitize_input($_POST['transaction_id']);
    
    // Validation
    if (empty($holder_id) || empty($payment_type) || empty($amount) || empty($payment_method)) {
        $error = 'Please fill in all required fields';
    } elseif ($amount <= 0) {
        $error = 'Payment amount must be greater than zero';
    } else {
        // Verify policy belongs to customer
        $stmt = $conn->prepare("SELECT ph.*, p.policy_name FROM policy_holders ph 
                               JOIN policies p ON ph.policy_id = p.policy_id 
                               WHERE ph.holder_id = ? AND ph.customer_id = ? AND ph.status = 'active'");
        $stmt->execute([$holder_id, $customer_info['customer_id']]);
        $policy = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$policy) {
            $error = 'Invalid policy selected';
        } else {
            try {
                $stmt = $conn->prepare("INSERT INTO payments (holder_id, payment_type, amount, payment_method, payment_date, transaction_id, status) VALUES (?, ?, ?, ?, CURDATE(), ?, 'completed')");
                $stmt->execute([$holder_id, $payment_type, $amount, $payment_method, $transaction_id]);
                
                set_flash_message('success', 'Payment of ' . format_currency($amount) . ' processed successfully! Transaction ID: #' . $conn->lastInsertId());
                redirect('payment-history.php');
            } catch (PDOException $e) {
                $error = 'Failed to process payment. Please try again.';
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
    <title>Make Payment - HealthSure</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/animations.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <?php include 'includes/header.php'; ?>
        
        <div class="container">
            <h1>Make Payment</h1>
            <p class="text-light">Pay your insurance premiums or other policy-related fees.</p>
            
            <?php if (empty($active_policies)): ?>
                <div class="card">
                    <div class="card-body text-center">
                        <h4>No Active Policies</h4>
                        <p class="text-light">You don't have any active insurance policies to make payments for.</p>
                        <a href="browse-policies.php" class="btn btn-primary">Browse Policies</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="row">
                    <div class="col-8">
                        <div class="card">
                            <div class="card-header">
                                <h3>Payment Details</h3>
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
                                                        data-premium="<?php echo $policy['premium_amount']; ?>"
                                                        data-coverage="<?php echo $policy['coverage_amount']; ?>"
                                                        data-type="<?php echo $policy['policy_type']; ?>"
                                                        data-name="<?php echo htmlspecialchars($policy['policy_name']); ?>"
                                                        <?php echo ($policy_id == $policy['holder_id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($policy['policy_name']); ?> 
                                                    (Premium: <?php echo format_currency($policy['premium_amount']); ?>)
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
                                                <label for="payment_type" class="form-label">Payment Type *</label>
                                                <select id="payment_type" name="payment_type" class="form-control form-select" required>
                                                    <option value="">Select payment type</option>
                                                    <option value="premium">Premium Payment</option>
                                                    <option value="renewal">Policy Renewal</option>
                                                    <option value="late_fee">Late Fee</option>
                                                    <option value="other">Other Fees</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="form-group">
                                                <label for="amount" class="form-label">Amount (₹) *</label>
                                                <input type="number" id="amount" name="amount" class="form-control" 
                                                       step="0.01" min="1" required 
                                                       value="<?php echo $_POST['amount'] ?? ''; ?>">
                                                <small class="text-light">
                                                    <button type="button" class="btn btn-sm btn-outline mt-1" onclick="setPremiumAmount()" id="set-premium-btn" style="display: none;">
                                                        Use Premium Amount
                                                    </button>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="payment_method" class="form-label">Payment Method *</label>
                                        <select id="payment_method" name="payment_method" class="form-control form-select" required>
                                            <option value="">Select payment method</option>
                                            <option value="online">Online Payment (UPI/Net Banking)</option>
                                            <option value="card">Credit/Debit Card</option>
                                            <option value="bank_transfer">Bank Transfer</option>
                                            <option value="cash">Cash (Office Visit)</option>
                                        </select>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="transaction_id" class="form-label">Transaction Reference (Optional)</label>
                                        <input type="text" id="transaction_id" name="transaction_id" class="form-control" 
                                               placeholder="Bank reference number or transaction ID"
                                               value="<?php echo $_POST['transaction_id'] ?? ''; ?>">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label class="d-flex align-items-center">
                                            <input type="checkbox" required style="margin-right: 0.5rem;">
                                            I confirm that the payment details are correct and authorize this transaction.
                                        </label>
                                    </div>
                                    
                                    <div class="d-flex" style="gap: 1rem;">
                                        <button type="submit" class="btn btn-primary">Process Payment</button>
                                        <a href="dashboard.php" class="btn btn-outline">Cancel</a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-4">
                        <div class="card">
                            <div class="card-header">
                                <h3>Payment Information</h3>
                            </div>
                            <div class="card-body">
                                <h6>Accepted Payment Methods:</h6>
                                <ul style="font-size: 0.875rem;">
                                    <li><strong>Online:</strong> UPI, Net Banking, Wallets</li>
                                    <li><strong>Cards:</strong> Visa, MasterCard, RuPay</li>
                                    <li><strong>Bank Transfer:</strong> NEFT, RTGS, IMPS</li>
                                    <li><strong>Cash:</strong> Visit nearest office</li>
                                </ul>
                                
                                <h6 class="mt-3">Processing Time:</h6>
                                <ul style="font-size: 0.875rem;">
                                    <li>Online/Card: Instant</li>
                                    <li>Bank Transfer: 1-2 hours</li>
                                    <li>Cash: Immediate</li>
                                </ul>
                                
                                <h6 class="mt-3">Important Notes:</h6>
                                <ul style="font-size: 0.875rem;">
                                    <li>Keep transaction reference for records</li>
                                    <li>Payment confirmation via email/SMS</li>
                                    <li>Policy remains active after payment</li>
                                    <li>Late fees may apply after due date</li>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="card mt-4">
                            <div class="card-header">
                                <h3>Need Help?</h3>
                            </div>
                            <div class="card-body">
                                <p style="font-size: 0.875rem;">Having trouble with your payment? Contact our support team.</p>
                                <div class="d-flex flex-column" style="gap: 0.5rem;">
                                    <button class="btn btn-secondary btn-sm" onclick="alert('Support: +91-1800-123-4567')">📞 Call Support</button>
                                    <a href="support.php" class="btn btn-outline btn-sm">💬 Live Chat</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        let currentPremium = 0;
        
        function updatePolicyInfo(holderId) {
            const select = document.getElementById('holder_id');
            const option = select.options[select.selectedIndex];
            const infoDiv = document.getElementById('policy-info');
            const detailsDiv = document.getElementById('policy-details');
            const setPremiumBtn = document.getElementById('set-premium-btn');
            
            if (holderId && option) {
                const premium = option.getAttribute('data-premium');
                const coverage = option.getAttribute('data-coverage');
                const type = option.getAttribute('data-type');
                const name = option.getAttribute('data-name');
                
                currentPremium = parseFloat(premium);
                
                detailsDiv.innerHTML = `
                    <div>Policy: ${name}</div>
                    <div>Type: ${type.charAt(0).toUpperCase() + type.slice(1)} Insurance</div>
                    <div>Annual Premium: ₹${parseFloat(premium).toLocaleString('en-IN')}</div>
                    <div>Coverage: ₹${parseFloat(coverage).toLocaleString('en-IN')}</div>
                `;
                infoDiv.style.display = 'block';
                setPremiumBtn.style.display = 'inline-block';
            } else {
                infoDiv.style.display = 'none';
                setPremiumBtn.style.display = 'none';
                currentPremium = 0;
            }
        }
        
        function setPremiumAmount() {
            if (currentPremium > 0) {
                document.getElementById('amount').value = currentPremium;
                document.getElementById('payment_type').value = 'premium';
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
