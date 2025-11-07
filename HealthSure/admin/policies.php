<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

check_role('admin');

$message = get_flash_message();
$action = $_GET['action'] ?? 'list';
$policy_id = $_GET['policy_id'] ?? null;

// Handle form submissions
if ($_POST) {
    if (isset($_POST['add_policy'])) {
        $policy_name = sanitize_input($_POST['policy_name']);
        $policy_type = $_POST['policy_type'];
        $description = sanitize_input($_POST['description']);
        $base_premium = (float)$_POST['base_premium'];
        $coverage_amount = (float)$_POST['coverage_amount'];
        $duration_years = (int)$_POST['duration_years'];
        
        try {
            $conn->beginTransaction();
            
            // Insert main policy
            $stmt = $conn->prepare("INSERT INTO policies (policy_name, policy_type, description, base_premium, coverage_amount, duration_years) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$policy_name, $policy_type, $description, $base_premium, $coverage_amount, $duration_years]);
            $new_policy_id = $conn->lastInsertId();
            
            // Insert type-specific details
            if ($policy_type === 'health') {
                $stmt = $conn->prepare("INSERT INTO health_policies (policy_id, hospital_coverage, pre_existing_conditions, network_hospitals, cashless_limit) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([
                    $new_policy_id,
                    $_POST['hospital_coverage'],
                    isset($_POST['pre_existing_conditions']) ? 1 : 0,
                    $_POST['network_hospitals'],
                    (float)$_POST['cashless_limit']
                ]);
            } elseif ($policy_type === 'life') {
                $stmt = $conn->prepare("INSERT INTO life_policies (policy_id, term_years, maturity_benefit, death_benefit) VALUES (?, ?, ?, ?)");
                $stmt->execute([
                    $new_policy_id,
                    (int)$_POST['term_years'],
                    (float)$_POST['maturity_benefit'],
                    (float)$_POST['death_benefit']
                ]);
            } elseif ($policy_type === 'family') {
                $stmt = $conn->prepare("INSERT INTO family_policies (policy_id, no_of_dependents, maternity_cover, dependent_age_limit, family_floater_sum) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([
                    $new_policy_id,
                    (int)$_POST['no_of_dependents'],
                    isset($_POST['maternity_cover']) ? 1 : 0,
                    (int)$_POST['dependent_age_limit'],
                    (float)$_POST['family_floater_sum']
                ]);
            }
            
            $conn->commit();
            set_flash_message('success', 'Policy created successfully!');
            redirect('policies.php');
        } catch (PDOException $e) {
            $conn->rollBack();
            set_flash_message('danger', 'Error creating policy: ' . $e->getMessage());
        }
    }
    
    if (isset($_POST['update_status'])) {
        $policy_id = (int)$_POST['policy_id'];
        $status = $_POST['status'];
        
        try {
            $stmt = $conn->prepare("UPDATE policies SET status = ? WHERE policy_id = ?");
            $stmt->execute([$status, $policy_id]);
            set_flash_message('success', 'Policy status updated successfully!');
        } catch (PDOException $e) {
            set_flash_message('danger', 'Error updating policy status.');
        }
    }
}

