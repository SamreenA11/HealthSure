<?php
$agent_info = get_agent_info($_SESSION['user_id'], $conn);
?>
<div class="navbar">
    <div class="container d-flex justify-content-between align-items-center">
        <div>
            <h4 style="margin: 0; color: var(--text-dark);">
                <?php 
                $page_titles = [
                    'dashboard.php' => 'Dashboard',
                    'customers.php' => 'My Customers',
                    'reports.php' => 'Performance Reports',
                    'profile.php' => 'My Profile',
                    'support.php' => 'Support'
                ];
                echo $page_titles[basename($_SERVER['PHP_SELF'])] ?? 'Agent Portal';
                ?>
            </h4>
        </div>
        <div class="d-flex align-items-center" style="gap: 1rem;">
            <span style="color: var(--text-light);">Welcome, <?php echo htmlspecialchars($agent_info['first_name']); ?></span>
            <div style="width: 40px; height: 40px; background: var(--warning-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600;">
                <?php echo strtoupper(substr($agent_info['first_name'], 0, 1)); ?>
            </div>
        </div>
    </div>
</div>
