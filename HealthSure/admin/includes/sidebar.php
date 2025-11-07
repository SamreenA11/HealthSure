<div class="sidebar">
    <div class="sidebar-header">
        <h3>HealthSure</h3>
        <p style="font-size: 0.875rem; color: var(--text-light);">Admin Panel</p>
    </div>
    <nav class="sidebar-menu">
        <a href="dashboard.php" class="sidebar-item <?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : ''; ?>">
            📊 Dashboard
        </a>
        <a href="policies.php" class="sidebar-item <?php echo basename($_SERVER['PHP_SELF']) === 'policies.php' ? 'active' : ''; ?>">
            📋 Policies
        </a>
        <a href="customers.php" class="sidebar-item <?php echo basename($_SERVER['PHP_SELF']) === 'customers.php' ? 'active' : ''; ?>">
            👥 Customers
        </a>
        <a href="agents.php" class="sidebar-item <?php echo basename($_SERVER['PHP_SELF']) === 'agents.php' ? 'active' : ''; ?>">
            🤝 Agents
        </a>
        <a href="claims.php" class="sidebar-item <?php echo basename($_SERVER['PHP_SELF']) === 'claims.php' ? 'active' : ''; ?>">
            📄 Claims
        </a>
        <a href="payments.php" class="sidebar-item <?php echo basename($_SERVER['PHP_SELF']) === 'payments.php' ? 'active' : ''; ?>">
            💳 Payments
        </a>
        <a href="reports.php" class="sidebar-item <?php echo basename($_SERVER['PHP_SELF']) === 'reports.php' ? 'active' : ''; ?>">
            📈 Reports
        </a>
        <a href="settings.php" class="sidebar-item <?php echo basename($_SERVER['PHP_SELF']) === 'settings.php' ? 'active' : ''; ?>">
            ⚙️ Settings
        </a>
        <a href="../auth/logout.php" class="sidebar-item" style="margin-top: 2rem; color: var(--danger-color);">
            🚪 Logout
        </a>
    </nav>
</div>
