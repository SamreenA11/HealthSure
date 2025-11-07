<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

check_role('customer');

$customer_info = get_customer_info($_SESSION['user_id'], $conn);

// Get all active policies
$stmt = $conn->query("SELECT * FROM policies WHERE status = 'active' ORDER BY policy_type, policy_name");
$policies = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get policy details based on type
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

$message = get_flash_message();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Policies - HealthSure</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/animations.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <?php include 'includes/header.php'; ?>
        
        <div class="container">
            <h1>Browse Insurance Policies</h1>
            <p class="text-light">Choose the perfect insurance policy that suits your needs.</p>
            
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message['type']; ?>">
                    <?php echo $message['message']; ?>
                </div>
            <?php endif; ?>
            
            <!-- Filter Tabs -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex" style="gap: 1rem;">
                        <button class="btn btn-primary" onclick="filterPolicies('all')">All Policies</button>
                        <button class="btn btn-outline" onclick="filterPolicies('health')">Health Insurance</button>
                        <button class="btn btn-outline" onclick="filterPolicies('life')">Life Insurance</button>
                        <button class="btn btn-outline" onclick="filterPolicies('family')">Family Insurance</button>
                    </div>
                </div>
            </div>
            
            <!-- Policies Grid -->
            <div class="row">
                <?php foreach ($policies as $policy): ?>
                    <?php $details = getPolicyDetails($policy['policy_id'], $policy['policy_type'], $conn); ?>
                    <div class="col-4 policy-card" data-type="<?php echo $policy['policy_type']; ?>">
                        <div class="card mb-4">
                            <div class="card-header">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h4><?php echo htmlspecialchars($policy['policy_name']); ?></h4>
                                    <span class="badge badge-primary"><?php echo ucfirst($policy['policy_type']); ?></span>
                                </div>
                            </div>
                            <div class="card-body">
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
                                
                                <?php if ($details): ?>
                                    <div class="mb-3" style="border-top: 1px solid var(--border-color); padding-top: 1rem;">
                                        <h6>Key Features:</h6>
                                        <?php if ($policy['policy_type'] === 'health'): ?>
                                            <ul style="font-size: 0.875rem; color: var(--text-light);">
                                                <li>Cashless Limit: <?php echo format_currency($details['cashless_limit']); ?></li>
                                                <li>Pre-existing Conditions: <?php echo $details['pre_existing_conditions'] ? 'Covered' : 'Not Covered'; ?></li>
                                                <li>Network Hospitals: <?php echo htmlspecialchars($details['network_hospitals']); ?></li>
                                            </ul>
                                        <?php elseif ($policy['policy_type'] === 'life'): ?>
                                            <ul style="font-size: 0.875rem; color: var(--text-light);">
                                                <li>Term: <?php echo $details['term_years']; ?> years</li>
                                                <li>Death Benefit: <?php echo format_currency($details['death_benefit']); ?></li>
                                                <li>Maturity Benefit: <?php echo format_currency($details['maturity_benefit']); ?></li>
                                            </ul>
                                        <?php elseif ($policy['policy_type'] === 'family'): ?>
                                            <ul style="font-size: 0.875rem; color: var(--text-light);">
                                                <li>Max Dependents: <?php echo $details['no_of_dependents']; ?></li>
                                                <li>Maternity Cover: <?php echo $details['maternity_cover'] ? 'Included' : 'Not Included'; ?></li>
                                                <li>Dependent Age Limit: <?php echo $details['dependent_age_limit']; ?> years</li>
                                            </ul>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="card-footer">
                                <a href="apply-policy.php?policy_id=<?php echo $policy['policy_id']; ?>" 
                                   class="btn btn-primary" style="width: 100%;">
                                    Apply Now
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <?php if (empty($policies)): ?>
                <div class="card">
                    <div class="card-body text-center">
                        <h4>No Policies Available</h4>
                        <p class="text-light">There are currently no active insurance policies available. Please check back later.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        function filterPolicies(type) {
            const cards = document.querySelectorAll('.policy-card');
            const buttons = document.querySelectorAll('.card-body .btn');
            
            // Reset button styles
            buttons.forEach(btn => {
                btn.className = 'btn btn-outline';
            });
            
            // Highlight active button
            event.target.className = 'btn btn-primary';
            
            // Show/hide cards
            cards.forEach(card => {
                if (type === 'all' || card.dataset.type === type) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }
    </script>
    <script src="../assets/js/main.js"></script>
</body>
</html>
