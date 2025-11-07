<div class="sidebar">
    <div class="sidebar-header">
        <h3>HealthSure</h3>
        <p style="font-size: 0.875rem; color: var(--text-light);">Agent Portal</p>
    </div>
    <nav class="sidebar-menu">
        <a href="dashboard.php" class="sidebar-item <?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : ''; ?>">
            🏠 Dashboard
        </a>
        <a href="customers.php" class="sidebar-item <?php echo basename($_SERVER['PHP_SELF']) === 'customers.php' ? 'active' : ''; ?>">
            👥 My Customers
        </a>
        <a href="../customer/browse-policies.php" class="sidebar-item">
            📋 Browse Policies
        </a>
        <a href="reports.php" class="sidebar-item <?php echo basename($_SERVER['PHP_SELF']) === 'reports.php' ? 'active' : ''; ?>">
            📊 My Performance
        </a>
        <a href="profile.php" class="sidebar-item <?php echo basename($_SERVER['PHP_SELF']) === 'profile.php' ? 'active' : ''; ?>">
            👤 My Profile
        </a>
        <a href="support.php" class="sidebar-item <?php echo basename($_SERVER['PHP_SELF']) === 'support.php' ? 'active' : ''; ?>">
            🎧 Support
        </a>
        <a href="../auth/logout.php" class="sidebar-item" style="margin-top: 2rem; color: var(--danger-color);">
            🚪 Logout
        </a>
    </nav>
</div>
