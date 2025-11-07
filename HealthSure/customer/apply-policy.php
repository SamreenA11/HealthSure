<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

check_role('customer');

$customer_info = get_customer_info($_SESSION['user_id'], $conn);

if (!isset($_GET['policy_id'])) {
    redirect('browse-policies.php');
}

$policy_id = (int)$_GET['policy_id'];

// Get policy details
$stmt = $conn->prepare("SELECT * FROM policies WHERE policy_id = ? AND status = 'active'");
$stmt->execute([$policy_id]);
$policy = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$policy) {
    set_flash_message('danger', 'Policy not found or not available.');
    redirect('browse-policies.php');
}

// Get policy-specific details
function getPolicyDetails($policy_id, $policy_type, $conn) {
    switch ($policy_type) {
        case 'health':
            $stmt = $conn->prepare("SELECT * FROM health_policies WHERE policy_id = ?");
            break;
        case 'life':
            $stmt = $conn->prepare("SELECT * FROM life_policies WHERE policy_id = ?");
            break;
        case 'family':
            $stmt = $conn->prepare("SELECT * FROM family_policies WHERE policy_id = ?");
            break;
        default:
            return null;
    }
    $stmt->execute([$policy_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

$policy_details = getPolicyDetails($policy_id, $policy['policy_type'], $conn);

$error = '';
$success = '';

if ($_POST) {
    $nominee_name = sanitize_input($_POST['nominee_name'] ?? '');
    $nominee_relation = sanitize_input($_POST['nominee_relation'] ?? '');
    $start_date = $_POST['start_date'];
    
    // Validation
    if (empty($start_date)) {
        $error = 'Please select a start date for the policy';
    } elseif (strtotime($start_date) < strtotime('today')) {
        $error = 'Start date cannot be in the past';
    } else {
        try {
            // Check if customer already has this policy
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM policy_holders 
                                   WHERE customer_id = ? AND policy_id = ? AND status = 'active'");
            $stmt->execute([$customer_info['customer_id'], $policy_id]);
            $existing = $stmt->fetch()['count'];
            
            if ($existing > 0) {
                $error = 'You already have an active policy of this type';
            } else {
                // Calculate end date
                $end_date = date('Y-m-d', strtotime($start_date . ' + ' . $policy['duration_years'] . ' years'));
                
                // Start transaction
                $conn->beginTransaction();
                
                // Create policy holder record
                $stmt = $conn->prepare("INSERT INTO policy_holders 
                                       (customer_id, policy_id, start_date, end_date, premium_amount, status) 
                                       VALUES (?, ?, ?, ?, ?, 'active')");
                $stmt->execute([
                    $customer_info['customer_id'], 
                    $policy_id, 
                    $start_date, 
                    $end_date, 
                    $policy['base_premium']
                ]);
                
                $holder_id = $conn->lastInsertId();
                
                // Update life policy with nominee details if applicable
                if ($policy['policy_type'] === 'life' && !empty($nominee_name)) {
                    $stmt = $conn->prepare("UPDATE life_policies SET nominee_name = ?, nominee_relation = ? WHERE policy_id = ?");
                    $stmt->execute([$nominee_name, $nominee_relation, $policy_id]);
                }
                
                $conn->commit();
                
                set_flash_message('success', 'Policy application successful! Your policy is now active.');
                redirect('my-policies.php');
            }
        } catch (PDOException $e) {
            $conn->rollBack();
            $error = 'Application failed. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply for Policy - HealthSure</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/animations.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <?php include 'includes/header.php'; ?>
        
        <div class="container">
            <h1>Apply for Insurance Policy</h1>
            <p class="text-light">Complete your application for the selected insurance policy.</p>
            
            <div class="row">
                <!-- Policy Details -->
                <div class="col-6">
                    <div class="card">
                        <div class="card-header">
                            <h3>Policy Details</h3>
                        </div>
                        <div class="card-body">
                            <h4><?php echo htmlspecialchars($policy['policy_name']); ?></h4>
                            <span class="badge badge-primary mb-3"><?php echo ucfirst($policy['policy_type']); ?> Insurance</span>
                            
                            <p class="text-light"><?php echo htmlspecialchars($policy['description']); ?></p>
                            
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Coverage Amount:</span>
                                    <strong><?php echo format_currency($policy['coverage_amount']); ?></strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Annual Premium:</span>
                                    <strong><?php echo format_currency($policy['base_premium']); ?></strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Policy Term:</span>
                                    <strong><?php echo $policy['duration_years']; ?> Year(s)</strong>
                                </div>
                            </div>
                            
                            <?php if ($policy_details): ?>
                                <div style="border-top: 1px solid var(--border-color); padding-top: 1rem;">
                                    <h6>Key Features:</h6>
                                    <?php if ($policy['policy_type'] === 'health'): ?>
                                        <ul style="font-size: 0.875rem; color: var(--text-light);">
                                            <li>Cashless Limit: <?php echo format_currency($policy_details['cashless_limit']); ?></li>
                                            <li>Pre-existing Conditions: <?php echo $policy_details['pre_existing_conditions'] ? 'Covered' : 'Not Covered'; ?></li>
                                            <li>Network Hospitals: <?php echo htmlspecialchars($policy_details['network_hospitals']); ?></li>
                                        </ul>
                                    <?php elseif ($policy['policy_type'] === 'life'): ?>
                                        <ul style="font-size: 0.875rem; color: var(--text-light);">
                                            <li>Term: <?php echo $policy_details['term_years']; ?> years</li>
                                            <li>Death Benefit: <?php echo format_currency($policy_details['death_benefit']); ?></li>
                                            <li>Maturity Benefit: <?php echo format_currency($policy_details['maturity_benefit']); ?></li>
                                        </ul>
                                    <?php elseif ($policy['policy_type'] === 'family'): ?>
                                        <ul style="font-size: 0.875rem; color: var(--text-light);">
                                            <li>Max Dependents: <?php echo $policy_details['no_of_dependents']; ?></li>
                                            <li>Maternity Cover: <?php echo $policy_details['maternity_cover'] ? 'Included' : 'Not Included'; ?></li>
                                            <li>Dependent Age Limit: <?php echo $policy_details['dependent_age_limit']; ?> years</li>
                                        </ul>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Application Form -->
                <div class="col-6">
                    <div class="card">
                        <div class="card-header">
                            <h3>Application Form</h3>
                        </div>
                        <div class="card-body">
                            <?php if ($error): ?>
                                <div class="alert alert-danger"><?php echo $error; ?></div>
                            <?php endif; ?>
                            
                            <form method="POST" action="">
                                <!-- Customer Information (Read-only) -->
                                <div class="form-group">
                                    <label class="form-label">Applicant Name</label>
                                    <input type="text" class="form-control" 
                                           value="<?php echo htmlspecialchars($customer_info['first_name'] . ' ' . $customer_info['last_name']); ?>" 
                                           readonly>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Email Address</label>
                                    <input type="email" class="form-control" 
                                           value="<?php echo htmlspecialchars($customer_info['email']); ?>" 
                                           readonly>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Phone Number</label>
                                    <input type="text" class="form-control" 
                                           value="<?php echo htmlspecialchars($customer_info['phone'] ?? 'Not provided'); ?>" 
                                           readonly>
                                </div>
                                
                                <!-- Policy Start Date -->
                                <div class="form-group">
                                    <label for="start_date" class="form-label">Policy Start Date *</label>
                                    <input type="date" id="start_date" name="start_date" class="form-control" 
                                           min="<?php echo date('Y-m-d'); ?>" 
                                           value="<?php echo $_POST['start_date'] ?? date('Y-m-d'); ?>" required>
                                </div>
                                
                                <!-- Nominee Details (for Life Insurance) -->
                                <?php if ($policy['policy_type'] === 'life'): ?>
                                    <div class="form-group">
                                        <label for="nominee_name" class="form-label">Nominee Name</label>
                                        <input type="text" id="nominee_name" name="nominee_name" class="form-control" 
                                               value="<?php echo $_POST['nominee_name'] ?? ''; ?>">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="nominee_relation" class="form-label">Nominee Relation</label>
                                        <select id="nominee_relation" name="nominee_relation" class="form-control form-select">
                                            <option value="">Select Relation</option>
                                            <option value="spouse" <?php echo (isset($_POST['nominee_relation']) && $_POST['nominee_relation'] === 'spouse') ? 'selected' : ''; ?>>Spouse</option>
                                            <option value="child" <?php echo (isset($_POST['nominee_relation']) && $_POST['nominee_relation'] === 'child') ? 'selected' : ''; ?>>Child</option>
                                            <option value="parent" <?php echo (isset($_POST['nominee_relation']) && $_POST['nominee_relation'] === 'parent') ? 'selected' : ''; ?>>Parent</option>
                                            <option value="sibling" <?php echo (isset($_POST['nominee_relation']) && $_POST['nominee_relation'] === 'sibling') ? 'selected' : ''; ?>>Sibling</option>
                                            <option value="other" <?php echo (isset($_POST['nominee_relation']) && $_POST['nominee_relation'] === 'other') ? 'selected' : ''; ?>>Other</option>
                                        </select>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Terms and Conditions -->
                                <div class="form-group">
                                    <label class="d-flex align-items-center">
                                        <input type="checkbox" required style="margin-right: 0.5rem;">
                                        I agree to the <a href="#" style="color: var(--primary-color);">Terms and Conditions</a>
                                    </label>
                                </div>
                                
                                <div class="d-flex" style="gap: 1rem;">
                                    <button type="submit" class="btn btn-primary">Apply for Policy</button>
                                    <a href="browse-policies.php" class="btn btn-outline">Cancel</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="../assets/js/main.js"></script>
</body>
</html>
