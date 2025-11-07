<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

check_role('customer');

$customer_info = get_customer_info($_SESSION['user_id'], $conn);
$success = '';

if ($_POST) {
    $subject = sanitize_input($_POST['subject']);
    $message = sanitize_input($_POST['message']);
    
    if (!empty($subject) && !empty($message)) {
        try {
            $stmt = $conn->prepare("INSERT INTO support_queries (customer_id, subject, message, status) VALUES (?, ?, ?, 'open')");
            $stmt->execute([$customer_info['customer_id'], $subject, $message]);
            
            $success = 'Your support request has been submitted successfully! We will get back to you within 24 hours.';
        } catch (PDOException $e) {
            $error = 'Failed to submit support request. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support - HealthSure</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/animations.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <?php include 'includes/header.php'; ?>
        
        <div class="container">
            <h1>Customer Support</h1>
            <p class="text-light">Get help with your insurance policies, claims, and account.</p>
            
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
                            
                            <form method="POST" action="">
                                <div class="form-group">
                                    <label for="subject" class="form-label">Subject *</label>
                                    <select id="subject" name="subject" class="form-control form-select" required>
                                        <option value="">Select a topic</option>
                                        <option value="Policy Question">Policy Question</option>
                                        <option value="Claim Issue">Claim Issue</option>
                                        <option value="Payment Problem">Payment Problem</option>
                                        <option value="Account Access">Account Access</option>
                                        <option value="Technical Issue">Technical Issue</option>
                                        <option value="General Inquiry">General Inquiry</option>
                                        <option value="Complaint">Complaint</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="message" class="form-label">Message *</label>
                                    <textarea id="message" name="message" class="form-control" rows="6" 
                                              placeholder="Please describe your issue or question in detail..." required></textarea>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">Submit Request</button>
                            </form>
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
                                <strong>📞 Phone Support</strong><br>
                                <span style="font-size: 1.1rem;">+91-1800-123-4567</span><br>
                                <small class="text-light">Mon-Fri: 9 AM - 6 PM</small>
                            </div>
                            
                            <div class="mb-3">
                                <strong>📧 Email Support</strong><br>
                                support@healthsure.com<br>
                                <small class="text-light">Response within 24 hours</small>
                            </div>
                            
                            <div class="mb-3">
                                <strong>🏢 Office Address</strong><br>
                                HealthSure Insurance<br>
                                123 Insurance Street<br>
                                Business District<br>
                                City - 123456
                            </div>
                        </div>
                    </div>
                    
                    <div class="card mt-4">
                        <div class="card-header">
                            <h3>Frequently Asked Questions</h3>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <strong>How do I file a claim?</strong><br>
                                <small>Go to "File New Claim" from your dashboard and follow the steps.</small>
                            </div>
                            
                            <div class="mb-3">
                                <strong>When are premiums due?</strong><br>
                                <small>Annual premiums are due on your policy anniversary date.</small>
                            </div>
                            
                            <div class="mb-3">
                                <strong>How long does claim processing take?</strong><br>
                                <small>Most claims are processed within 10-15 business days.</small>
                            </div>
                            
                            <div class="mb-3">
                                <strong>Can I change my policy details?</strong><br>
                                <small>Contact support to modify policy information or beneficiaries.</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card mt-4">
                        <div class="card-header">
                            <h3>Emergency Claims</h3>
                        </div>
                        <div class="card-body">
                            <p style="font-size: 0.875rem;">For medical emergencies requiring immediate attention:</p>
                            <div class="alert alert-warning">
                                <strong>Emergency Hotline</strong><br>
                                📞 +91-1800-EMERGENCY<br>
                                Available 24/7
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
