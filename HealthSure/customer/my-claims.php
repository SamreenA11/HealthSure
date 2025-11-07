<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

check_role('customer');

$customer_info = get_customer_info($_SESSION['user_id'], $conn);

// Get customer's claims
try {
    $stmt = $conn->prepare("SELECT c.*, p.policy_name, p.policy_type, ph.coverage_amount 
                           FROM claims c 
                           JOIN policy_holders ph ON c.holder_id = ph.holder_id 
                           JOIN policies p ON ph.policy_id = p.policy_id 
                           WHERE ph.customer_id = ? 
                           ORDER BY c.created_at DESC");
    $stmt->execute([$customer_info['customer_id']]);
    $my_claims = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $my_claims = [];
    set_flash_message('warning', 'Unable to load claims data. Please try again later.');
}

$message = get_flash_message();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Claims - HealthSure</title>
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
                <h1>My Claims</h1>
                <a href="file-claim.php" class="btn btn-primary">File New Claim</a>
            </div>
            
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message['type']; ?>">
                    <?php echo $message['message']; ?>
                </div>
            <?php endif; ?>
            
            <?php if (empty($my_claims)): ?>
                <div class="card">
                    <div class="card-body text-center">
                        <h4>No Claims Found</h4>
                        <p class="text-light">You haven't filed any insurance claims yet.</p>
                        <a href="file-claim.php" class="btn btn-primary">File Your First Claim</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="card">
                    <div class="card-header">
                        <h3>Claims History</h3>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Claim ID</th>
                                    <th>Policy</th>
                                    <th>Claim Amount</th>
                                    <th>Status</th>
                                    <th>Filed Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($my_claims as $claim): ?>
                                    <tr>
                                        <td><strong>#<?php echo $claim['claim_id']; ?></strong></td>
                                        <td>
                                            <?php echo htmlspecialchars($claim['policy_name']); ?>
                                            <br><span class="badge badge-primary"><?php echo ucfirst($claim['policy_type']); ?></span>
                                        </td>
                                        <td><?php echo format_currency($claim['claim_amount']); ?></td>
                                        <td>
                                            <span class="badge badge-<?php echo $claim['status'] === 'approved' ? 'success' : ($claim['status'] === 'rejected' ? 'danger' : 'warning'); ?>">
                                                <?php echo ucfirst($claim['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo format_date($claim['claim_date']); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline" onclick="viewClaim(<?php echo $claim['claim_id']; ?>)">View Details</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Claim Details Modal (simplified) -->
                <div id="claimModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
                    <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 2rem; border-radius: 0.5rem; max-width: 500px; width: 90%;">
                        <div id="claimDetails"></div>
                        <button onclick="closeModal()" class="btn btn-outline mt-3">Close</button>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        const claims = <?php echo json_encode($my_claims); ?>;
        
        function viewClaim(claimId) {
            const claim = claims.find(c => c.claim_id == claimId);
            if (!claim) return;
            
            const statusColor = claim.status === 'approved' ? 'success' : (claim.status === 'rejected' ? 'danger' : 'warning');
            
            document.getElementById('claimDetails').innerHTML = `
                <h3>Claim #${claim.claim_id}</h3>
                <div class="mb-3">
                    <strong>Policy:</strong> ${claim.policy_name}<br>
                    <strong>Status:</strong> <span class="badge badge-${statusColor}">${claim.status.charAt(0).toUpperCase() + claim.status.slice(1)}</span><br>
                    <strong>Claim Amount:</strong> ₹${parseFloat(claim.claim_amount).toLocaleString('en-IN')}<br>
                    <strong>Filed Date:</strong> ${new Date(claim.claim_date).toLocaleDateString()}<br>
                    ${claim.approved_amount > 0 ? `<strong>Approved Amount:</strong> ₹${parseFloat(claim.approved_amount).toLocaleString('en-IN')}<br>` : ''}
                </div>
                <div class="mb-3">
                    <strong>Reason:</strong><br>
                    <p style="background: #f8f9fa; padding: 1rem; border-radius: 0.25rem;">${claim.claim_reason}</p>
                </div>
                ${claim.admin_notes ? `
                <div class="mb-3">
                    <strong>Admin Notes:</strong><br>
                    <p style="background: #f8f9fa; padding: 1rem; border-radius: 0.25rem;">${claim.admin_notes}</p>
                </div>
                ` : ''}
                ${claim.documents ? `
                <div class="mb-3">
                    <strong>Documents:</strong><br>
                    <p style="background: #f8f9fa; padding: 1rem; border-radius: 0.25rem;">${claim.documents}</p>
                </div>
                ` : ''}
            `;
            
            document.getElementById('claimModal').style.display = 'block';
        }
        
        function closeModal() {
            document.getElementById('claimModal').style.display = 'none';
        }
        
        // Close modal when clicking outside
        document.getElementById('claimModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
    </script>
    <script src="../assets/js/main.js"></script>
</body>
</html>
