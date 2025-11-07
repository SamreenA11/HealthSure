<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

check_role('customer');

$customer_info = get_customer_info($_SESSION['user_id'], $conn);

// Get customer's policies with detailed information
$stmt = $conn->prepare("SELECT ph.*, p.policy_name, p.policy_type, p.coverage_amount, p.duration_years, p.description,
                       hp.hospital_coverage, hp.pre_existing_conditions, hp.network_hospitals, hp.cashless_limit,
                       lp.nominee_name, lp.nominee_relation, lp.term_years, lp.maturity_benefit, lp.death_benefit,
                       fp.no_of_dependents, fp.maternity_cover, fp.dependent_age_limit, fp.family_floater_sum
                       FROM policy_holders ph 
                       JOIN policies p ON ph.policy_id = p.policy_id 
                       LEFT JOIN health_policies hp ON p.policy_id = hp.policy_id
                       LEFT JOIN life_policies lp ON p.policy_id = lp.policy_id
                       LEFT JOIN family_policies fp ON p.policy_id = fp.policy_id
                       WHERE ph.customer_id = ? 
                       ORDER BY ph.created_at DESC");
$stmt->execute([$customer_info['customer_id']]);
$my_policies = $stmt->fetchAll(PDO::FETCH_ASSOC);

$message = get_flash_message();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Policies - HealthSure</title>
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
                <h1>My Insurance Policies</h1>
                <a href="browse-policies.php" class="btn btn-primary">Browse More Policies</a>
            </div>
            
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message['type']; ?>">
                    <?php echo $message['message']; ?>
                </div>
            <?php endif; ?>
            
            <?php if (empty($my_policies)): ?>
                <div class="card">
                    <div class="card-body text-center">
                        <h4>No Policies Found</h4>
                        <p class="text-light">You don't have any insurance policies yet. Browse our available policies to get started.</p>
                        <a href="browse-policies.php" class="btn btn-primary">Browse Policies</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($my_policies as $policy): ?>
                        <div class="col-6">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h4><?php echo htmlspecialchars($policy['policy_name']); ?></h4>
                                        <span class="badge badge-<?php echo $policy['status'] === 'active' ? 'success' : 'warning'; ?>">
                                            <?php echo ucfirst($policy['status']); ?>
                                        </span>
                                    </div>
                                    <span class="badge badge-primary"><?php echo ucfirst($policy['policy_type']); ?> Insurance</span>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Policy ID:</span>
                                            <strong>#<?php echo $policy['holder_id']; ?></strong>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Coverage Amount:</span>
                                            <strong><?php echo format_currency($policy['coverage_amount']); ?></strong>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Annual Premium:</span>
                                            <strong><?php echo format_currency($policy['premium_amount']); ?></strong>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Start Date:</span>
                                            <strong><?php echo format_date($policy['start_date']); ?></strong>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>End Date:</span>
                                            <strong><?php echo format_date($policy['end_date']); ?></strong>
                                        </div>
                                    </div>
                                    
                                    <?php if ($policy['policy_type'] === 'health' && $policy['hospital_coverage']): ?>
                                        <div class="mb-3" style="border-top: 1px solid var(--border-color); padding-top: 1rem;">
                                            <h6>Health Benefits:</h6>
                                            <ul style="font-size: 0.875rem; color: var(--text-light);">
                                                <li>Cashless Limit: <?php echo format_currency($policy['cashless_limit']); ?></li>
                                                <li>Pre-existing: <?php echo $policy['pre_existing_conditions'] ? 'Covered' : 'Not Covered'; ?></li>
                                                <li>Network: <?php echo htmlspecialchars($policy['network_hospitals']); ?></li>
                                            </ul>
                                        </div>
                                    <?php elseif ($policy['policy_type'] === 'life' && $policy['nominee_name']): ?>
                                        <div class="mb-3" style="border-top: 1px solid var(--border-color); padding-top: 1rem;">
                                            <h6>Life Insurance Details:</h6>
                                            <ul style="font-size: 0.875rem; color: var(--text-light);">
                                                <li>Nominee: <?php echo htmlspecialchars($policy['nominee_name']); ?></li>
                                                <li>Relation: <?php echo ucfirst($policy['nominee_relation']); ?></li>
                                                <li>Death Benefit: <?php echo format_currency($policy['death_benefit']); ?></li>
                                            </ul>
                                        </div>
                                    <?php elseif ($policy['policy_type'] === 'family' && $policy['no_of_dependents']): ?>
                                        <div class="mb-3" style="border-top: 1px solid var(--border-color); padding-top: 1rem;">
                                            <h6>Family Coverage:</h6>
                                            <ul style="font-size: 0.875rem; color: var(--text-light);">
                                                <li>Max Dependents: <?php echo $policy['no_of_dependents']; ?></li>
                                                <li>Maternity: <?php echo $policy['maternity_cover'] ? 'Covered' : 'Not Covered'; ?></li>
                                                <li>Age Limit: <?php echo $policy['dependent_age_limit']; ?> years</li>
                                            </ul>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="card-footer">
                                    <div class="d-flex" style="gap: 0.5rem;">
                                        <?php if ($policy['status'] === 'active'): ?>
                                            <a href="file-claim.php?policy_id=<?php echo $policy['holder_id']; ?>" class="btn btn-success btn-sm">File Claim</a>
                                            <a href="make-payment.php?policy_id=<?php echo $policy['holder_id']; ?>" class="btn btn-warning btn-sm">Pay Premium</a>
                                        <?php endif; ?>
                                        <button class="btn btn-outline btn-sm" onclick="downloadPolicy(<?php echo $policy['holder_id']; ?>)">Download</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        function downloadPolicy(policyId) {
            alert('Policy download feature would generate a PDF certificate for Policy #' + policyId);
        }
    </script>
    <script src="../assets/js/main.js"></script>
</body>
</html>
