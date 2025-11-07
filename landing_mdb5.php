<?php
// HealthSure Landing Page with MDB5 (Material Design for Bootstrap 5)
require_once 'config/library_config.php';

// Configure MDB5
$library_name = 'mdb5';
$config = getLibraryConfig($library_name);

// Page configuration
$page_title = 'HealthSure - Insurance Management System';
$show_nav = true;
$show_footer = true;

// Navigation items
$nav_items = [
    ['url' => '#portals', 'text' => 'Portals', 'icon' => 'fas fa-tachometer-alt'],
    ['url' => '#features', 'text' => 'Features', 'icon' => 'fas fa-star'],
    ['url' => 'user-guide.php', 'text' => 'Guide', 'icon' => 'fas fa-book'],
    ['url' => '#contact', 'text' => 'Contact', 'icon' => 'fas fa-envelope']
];

// Custom CSS for MDB5 Material Design
$custom_css = '
    /* Material Design Enhancements */
    :root {
        --mdb-primary: #1976d2;
        --mdb-secondary: #424242;
        --mdb-success: #00c851;
        --mdb-info: #33b5e5;
        --mdb-warning: #ffbb33;
        --mdb-danger: #ff4444;
    }
    
    body {
        font-family: "Roboto", sans-serif;
    }
    
    /* Hero Section with Material Design */
    .hero-section {
        background: linear-gradient(135deg, #1976d2 0%, #1565c0 100%);
        color: white;
        padding: 120px 0 80px;
        position: relative;
        overflow: hidden;
    }
    
    .hero-section::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url("data:image/svg+xml,%3Csvg width=\"60\" height=\"60\" viewBox=\"0 0 60 60\" xmlns=\"http://www.w3.org/2000/svg\"%3E%3Cg fill=\"none\" fill-rule=\"evenodd\"%3E%3Cg fill=\"%23ffffff\" fill-opacity=\"0.1\"%3E%3Ccircle cx=\"30\" cy=\"30\" r=\"2\"/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        opacity: 0.3;
    }
    
    .hero-content {
        position: relative;
        z-index: 2;
    }
    
    /* Material Design Cards */
    .portal-card {
        transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        border-radius: 16px;
        overflow: hidden;
        height: 100%;
        border: none;
        box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    }
    
    .portal-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 12px 40px rgba(0,0,0,0.15);
    }
    
    .portal-icon {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 24px;
        font-size: 2.5rem;
        color: white;
        position: relative;
        overflow: hidden;
    }
    
    .portal-icon::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(45deg, rgba(255,255,255,0.1), rgba(255,255,255,0.3));
        border-radius: 50%;
    }
    
    .admin-icon { 
        background: linear-gradient(135deg, #f44336, #d32f2f);
        box-shadow: 0 8px 25px rgba(244, 67, 54, 0.3);
    }
    .agent-icon { 
        background: linear-gradient(135deg, #ff9800, #f57c00);
        box-shadow: 0 8px 25px rgba(255, 152, 0, 0.3);
    }
    .customer-icon { 
        background: linear-gradient(135deg, #4caf50, #388e3c);
        box-shadow: 0 8px 25px rgba(76, 175, 80, 0.3);
    }
    
    /* Feature Cards with Material Design */
    .feature-card {
        border-radius: 16px;
        transition: all 0.3s ease;
        height: 100%;
        border: none;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        overflow: hidden;
        position: relative;
    }
    
    .feature-card::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #1976d2, #42a5f5);
    }
    
    .feature-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.12);
    }
    
    .feature-icon {
        font-size: 3rem;
        background: linear-gradient(135deg, #1976d2, #42a5f5);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        margin-bottom: 20px;
    }
    
    /* Material Design Buttons */
    .btn-material {
        border-radius: 24px;
        padding: 12px 32px;
        font-weight: 500;
        text-transform: none;
        letter-spacing: 0.5px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }
    
    .btn-material:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.3);
    }
    
    /* Navbar Material Design */
    .navbar {
        background: linear-gradient(135deg, #1976d2, #1565c0) !important;
        box-shadow: 0 4px 20px rgba(25, 118, 210, 0.3);
        backdrop-filter: blur(10px);
    }
    
    .navbar-brand {
        font-weight: 500;
        font-size: 1.5rem;
        display: flex;
        align-items: center;
    }
    
    .brand-icon {
        width: 40px;
        height: 40px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 12px;
        font-size: 1.2rem;
    }
    
    /* Footer Material Design */
    .footer-material {
        background: linear-gradient(135deg, #263238, #37474f);
        color: white;
        padding: 60px 0 20px;
    }
    
    .footer-section h5 {
        font-weight: 500;
        margin-bottom: 24px;
        color: white;
    }
    
    .footer-links a {
        color: rgba(255, 255, 255, 0.8);
        text-decoration: none;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        padding: 4px 0;
    }
    
    .footer-links a:hover {
        color: #42a5f5;
        transform: translateX(8px);
    }
    
    .footer-links a::before {
        content: "→";
        margin-right: 8px;
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    
    .footer-links a:hover::before {
        opacity: 1;
    }
    
    /* Social buttons */
    .social-btn {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin: 0 8px;
        transition: all 0.3s ease;
        color: white;
        text-decoration: none;
    }
    
    .social-btn:hover {
        transform: translateY(-4px);
        color: white;
    }
    
    .social-facebook { background: linear-gradient(135deg, #3b5998, #2d4373); }
    .social-twitter { background: linear-gradient(135deg, #1da1f2, #0d8bd9); }
    .social-linkedin { background: linear-gradient(135deg, #0077b5, #005885); }
    .social-email { background: linear-gradient(135deg, #ea4335, #d33b2c); }
    
    /* Animations */
    .fade-in-up {
        opacity: 0;
        transform: translateY(30px);
        transition: all 0.8s ease;
    }
    
    .fade-in-up.animate {
        opacity: 1;
        transform: translateY(0);
    }
    
    /* Quick Links Section */
    .quick-links {
        background: linear-gradient(135deg, #f8f9fa, #e9ecef);
        padding: 60px 0;
    }
    
    .quick-link-btn {
        margin: 8px;
        min-width: 160px;
    }
';

// Custom JavaScript for MDB5
$custom_js = '
    // Smooth scrolling for anchor links
    document.querySelectorAll(\'a[href^="#"]\').forEach(anchor => {
        anchor.addEventListener(\'click\', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute(\'href\'));
            if (target) {
                const headerHeight = 76; // Navbar height
                const targetPosition = target.offsetTop - headerHeight;
                
                window.scrollTo({
                    top: targetPosition,
                    behavior: \'smooth\'
                });
            }
        });
    });
    
    // Intersection Observer for animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: \'0px 0px -50px 0px\'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add(\'animate\');
            }
        });
    }, observerOptions);
    
    // Observe all fade-in elements
    document.querySelectorAll(\'.fade-in-up\').forEach(el => {
        observer.observe(el);
    });
    
    // Add staggered animation delays
    document.querySelectorAll(\'.portal-card\').forEach((card, index) => {
        card.style.animationDelay = `${index * 0.1}s`;
    });
    
    document.querySelectorAll(\'.feature-card\').forEach((card, index) => {
        card.style.animationDelay = `${index * 0.1}s`;
    });
';

// Merge configuration
extract($config);

// Start output buffering for content
ob_start();
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container hero-content">
        <div class="text-center">
            <h1 class="display-3 fw-light mb-4" style="font-weight: 300;">
                HealthSure
            </h1>
            <p class="lead mb-4" style="font-size: 1.4rem; opacity: 0.9;">
                Complete Health Insurance Management System
            </p>
            <p class="mb-5" style="font-size: 1.1rem; opacity: 0.8; max-width: 600px; margin: 0 auto;">
                Manage insurance policies, process claims, and track payments with our comprehensive 
                digital platform designed for customers, agents, and administrators.
            </p>
            <div class="d-flex flex-wrap justify-content-center gap-3">
                <a href="#portals" class="btn btn-light btn-lg btn-material" data-mdb-ripple-init>
                    <i class="fas fa-rocket me-2"></i>Get Started
                </a>
                <a href="user-guide.php" class="btn btn-outline-light btn-lg btn-material" data-mdb-ripple-init>
                    <i class="fas fa-info-circle me-2"></i>Learn More
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Portal Selection -->
<section id="portals" class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 text-primary mb-3">Choose Your Portal</h2>
            <p class="lead text-muted">Select your role to access the appropriate dashboard</p>
        </div>
        
        <div class="row g-4">
            <!-- Admin Portal -->
            <div class="col-lg-4 col-md-6">
                <div class="card portal-card fade-in-up" data-mdb-ripple-init>
                    <div class="card-body text-center p-4">
                        <div class="portal-icon admin-icon">
                            <i class="fas fa-user-shield"></i>
                        </div>
                        <h5 class="card-title fw-bold mb-3">Admin Portal</h5>
                        <p class="card-text text-muted mb-4">Complete system management and control</p>
                        <ul class="list-unstyled text-start mb-4">
                            <li class="mb-2"><i class="fas fa-check text-primary me-2"></i> Manage all policies</li>
                            <li class="mb-2"><i class="fas fa-check text-primary me-2"></i> Create & manage agents</li>
                            <li class="mb-2"><i class="fas fa-check text-primary me-2"></i> Process claims</li>
                            <li class="mb-2"><i class="fas fa-check text-primary me-2"></i> Generate reports</li>
                            <li class="mb-2"><i class="fas fa-check text-primary me-2"></i> System settings</li>
                        </ul>
                        <a href="auth/login.php?role=admin" class="btn btn-danger w-100 btn-material" data-mdb-ripple-init>
                            <i class="fas fa-sign-in-alt me-2"></i>Admin Login
                        </a>
                        <small class="text-muted d-block mt-3">
                            <strong>Default:</strong> admin@healthsure.com / password
                        </small>
                    </div>
                </div>
            </div>

            <!-- Agent Portal -->
            <div class="col-lg-4 col-md-6">
                <div class="card portal-card fade-in-up" data-mdb-ripple-init>
                    <div class="card-body text-center p-4">
                        <div class="portal-icon agent-icon">
                            <i class="fas fa-handshake"></i>
                        </div>
                        <h5 class="card-title fw-bold mb-3">Agent Portal</h5>
                        <p class="card-text text-muted mb-4">Customer management and sales tracking</p>
                        <ul class="list-unstyled text-start mb-4">
                            <li class="mb-2"><i class="fas fa-check text-primary me-2"></i> Manage assigned customers</li>
                            <li class="mb-2"><i class="fas fa-check text-primary me-2"></i> Assist with policy applications</li>
                            <li class="mb-2"><i class="fas fa-check text-primary me-2"></i> Help with claims</li>
                            <li class="mb-2"><i class="fas fa-check text-primary me-2"></i> Track performance</li>
                            <li class="mb-2"><i class="fas fa-check text-primary me-2"></i> Customer support</li>
                        </ul>
                        <a href="auth/login.php?role=agent" class="btn btn-warning w-100 btn-material" data-mdb-ripple-init>
                            <i class="fas fa-sign-in-alt me-2"></i>Agent Login
                        </a>
                        <small class="text-muted d-block mt-3">
                            <strong>Note:</strong> Admin creates agent accounts
                        </small>
                    </div>
                </div>
            </div>

            <!-- Customer Portal -->
            <div class="col-lg-4 col-md-6">
                <div class="card portal-card fade-in-up" data-mdb-ripple-init>
                    <div class="card-body text-center p-4">
                        <div class="portal-icon customer-icon">
                            <i class="fas fa-user"></i>
                        </div>
                        <h5 class="card-title fw-bold mb-3">Customer Portal</h5>
                        <p class="card-text text-muted mb-4">Insurance policies and claims management</p>
                        <ul class="list-unstyled text-start mb-4">
                            <li class="mb-2"><i class="fas fa-check text-primary me-2"></i> Browse & buy policies</li>
                            <li class="mb-2"><i class="fas fa-check text-primary me-2"></i> File insurance claims</li>
                            <li class="mb-2"><i class="fas fa-check text-primary me-2"></i> Make premium payments</li>
                            <li class="mb-2"><i class="fas fa-check text-primary me-2"></i> Track policy status</li>
                            <li class="mb-2"><i class="fas fa-check text-primary me-2"></i> Download documents</li>
                        </ul>
                        <div class="d-grid gap-2">
                            <a href="auth/login.php?role=customer" class="btn btn-success btn-material" data-mdb-ripple-init>
                                <i class="fas fa-sign-in-alt me-2"></i>Customer Login
                            </a>
                            <a href="auth/register.php" class="btn btn-outline-success btn-material" data-mdb-ripple-init>
                                <i class="fas fa-user-plus me-2"></i>New Customer? Register
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section id="features" class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 text-primary mb-3">System Features</h2>
            <p class="lead text-muted">Comprehensive insurance management capabilities</p>
        </div>
        
        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <div class="card feature-card fade-in-up h-100" data-mdb-ripple-init>
                    <div class="card-body text-center p-4">
                        <i class="fas fa-clipboard-list feature-icon"></i>
                        <h5 class="fw-bold mb-3">Policy Management</h5>
                        <p class="text-muted">Create and manage Health, Life, and Family insurance policies with specialized features and automated workflows.</p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <div class="card feature-card fade-in-up h-100" data-mdb-ripple-init>
                    <div class="card-body text-center p-4">
                        <i class="fas fa-file-medical feature-icon"></i>
                        <h5 class="fw-bold mb-3">Claims Processing</h5>
                        <p class="text-muted">Streamlined claim filing, review, and approval process with document management and real-time tracking.</p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <div class="card feature-card fade-in-up h-100" data-mdb-ripple-init>
                    <div class="card-body text-center p-4">
                        <i class="fas fa-credit-card feature-icon"></i>
                        <h5 class="fw-bold mb-3">Payment Tracking</h5>
                        <p class="text-muted">Complete payment management for premiums and claim settlements with multiple payment methods.</p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <div class="card feature-card fade-in-up h-100" data-mdb-ripple-init>
                    <div class="card-body text-center p-4">
                        <i class="fas fa-chart-bar feature-icon"></i>
                        <h5 class="fw-bold mb-3">Analytics & Reports</h5>
                        <p class="text-muted">Comprehensive reporting and analytics for business insights with customizable dashboards.</p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <div class="card feature-card fade-in-up h-100" data-mdb-ripple-init>
                    <div class="card-body text-center p-4">
                        <i class="fas fa-shield-alt feature-icon"></i>
                        <h5 class="fw-bold mb-3">Secure & Reliable</h5>
                        <p class="text-muted">Role-based access control with secure data handling, encryption, and comprehensive audit trails.</p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <div class="card feature-card fade-in-up h-100" data-mdb-ripple-init>
                    <div class="card-body text-center p-4">
                        <i class="fas fa-mobile-alt feature-icon"></i>
                        <h5 class="fw-bold mb-3">Responsive Design</h5>
                        <p class="text-muted">Modern, mobile-friendly interface that works seamlessly on all devices and screen sizes.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Quick Links -->
<section class="quick-links">
    <div class="container">
        <div class="text-center">
            <h3 class="display-6 text-primary mb-4">Quick Links</h3>
            <div class="d-flex flex-wrap justify-content-center">
                <a href="user-guide.php" class="btn btn-primary btn-material quick-link-btn" data-mdb-ripple-init>
                    <i class="fas fa-book me-2"></i>User Guide
                </a>
                <a href="setup.php" class="btn btn-warning btn-material quick-link-btn" data-mdb-ripple-init>
                    <i class="fas fa-cog me-2"></i>System Setup
                </a>
                <a href="debug.php" class="btn btn-success btn-material quick-link-btn" data-mdb-ripple-init>
                    <i class="fas fa-bug me-2"></i>System Status
                </a>
                <a href="add_sample_data.php" class="btn btn-info btn-material quick-link-btn" data-mdb-ripple-init>
                    <i class="fas fa-database me-2"></i>Sample Data
                </a>
            </div>
        </div>
    </div>
</section>

<?php
$content = ob_get_clean();

// Override footer to use custom Material Design footer
$show_footer = false;

// Include the template
include 'templates/local_library_template.php';
?>

<!-- Custom Material Design Footer -->
<footer id="contact" class="footer-material">
    <div class="container">
        <div class="row">
            <div class="col-lg-6 col-md-6 mb-4">
                <h5 class="fw-bold mb-4">HealthSure</h5>
                <p class="mb-4" style="color: rgba(255, 255, 255, 0.8); line-height: 1.6;">
                    Complete Health Insurance Management System designed to streamline policy management, 
                    claims processing, and customer service for insurance providers and their clients.
                </p>
                <div class="d-flex">
                    <a href="#" class="social-btn social-facebook" data-mdb-ripple-init data-mdb-ripple-color="light">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="#" class="social-btn social-twitter" data-mdb-ripple-init data-mdb-ripple-color="light">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="#" class="social-btn social-linkedin" data-mdb-ripple-init data-mdb-ripple-color="light">
                        <i class="fab fa-linkedin-in"></i>
                    </a>
                    <a href="#" class="social-btn social-email" data-mdb-ripple-init data-mdb-ripple-color="light">
                        <i class="fas fa-envelope"></i>
                    </a>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-4">
                <h5 class="fw-bold mb-4">Quick Links</h5>
                <ul class="footer-links list-unstyled">
                    <li class="mb-2"><a href="#portals">Portal Access</a></li>
                    <li class="mb-2"><a href="#features">Features</a></li>
                    <li class="mb-2"><a href="user-guide.php">User Guide</a></li>
                    <li class="mb-2"><a href="setup.php">System Setup</a></li>
                    <li class="mb-2"><a href="debug.php">System Status</a></li>
                </ul>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-4">
                <h5 class="fw-bold mb-4">Support</h5>
                <ul class="footer-links list-unstyled">
                    <li class="mb-2"><a href="auth/login.php">Customer Login</a></li>
                    <li class="mb-2"><a href="auth/register.php">New Registration</a></li>
                    <li class="mb-2"><a href="user-guide.php">Help Center</a></li>
                    <li class="mb-2"><a href="#contact">Contact Us</a></li>
                    <li class="mb-2"><a href="add_sample_data.php">Sample Data</a></li>
                </ul>
            </div>
        </div>
        
        <hr style="border-color: rgba(255, 255, 255, 0.2); margin: 40px 0 20px;">
        
        <div class="text-center">
            <p class="mb-0" style="color: rgba(255, 255, 255, 0.6);">
                &copy; 2024 HealthSure Insurance Management System. All rights reserved. | 
                Built with MDB5 & Material Design
            </p>
        </div>
    </div>
</footer>

<script>
    <?php echo $custom_js; ?>
</script>
