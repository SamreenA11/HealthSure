<div class="navbar">
    <div class="container d-flex justify-content-between align-items-center">
        <div>
            <h4 style="margin: 0; color: var(--text-dark);">
                <?php 
                $page_titles = [
                    'dashboard.php' => 'Dashboard',
                    'policies.php' => 'Policy Management',
                    'customers.php' => 'Customer Management',
                    'agents.php' => 'Agent Management',
                    'claims.php' => 'Claims Management',
                    'payments.php' => 'Payment Management',
                    'reports.php' => 'Reports & Analytics',
                    'settings.php' => 'System Settings'
                ];
                echo $page_titles[basename($_SERVER['PHP_SELF'])] ?? 'Admin Panel';
                ?>
            </h4>
        </div>
        <div class="d-flex align-items-center" style="gap: 1rem;">
            <span style="color: var(--text-light);">Welcome, Admin</span>
            <div style="width: 40px; height: 40px; background: var(--primary-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600;">
                A
            </div>
        </div>
    </div>
</div>
