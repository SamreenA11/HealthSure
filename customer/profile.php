<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

check_role('customer');

$customer_info = get_customer_info($_SESSION['user_id'], $conn);
$error = '';
$success = '';

if ($_POST) {
    $first_name = sanitize_input($_POST['first_name']);
    $last_name = sanitize_input($_POST['last_name']);
    $phone = sanitize_input($_POST['phone']);
    $address = sanitize_input($_POST['address']);
    $date_of_birth = $_POST['date_of_birth'];
    $gender = $_POST['gender'];
    
    if (empty($first_name) || empty($last_name)) {
        $error = 'First name and last name are required';
    } else {
        try {
            $stmt = $conn->prepare("UPDATE customers SET first_name = ?, last_name = ?, phone = ?, address = ?, date_of_birth = ?, gender = ? WHERE customer_id = ?");
            $stmt->execute([$first_name, $last_name, $phone, $address, $date_of_birth, $gender, $customer_info['customer_id']]);
            
            $success = 'Profile updated successfully!';
            
            // Refresh customer info
            $customer_info = get_customer_info($_SESSION['user_id'], $conn);
        } catch (PDOException $e) {
            $error = 'Failed to update profile. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - HealthSure</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/animations.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <?php include 'includes/header.php'; ?>
        
        <div class="container">
            <h1>My Profile</h1>
            <p class="text-light">Manage your personal information and account settings.</p>
            
            <div class="row">
                <div class="col-8">
                    <div class="card">
                        <div class="card-header">
                            <h3>Personal Information</h3>
                        </div>
                        <div class="card-body">
                            <?php if ($error): ?>
                                <div class="alert alert-danger"><?php echo $error; ?></div>
                            <?php endif; ?>
                            
                            <?php if ($success): ?>
                                <div class="alert alert-success"><?php echo $success; ?></div>
                            <?php endif; ?>
                            
                            <form method="POST" action="">
                                <div class="row">
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label for="first_name" class="form-label">First Name *</label>
                                            <input type="text" id="first_name" name="first_name" class="form-control" 
                                                   value="<?php echo htmlspecialchars($customer_info['first_name']); ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label for="last_name" class="form-label">Last Name *</label>
                                            <input type="text" id="last_name" name="last_name" class="form-control" 
                                                   value="<?php echo htmlspecialchars($customer_info['last_name']); ?>" required>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" id="email" class="form-control" 
                                           value="<?php echo htmlspecialchars($customer_info['email']); ?>" disabled>
                                    <small class="text-light">Email cannot be changed. Contact support if needed.</small>
                                </div>
                                
                                <div class="row">
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label for="phone" class="form-label">Phone Number</label>
                                            <input type="tel" id="phone" name="phone" class="form-control" 
                                                   value="<?php echo htmlspecialchars($customer_info['phone'] ?? ''); ?>">
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label for="date_of_birth" class="form-label">Date of Birth</label>
                                            <input type="date" id="date_of_birth" name="date_of_birth" class="form-control" 
                                                   value="<?php echo $customer_info['date_of_birth']; ?>">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="gender" class="form-label">Gender</label>
                                    <select id="gender" name="gender" class="form-control form-select">
                                        <option value="">Select Gender</option>
                                        <option value="male" <?php echo $customer_info['gender'] === 'male' ? 'selected' : ''; ?>>Male</option>
                                        <option value="female" <?php echo $customer_info['gender'] === 'female' ? 'selected' : ''; ?>>Female</option>
                                        <option value="other" <?php echo $customer_info['gender'] === 'other' ? 'selected' : ''; ?>>Other</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="address" class="form-label">Address</label>
                                    <textarea id="address" name="address" class="form-control" rows="3"><?php echo htmlspecialchars($customer_info['address'] ?? ''); ?></textarea>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">Update Profile</button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="col-4">
                    <div class="card">
                        <div class="card-header">
                            <h3>Account Information</h3>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <strong>Customer ID:</strong><br>
                                #<?php echo $customer_info['customer_id']; ?>
                            </div>
                            <div class="mb-3">
                                <strong>Account Status:</strong><br>
                                <span class="badge badge-success">Active</span>
                            </div>
                            <div class="mb-3">
                                <strong>Member Since:</strong><br>
                                <?php echo format_date($customer_info['created_at']); ?>
                            </div>
                            <?php if ($customer_info['agent_id']): ?>
                                <div class="mb-3">
                                    <strong>Assigned Agent:</strong><br>
                                    Agent #<?php echo $customer_info['agent_id']; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="card mt-4">
                        <div class="card-header">
                            <h3>Security</h3>
                        </div>
                        <div class="card-body">
                            <p style="font-size: 0.875rem; color: var(--text-light);">
                                To change your password or update security settings, please contact our support team.
                            </p>
                            <button class="btn btn-secondary btn-sm" onclick="alert('Contact Support: +91-1800-123-4567')">
                                Contact Support
                            </button>
                        </div>
                    </div>
                    
                    <div class="card mt-4">
                        <div class="card-header">
                            <h3>Quick Actions</h3>
                        </div>
                        <div class="card-body">
                            <div class="d-flex flex-column" style="gap: 0.5rem;">
                                <a href="my-policies.php" class="btn btn-outline btn-sm">View My Policies</a>
                                <a href="my-claims.php" class="btn btn-outline btn-sm">View My Claims</a>
                                <a href="payment-history.php" class="btn btn-outline btn-sm">Payment History</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="../assets/js/main.js"></script>
</body>
</html>
