<?php
$customer_info = get_customer_info($_SESSION['user_id'], $conn);
?>
<div class="navbar">
    <div class="container d-flex justify-content-between align-items-center">
        <div>
            <h4 style="margin: 0; color: var(--text-dark);">
                <?php 
                $page_titles = [
                    'dashboard.php' => 'Dashboard',
                    'browse-policies.php' => 'Browse Policies',
                    'my-policies.php' => 'My Policies',
                    'my-claims.php' => 'My Claims',
                    'file-claim.php' => 'File New Claim',
                    'make-payment.php' => 'Make Payment',
                    'payment-history.php' => 'Payment History',
                    'profile.php' => 'My Profile',
                    'support.php' => 'Support'
                ];
                echo $page_titles[basename($_SERVER['PHP_SELF'])] ?? 'Customer Portal';
                ?>
            </h4>
        </div>
        <div class="d-flex align-items-center" style="gap: 1rem;">
            <span style="color: var(--text-light);">Welcome, <?php echo htmlspecialchars($customer_info['first_name']); ?></span>
            <div style="width: 40px; height: 40px; background: var(--success-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600;">
                <?php echo strtoupper(substr($customer_info['first_name'], 0, 1)); ?>
            </div>
        </div>
    </div>
</div>
