<div class="sidebar">
    <div class="sidebar-header">
        <h3>HealthSure</h3>
        <p style="font-size: 0.875rem; color: var(--text-light);">Customer Portal</p>
    </div>
    <nav class="sidebar-menu">
        <a href="dashboard.php" class="sidebar-item <?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : ''; ?>">
            🏠 Dashboard
        </a>
        <a href="browse-policies.php" class="sidebar-item <?php echo basename($_SERVER['PHP_SELF']) === 'browse-policies.php' ? 'active' : ''; ?>">
            📋 Browse Policies
        </a>
        <a href="my-policies.php" class="sidebar-item <?php echo basename($_SERVER['PHP_SELF']) === 'my-policies.php' ? 'active' : ''; ?>">
            📄 My Policies
        </a>
        <a href="my-claims.php" class="sidebar-item <?php echo basename($_SERVER['PHP_SELF']) === 'my-claims.php' ? 'active' : ''; ?>">
            📋 My Claims
        </a>
        <a href="file-claim.php" class="sidebar-item <?php echo basename($_SERVER['PHP_SELF']) === 'file-claim.php' ? 'active' : ''; ?>">
            ➕ File New Claim
        </a>
        <a href="make-payment.php" class="sidebar-item <?php echo basename($_SERVER['PHP_SELF']) === 'make-payment.php' ? 'active' : ''; ?>">
            💳 Make Payment
        </a>
        <a href="payment-history.php" class="sidebar-item <?php echo basename($_SERVER['PHP_SELF']) === 'payment-history.php' ? 'active' : ''; ?>">
            📊 Payment History
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
