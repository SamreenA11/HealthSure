<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

check_role('agent');

$agent_info = get_agent_info($_SESSION['user_id'], $conn);
$success = '';
$error = '';

if ($_POST) {
    $subject = sanitize_input($_POST['subject']);
    $message = sanitize_input($_POST['message']);
    $priority = $_POST['priority'];
    
    if (!empty($subject) && !empty($message)) {
        try {
            // In a real system, you'd have an agent_support_tickets table
            // For now, we'll just show a success message
            $success = 'Your support request has been submitted successfully! Our admin team will get back to you within 24 hours.';
        } catch (PDOException $e) {
            $error = 'Failed to submit support request. Please try again.';
        }
    } else {
        $error = 'Please fill in all required fields.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agent Support - HealthSure</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/animations.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <?php include 'includes/header.php'; ?>
        
        <div class="container">
            <h1>Agent Support</h1>
            <p class="text-light">Get help with your agent account, customer management, and system issues.</p>
            
            <div class="row">
                <div class="col-8">
                    <div class="card">
                        <div class="card-header">
                            <h3>Submit Support Request</h3>
                        </div>
                        <div class="card-body">
                            <?php if ($success): ?>
                                <div class="alert alert-success"><?php echo $success; ?></div>
                            <?php endif; ?>
                            
                            <?php if ($error): ?>
                                <div class="alert alert-danger"><?php echo $error; ?></div>
                            <?php endif; ?>
                            
                            <form method="POST" action="">
                                <div class="row">
                                    <div class="col-8">
                                        <div class="form-group">
                                            <label for="subject" class="form-label">Subject *</label>
                                            <select id="subject" name="subject" class="form-control form-select" required>
                                                <option value="">Select a topic</option>
                                                <option value="Customer Assignment">Customer Assignment Issue</option>
                                                <option value="System Access">System Access Problem</option>
                                                <option value="Commission Query">Commission/Payment Query</option>
                                                <option value="Policy Information">Policy Information Request</option>
                                                <option value="Training Request">Training/Resources Request</option>
                                                <option value="Technical Issue">Technical Issue</option>
                                                <option value="Account Problem">Account Problem</option>
                                                <option value="General Inquiry">General Inquiry</option>
                                                <option value="Other">Other</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="form-group">
                                            <label for="priority" class="form-label">Priority *</label>
                                            <select id="priority" name="priority" class="form-control form-select" required>
                                                <option value="low">Low</option>
                                                <option value="medium" selected>Medium</option>
                                                <option value="high">High</option>
                                                <option value="urgent">Urgent</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="message" class="form-label">Message *</label>
                                    <textarea id="message" name="message" class="form-control" rows="6" 
                                              placeholder="Please describe your issue or question in detail..." required></textarea>
                                </div>
                                
                                <div class="form-group">
                                    <label class="d-flex align-items-center">
                                        <input type="checkbox" required style="margin-right: 0.5rem;">
                                        I have checked the FAQ and agent resources before submitting this request.
                                    </label>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">Submit Support Request</button>
                            </form>
                        </div>
                    </div>
                    
                    <!-- FAQ Section -->
                    <div class="card">
                        <div class="card-header">
                            <h3>Frequently Asked Questions</h3>
                        </div>
                        <div class="card-body">
                            <div class="mb-4">
                                <h6><strong>Q: How do I access my customer list?</strong></h6>
                                <p style="font-size: 0.875rem; color: var(--text-light);">
                                    Go to "My Customers" from the sidebar. You can only see customers assigned to you by the admin.
                                </p>
                            </div>
                            
                            <div class="mb-4">
                                <h6><strong>Q: How are commissions calculated?</strong></h6>
                                <p style="font-size: 0.875rem; color: var(--text-light);">
                                    Commissions are based on the premium amount and policy type. Contact admin for specific commission structure details.
                                </p>
                            </div>
                            
                            <div class="mb-4">
                                <h6><strong>Q: Can I help customers file claims?</strong></h6>
                                <p style="font-size: 0.875rem; color: var(--text-light);">
                                    Yes, you can guide customers through the claim filing process, but claims are processed by the admin team.
                                </p>
                            </div>
                            
                            <div class="mb-4">
                                <h6><strong>Q: How do I update my profile information?</strong></h6>
                                <p style="font-size: 0.875rem; color: var(--text-light);">
                                    Go to "My Profile" to update your contact information, branch, and license number. Email changes require admin approval.
                                </p>
                            </div>
                            
                            <div class="mb-4">
                                <h6><strong>Q: Where can I see my performance reports?</strong></h6>
                                <p style="font-size: 0.875rem; color: var(--text-light);">
                                    Visit "My Performance Reports" to view detailed statistics about your customers, policies sold, and commissions earned.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-4">
                    <div class="card">
                        <div class="card-header">
                            <h3>Contact Information</h3>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <strong>📧 Admin Email</strong><br>
                                <a href="mailto:admin@healthsure.com">admin@healthsure.com</a><br>
                                <small class="text-light">For account and system issues</small>
                            </div>
                            
                            <div class="mb-3">
                                <strong>📞 Support Hotline</strong><br>
                                <span style="font-size: 1.1rem;">+91-1800-123-4567</span><br>
                                <small class="text-light">Mon-Fri: 9 AM - 6 PM</small>
                            </div>
                            
                            <div class="mb-3">
                                <strong>🚨 Emergency Support</strong><br>
                                <span style="font-size: 1.1rem;">+91-1800-EMERGENCY</span><br>
                                <small class="text-light">24/7 for urgent issues</small>
                            </div>
                            
                            <div class="mb-3">
                                <strong>🏢 Head Office</strong><br>
                                HealthSure Insurance<br>
                                123 Insurance Street<br>
                                Business District<br>
                                City - 123456
                            </div>
                        </div>
                    </div>
                    
                    <div class="card mt-4">
                        <div class="card-header">
                            <h3>Agent Resources</h3>
                        </div>
                        <div class="card-body">
                            <h6>Quick Links:</h6>
                            <div class="d-flex flex-column" style="gap: 0.5rem;">
                                <button class="btn btn-outline btn-sm" onclick="alert('Feature coming soon!')">
                                    📚 Agent Handbook
                                </button>
                                <button class="btn btn-outline btn-sm" onclick="alert('Feature coming soon!')">
                                    🎯 Sales Training
                                </button>
                                <button class="btn btn-outline btn-sm" onclick="alert('Feature coming soon!')">
                                    💰 Commission Calculator
                                </button>
                                <button class="btn btn-outline btn-sm" onclick="alert('Feature coming soon!')">
                                    📋 Policy Comparison Tool
                                </button>
                            </div>
                            
                            <h6 class="mt-3">System Status:</h6>
                            <div class="d-flex align-items-center" style="gap: 0.5rem;">
                                <span class="badge badge-success">●</span>
                                <span style="font-size: 0.875rem;">All systems operational</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card mt-4">
                        <div class="card-header">
                            <h3>My Information</h3>
                        </div>
                        <div class="card-body">
                            <div class="mb-2">
                                <strong>Agent ID:</strong> #<?php echo $agent_info['agent_id']; ?>
                            </div>
                            <div class="mb-2">
                                <strong>Name:</strong> <?php echo htmlspecialchars($agent_info['first_name'] . ' ' . $agent_info['last_name']); ?>
                            </div>
                            <div class="mb-2">
                                <strong>Email:</strong> <?php echo htmlspecialchars($agent_info['email']); ?>
                            </div>
                            <?php if ($agent_info['branch']): ?>
                                <div class="mb-2">
                                    <strong>Branch:</strong> <?php echo htmlspecialchars($agent_info['branch']); ?>
                                </div>
                            <?php endif; ?>
                            <?php if ($agent_info['license_number']): ?>
                                <div class="mb-2">
                                    <strong>License:</strong> <?php echo htmlspecialchars($agent_info['license_number']); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/main.js"></script>
</body>
</html>