// Get all policies with counts
$stmt = $conn->query("SELECT p.*, 
                     (SELECT COUNT(*) FROM policy_holders ph WHERE ph.policy_id = p.policy_id AND ph.status = 'active') as active_holders,
                     (SELECT COUNT(*) FROM claims c JOIN policy_holders ph ON c.holder_id = ph.holder_id WHERE ph.policy_id = p.policy_id) as total_claims
                     FROM policies p ORDER BY p.created_at DESC");
$policies = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get policy for editing
$edit_policy = null;
if ($action === 'edit' && $policy_id) {
    $stmt = $conn->prepare("SELECT * FROM policies WHERE policy_id = ?");
    $stmt->execute([$policy_id]);
    $edit_policy = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Policy Management - HealthSure</title>
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
                <h1>Policy Management</h1>
                <a href="?action=add" class="btn btn-primary">Add New Policy</a>
            </div>
            
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message['type']; ?>">
                    <?php echo $message['message']; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($action === 'add' || $action === 'edit'): ?>
                <!-- Add/Edit Policy Form -->
                <div class="card">
                    <div class="card-header">
                        <h3><?php echo $action === 'edit' ? 'Edit Policy' : 'Add New Policy'; ?></h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label for="policy_name" class="form-label">Policy Name *</label>
                                        <input type="text" id="policy_name" name="policy_name" class="form-control" 
                                               value="<?php echo $edit_policy['policy_name'] ?? ''; ?>" required>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label for="policy_type" class="form-label">Policy Type *</label>
                                        <select id="policy_type" name="policy_type" class="form-control form-select" required onchange="showTypeFields(this.value)">
                                            <option value="">Select Type</option>
                                            <option value="health" <?php echo ($edit_policy['policy_type'] ?? '') === 'health' ? 'selected' : ''; ?>>Health Insurance</option>
                                            <option value="life" <?php echo ($edit_policy['policy_type'] ?? '') === 'life' ? 'selected' : ''; ?>>Life Insurance</option>
                                            <option value="family" <?php echo ($edit_policy['policy_type'] ?? '') === 'family' ? 'selected' : ''; ?>>Family Insurance</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="description" class="form-label">Description</label>
                                <textarea id="description" name="description" class="form-control" rows="3"><?php echo $edit_policy['description'] ?? ''; ?></textarea>
                            </div>
                            
                            <div class="row">
                                <div class="col-4">
                                    <div class="form-group">
                                        <label for="base_premium" class="form-label">Base Premium (₹) *</label>
                                        <input type="number" id="base_premium" name="base_premium" class="form-control" 
                                               value="<?php echo $edit_policy['base_premium'] ?? ''; ?>" step="0.01" required>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="form-group">
                                        <label for="coverage_amount" class="form-label">Coverage Amount (₹) *</label>
                                        <input type="number" id="coverage_amount" name="coverage_amount" class="form-control" 
                                               value="<?php echo $edit_policy['coverage_amount'] ?? ''; ?>" step="0.01" required>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="form-group">
                                        <label for="duration_years" class="form-label">Duration (Years) *</label>
                                        <input type="number" id="duration_years" name="duration_years" class="form-control" 
                                               value="<?php echo $edit_policy['duration_years'] ?? ''; ?>" required>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Health Insurance Fields -->
                            <div id="health_fields" style="display: none;">
                                <h5>Health Insurance Details</h5>
                                <div class="row">
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label for="hospital_coverage" class="form-label">Hospital Coverage</label>
                                            <textarea id="hospital_coverage" name="hospital_coverage" class="form-control" rows="2"></textarea>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label for="network_hospitals" class="form-label">Network Hospitals</label>
                                            <textarea id="network_hospitals" name="network_hospitals" class="form-control" rows="2"></textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label for="cashless_limit" class="form-label">Cashless Limit (₹)</label>
                                            <input type="number" id="cashless_limit" name="cashless_limit" class="form-control" step="0.01">
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label class="d-flex align-items-center">
                                                <input type="checkbox" name="pre_existing_conditions" style="margin-right: 0.5rem;">
                                                Pre-existing Conditions Covered
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Life Insurance Fields -->
                            <div id="life_fields" style="display: none;">
                                <h5>Life Insurance Details</h5>
                                <div class="row">
                                    <div class="col-4">
                                        <div class="form-group">
                                            <label for="term_years" class="form-label">Term (Years)</label>
                                            <input type="number" id="term_years" name="term_years" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="form-group">
                                            <label for="death_benefit" class="form-label">Death Benefit (₹)</label>
                                            <input type="number" id="death_benefit" name="death_benefit" class="form-control" step="0.01">
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="form-group">
                                            <label for="maturity_benefit" class="form-label">Maturity Benefit (₹)</label>
                                            <input type="number" id="maturity_benefit" name="maturity_benefit" class="form-control" step="0.01">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Family Insurance Fields -->
                            <div id="family_fields" style="display: none;">
                                <h5>Family Insurance Details</h5>
                                <div class="row">
                                    <div class="col-3">
                                        <div class="form-group">
                                            <label for="no_of_dependents" class="form-label">Max Dependents</label>
                                            <input type="number" id="no_of_dependents" name="no_of_dependents" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-3">
                                        <div class="form-group">
                                            <label for="dependent_age_limit" class="form-label">Dependent Age Limit</label>
                                            <input type="number" id="dependent_age_limit" name="dependent_age_limit" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-3">
                                        <div class="form-group">
                                            <label for="family_floater_sum" class="form-label">Family Floater Sum (₹)</label>
                                            <input type="number" id="family_floater_sum" name="family_floater_sum" class="form-control" step="0.01">
                                        </div>
                                    </div>
                                    <div class="col-3">
                                        <div class="form-group">
                                            <label class="d-flex align-items-center">
                                                <input type="checkbox" name="maternity_cover" style="margin-right: 0.5rem;">
                                                Maternity Cover
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-flex" style="gap: 1rem;">
                                <button type="submit" name="add_policy" class="btn btn-primary">
                                    <?php echo $action === 'edit' ? 'Update Policy' : 'Create Policy'; ?>
                                </button>
                                <a href="policies.php" class="btn btn-outline">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            <?php else: ?>
                <!-- Policies List -->
                <div class="card">
                    <div class="card-header">
                        <h3>All Policies</h3>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Policy Name</th>
                                    <th>Type</th>
                                    <th>Premium</th>
                                    <th>Coverage</th>
                                    <th>Active Holders</th>
                                    <th>Claims</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($policies as $policy): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($policy['policy_name']); ?></strong>
                                            <br><small class="text-light"><?php echo htmlspecialchars(substr($policy['description'], 0, 50)); ?>...</small>
                                        </td>
                                        <td><span class="badge badge-primary"><?php echo ucfirst($policy['policy_type']); ?></span></td>
                                        <td><?php echo format_currency($policy['base_premium']); ?></td>
                                        <td><?php echo format_currency($policy['coverage_amount']); ?></td>
                                        <td><?php echo $policy['active_holders']; ?></td>
                                        <td><?php echo $policy['total_claims']; ?></td>
                                        <td>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="policy_id" value="<?php echo $policy['policy_id']; ?>">
                                                <select name="status" onchange="this.form.submit()" class="form-control form-select" style="width: auto; display: inline-block;">
                                                    <option value="active" <?php echo $policy['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                                    <option value="inactive" <?php echo $policy['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                                </select>
                                                <input type="hidden" name="update_status" value="1">
                                            </form>
                                        </td>
                                        <td>
                                            <a href="?action=edit&policy_id=<?php echo $policy['policy_id']; ?>" class="btn btn-sm btn-outline">Edit</a>
                                            <a href="?action=view&policy_id=<?php echo $policy['policy_id']; ?>" class="btn btn-sm btn-primary">View</a>
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
    
    <script>
        function showTypeFields(type) {
            // Hide all type-specific fields
            document.getElementById('health_fields').style.display = 'none';
            document.getElementById('life_fields').style.display = 'none';
            document.getElementById('family_fields').style.display = 'none';
            
            // Show relevant fields
            if (type) {
                document.getElementById(type + '_fields').style.display = 'block';
            }
        }
        
        // Show fields on page load if editing
        <?php if ($edit_policy): ?>
            showTypeFields('<?php echo $edit_policy['policy_type']; ?>');
        <?php endif; ?>
    </script>
    <script src="../assets/js/main.js"></script>
</body>
</html>
