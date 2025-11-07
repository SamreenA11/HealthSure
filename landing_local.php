<?php
// Landing page using local library
require_once 'config/library_config.php';

// CONFIGURE YOUR LIBRARY HERE
// Change this to match your downloaded library
$library_name = 'bootstrap'; // Options: bootstrap, materialize, bulma, foundation

// Get library configuration
$config = getLibraryConfig($library_name);

// Page configuration
$page_title = 'HealthSure - Insurance Management System';
$page_header = 'HealthSure';
$page_description = 'Complete Health Insurance Management System';
$show_nav = true;
$show_footer = true;

// Navigation items
$nav_items = [
    ['url' => '#portals', 'text' => 'Portals', 'icon' => 'fas fa-tachometer-alt'],
    ['url' => '#features', 'text' => 'Features', 'icon' => 'fas fa-star'],
    ['url' => 'user-guide.php', 'text' => 'Guide', 'icon' => 'fas fa-book'],
    ['url' => '#contact', 'text' => 'Contact', 'icon' => 'fas fa-envelope'],
    ['url' => 'auth/login.php', 'text' => 'Login', 'class' => 'btn btn-outline-light'],
    ['url' => 'auth/register.php', 'text' => 'Register', 'class' => 'btn btn-light']
];

// Custom CSS for this page
$custom_css = '
    .hero-section {
        background: linear-gradient(135deg, #007bff, #0056b3);
        color: white;
        padding: 80px 0;
        text-align: center;
    }
    
    .portal-card {
        transition: all 0.3s ease;
        border: none;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        border-radius: 12px;
        height: 100%;
    }
    
    .portal-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }
    
    .portal-icon {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
        font-size: 2rem;
        color: white;
    }
    
    .admin-icon { background: linear-gradient(135deg, #dc3545, #c82333); }
    .agent-icon { background: linear-gradient(135deg, #fd7e14, #e55a00); }
    .customer-icon { background: linear-gradient(135deg, #28a745, #1e7e34); }
    
    .feature-card {
        text-align: center;
        padding: 30px 20px;
        border-radius: 12px;
        height: 100%;
    }
    
    .feature-icon {
        font-size: 3rem;
        color: #007bff;
        margin-bottom: 20px;
    }
';

// Custom JavaScript
$custom_js = '
    // Smooth scrolling for anchor links
    document.querySelectorAll(\'a[href^="#"]\').forEach(anchor => {
        anchor.addEventListener(\'click\', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute(\'href\'));
            if (target) {
                target.scrollIntoView({
                    behavior: \'smooth\',
                    block: \'start\'
                });
            }
        });
    });
    
    // Add animation on scroll
    const observerOptions = {
        threshold: 0.1,
        rootMargin: \'0px 0px -50px 0px\'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = \'1\';
                entry.target.style.transform = \'translateY(0)\';
            }
        });
    }, observerOptions);
    
    document.querySelectorAll(\'.fade-in\').forEach(el => {
        el.style.opacity = \'0\';
        el.style.transform = \'translateY(30px)\';
        el.style.transition = \'all 0.8s ease\';
        observer.observe(el);
    });
';

// Merge configuration
extract($config);

// Start output buffering for content
ob_start();
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="<?php echo $container_class; ?>">
        <h1 style="font-size: 3.5rem; font-weight: 300; margin-bottom: 20px;">HealthSure</h1>
        <p style="font-size: 1.3rem; margin-bottom: 30px; opacity: 0.9;">
            Complete Health Insurance Management System
        </p>
        <p style="font-size: 1.1rem; opacity: 0.8; max-width: 600px; margin: 0 auto 40px;">
            Manage insurance policies, process claims, and track payments with our comprehensive digital platform.
        </p>
        <div>
            <a href="#portals" class="btn btn-light btn-lg me-3">
                <i class="fas fa-rocket"></i> Get Started
            </a>
            <a href="user-guide.php" class="btn btn-outline-light btn-lg">
                <i class="fas fa-info-circle"></i> Learn More
            </a>
        </div>
    </div>
</section>

<!-- Portal Selection -->
<section id="portals" class="py-5">
    <div class="<?php echo $container_class; ?>">
        <div class="text-center mb-5">
            <h2 class="text-primary">Choose Your Portal</h2>
            <p class="lead text-muted">Select your role to access the appropriate dashboard</p>
        </div>
        
        <div class="row g-4">
            <!-- Admin Portal -->
            <div class="col-lg-4 col-md-6 fade-in">
                <div class="card portal-card h-100">
                    <div class="card-body text-center">
                        <div class="portal-icon admin-icon">
                            <i class="fas fa-user-shield"></i>
                        </div>
                        <h5 class="card-title">Admin Portal</h5>
                        <p class="card-text text-muted">Complete system management and control</p>
                        <ul class="list-unstyled text-start mb-4">
                            <li><i class="fas fa-check text-primary me-2"></i> Manage all policies</li>
                            <li><i class="fas fa-check text-primary me-2"></i> Create & manage agents</li>
                            <li><i class="fas fa-check text-primary me-2"></i> Process claims</li>
                            <li><i class="fas fa-check text-primary me-2"></i> Generate reports</li>
                            <li><i class="fas fa-check text-primary me-2"></i> System settings</li>
                        </ul>
                        <a href="auth/login.php?role=admin" class="btn btn-danger w-100">
                            <i class="fas fa-sign-in-alt"></i> Admin Login
                        </a>
                        <small class="text-muted d-block mt-2">
                            <strong>Default:</strong> admin@healthsure.com / password
                        </small>
                    </div>
                </div>
            </div>

            <!-- Agent Portal -->
            <div class="col-lg-4 col-md-6 fade-in">
                <div class="card portal-card h-100">
                    <div class="card-body text-center">
                        <div class="portal-icon agent-icon">
                            <i class="fas fa-handshake"></i>
                        </div>
                        <h5 class="card-title">Agent Portal</h5>
                        <p class="card-text text-muted">Customer management and sales tracking</p>
                        <ul class="list-unstyled text-start mb-4">
                            <li><i class="fas fa-check text-primary me-2"></i> Manage assigned customers</li>
                            <li><i class="fas fa-check text-primary me-2"></i> Assist with policy applications</li>
                            <li><i class="fas fa-check text-primary me-2"></i> Help with claims</li>
                            <li><i class="fas fa-check text-primary me-2"></i> Track performance</li>
                            <li><i class="fas fa-check text-primary me-2"></i> Customer support</li>
                        </ul>
                        <a href="auth/login.php?role=agent" class="btn btn-warning w-100">
                            <i class="fas fa-sign-in-alt"></i> Agent Login
                        </a>
                        <small class="text-muted d-block mt-2">
                            <strong>Note:</strong> Admin creates agent accounts
                        </small>
                    </div>
                </div>
            </div>

            <!-- Customer Portal -->
            <div class="col-lg-4 col-md-6 fade-in">
                <div class="card portal-card h-100">
                    <div class="card-body text-center">
                        <div class="portal-icon customer-icon">
                            <i class="fas fa-user"></i>
                        </div>
                        <h5 class="card-title">Customer Portal</h5>
                        <p class="card-text text-muted">Insurance policies and claims management</p>
                        <ul class="list-unstyled text-start mb-4">
                            <li><i class="fas fa-check text-primary me-2"></i> Browse & buy policies</li>
                            <li><i class="fas fa-check text-primary me-2"></i> File insurance claims</li>
                            <li><i class="fas fa-check text-primary me-2"></i> Make premium payments</li>
                            <li><i class="fas fa-check text-primary me-2"></i> Track policy status</li>
                            <li><i class="fas fa-check text-primary me-2"></i> Download documents</li>
                        </ul>
                        <a href="auth/login.php?role=customer" class="btn btn-success w-100 mb-2">
                            <i class="fas fa-sign-in-alt"></i> Customer Login
                        </a>
                        <a href="auth/register.php" class="btn btn-outline-success w-100">
                            <i class="fas fa-user-plus"></i> New Customer? Register
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section id="features" class="py-5 bg-light">
    <div class="<?php echo $container_class; ?>">
        <div class="text-center mb-5">
            <h2 class="text-primary">System Features</h2>
            <p class="lead text-muted">Comprehensive insurance management capabilities</p>
        </div>
        
        <div class="row g-4">
            <div class="col-lg-4 col-md-6 fade-in">
                <div class="card feature-card h-100">
                    <div class="card-body">
                        <i class="fas fa-clipboard-list feature-icon"></i>
                        <h5>Policy Management</h5>
                        <p class="text-muted">Create and manage Health, Life, and Family insurance policies with specialized features.</p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6 fade-in">
                <div class="card feature-card h-100">
                    <div class="card-body">
                        <i class="fas fa-file-medical feature-icon"></i>
                        <h5>Claims Processing</h5>
                        <p class="text-muted">Streamlined claim filing, review, and approval process with document management.</p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6 fade-in">
                <div class="card feature-card h-100">
                    <div class="card-body">
                        <i class="fas fa-credit-card feature-icon"></i>
                        <h5>Payment Tracking</h5>
                        <p class="text-muted">Complete payment management for premiums and claim settlements.</p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6 fade-in">
                <div class="card feature-card h-100">
                    <div class="card-body">
                        <i class="fas fa-chart-bar feature-icon"></i>
                        <h5>Analytics & Reports</h5>
                        <p class="text-muted">Comprehensive reporting and analytics for business insights.</p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6 fade-in">
                <div class="card feature-card h-100">
                    <div class="card-body">
                        <i class="fas fa-shield-alt feature-icon"></i>
                        <h5>Secure & Reliable</h5>
                        <p class="text-muted">Role-based access control with secure data handling and encryption.</p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6 fade-in">
                <div class="card feature-card h-100">
                    <div class="card-body">
                        <i class="fas fa-mobile-alt feature-icon"></i>
                        <h5>Responsive Design</h5>
                        <p class="text-muted">Modern, mobile-friendly interface that works on all devices.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Quick Links -->
<section class="py-5">
    <div class="<?php echo $container_class; ?>">
        <div class="text-center">
            <h3 class="text-primary mb-4">Quick Links</h3>
            <div class="d-flex flex-wrap justify-content-center gap-3">
                <a href="user-guide.php" class="btn btn-primary">
                    <i class="fas fa-book"></i> User Guide
                </a>
                <a href="setup.php" class="btn btn-warning">
                    <i class="fas fa-cog"></i> System Setup
                </a>
                <a href="debug.php" class="btn btn-success">
                    <i class="fas fa-bug"></i> System Status
                </a>
                <a href="add_sample_data.php" class="btn btn-info">
                    <i class="fas fa-database"></i> Sample Data
                </a>
            </div>
        </div>
    </div>
</section>

<?php
$content = ob_get_clean();

// Include the template
include 'templates/local_library_template.php';
?>
