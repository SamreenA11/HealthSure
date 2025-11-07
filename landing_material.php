<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HealthSure - Insurance Management System</title>
    
    <!-- Materialize CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css" rel="stylesheet">
    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #2196f3;
            --primary-dark: #1976d2;
            --accent-color: #ff4081;
            --success-color: #4caf50;
            --warning-color: #ff9800;
            --error-color: #f44336;
        }
        
        body {
            font-family: 'Roboto', sans-serif;
            background: #fafafa;
        }
        
        /* Custom Material Header */
        .navbar-fixed nav {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            box-shadow: 0 4px 20px rgba(33, 150, 243, 0.3);
        }
        
        .brand-logo {
            display: flex;
            align-items: center;
            font-weight: 500;
        }
        
        .brand-icon {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
        }
        
        /* Hero Section */
        .hero-section {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: white;
            padding: 80px 0 60px;
            position: relative;
            overflow: hidden;
        }
        
        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse"><path d="M 10 0 L 0 0 0 10" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="0.5"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
            opacity: 0.3;
        }
        
        .hero-content {
            position: relative;
            z-index: 2;
        }
        
        /* Material Cards */
        .portal-card {
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            border-radius: 12px;
            overflow: hidden;
        }
        
        .portal-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
        }
        
        .card-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 2.5rem;
            color: white;
        }
        
        .admin-card .card-icon { background: linear-gradient(135deg, #f44336, #d32f2f); }
        .agent-card .card-icon { background: linear-gradient(135deg, #ff9800, #f57c00); }
        .customer-card .card-icon { background: linear-gradient(135deg, #4caf50, #388e3c); }
        
        /* Feature Cards */
        .feature-card {
            border-radius: 12px;
            transition: all 0.3s ease;
            height: 100%;
        }
        
        .feature-card:hover {
            transform: translateY(-4px);
        }
        
        .feature-icon {
            font-size: 3rem;
            color: var(--primary-color);
            margin-bottom: 16px;
        }
        
        /* Material Buttons */
        .btn-material {
            border-radius: 24px;
            text-transform: none;
            font-weight: 500;
            padding: 0 24px;
            height: 48px;
            line-height: 48px;
        }
        
        /* Footer */
        .page-footer {
            background: linear-gradient(135deg, #263238, #37474f);
        }
        
        .footer-section h5 {
            font-weight: 500;
            margin-bottom: 20px;
        }
        
        .footer-links a {
            color: rgba(255, 255, 255, 0.8);
            transition: color 0.3s ease;
        }
        
        .footer-links a:hover {
            color: var(--primary-color);
        }
        
        /* Animations */
        .fade-in-up {
            opacity: 0;
            transform: translateY(30px);
            animation: fadeInUp 0.8s ease-out forwards;
        }
        
        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .fade-in-up:nth-child(1) { animation-delay: 0.1s; }
        .fade-in-up:nth-child(2) { animation-delay: 0.2s; }
        .fade-in-up:nth-child(3) { animation-delay: 0.3s; }
    </style>
</head>
<body>
    <!-- Navigation -->
    <div class="navbar-fixed">
        <nav>
            <div class="nav-wrapper container">
                <a href="landing_material.php" class="brand-logo">
                    <div class="brand-icon">
                        <i class="material-icons">local_hospital</i>
                    </div>
                    HealthSure
                </a>
                <ul class="right hide-on-med-and-down">
                    <li><a href="#portals">Portals</a></li>
                    <li><a href="#features">Features</a></li>
                    <li><a href="user-guide.php">Guide</a></li>
                    <li><a href="#contact">Contact</a></li>
                    <li><a href="auth/login.php" class="btn btn-material white blue-text">Login</a></li>
                    <li><a href="auth/register.php" class="btn btn-material">Register</a></li>
                </ul>
                <a href="#" data-target="mobile-nav" class="sidenav-trigger"><i class="material-icons">menu</i></a>
            </div>
        </nav>
    </div>

    <!-- Mobile Navigation -->
    <ul class="sidenav" id="mobile-nav">
        <li><a href="#portals"><i class="material-icons">dashboard</i>Portals</a></li>
        <li><a href="#features"><i class="material-icons">star</i>Features</a></li>
        <li><a href="user-guide.php"><i class="material-icons">help</i>Guide</a></li>
        <li><a href="#contact"><i class="material-icons">contact_mail</i>Contact</a></li>
        <li><div class="divider"></div></li>
        <li><a href="auth/login.php"><i class="material-icons">login</i>Login</a></li>
        <li><a href="auth/register.php"><i class="material-icons">person_add</i>Register</a></li>
    </ul>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container hero-content">
            <div class="center-align">
                <h1 class="flow-text" style="font-size: 3.5rem; font-weight: 300; margin-bottom: 20px;">
                    HealthSure
                </h1>
                <h5 class="flow-text" style="font-weight: 300; opacity: 0.9; margin-bottom: 30px;">
                    Complete Health Insurance Management System
                </h5>
                <p class="flow-text" style="font-size: 1.2rem; opacity: 0.8; max-width: 600px; margin: 0 auto 40px;">
                    Manage insurance policies, process claims, and track payments with our comprehensive digital platform designed for customers, agents, and administrators.
                </p>
                <div>
                    <a href="#portals" class="btn-large btn-material waves-effect waves-light white blue-text" style="margin-right: 16px;">
                        <i class="material-icons left">rocket_launch</i>Get Started
                    </a>
                    <a href="user-guide.php" class="btn-large btn-material waves-effect waves-light transparent" style="border: 2px solid rgba(255,255,255,0.5);">
                        <i class="material-icons left">info</i>Learn More
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Portal Selection -->
    <section id="portals" class="section" style="padding: 80px 0;">
        <div class="container">
            <div class="center-align" style="margin-bottom: 60px;">
                <h2 class="blue-text text-darken-2">Choose Your Portal</h2>
                <p class="flow-text grey-text text-darken-1">Select your role to access the appropriate dashboard</p>
            </div>
            
            <div class="row">
                <!-- Admin Portal -->
                <div class="col s12 m4 fade-in-up">
                    <div class="card portal-card admin-card hoverable">
                        <div class="card-content center-align" style="padding: 40px 24px;">
                            <div class="card-icon">
                                <i class="material-icons">admin_panel_settings</i>
                            </div>
                            <h5>Admin Portal</h5>
                            <p class="grey-text">Complete system management and control</p>
                            <ul class="left-align" style="margin: 20px 0;">
                                <li><i class="material-icons tiny blue-text">check</i> Manage all policies</li>
                                <li><i class="material-icons tiny blue-text">check</i> Create & manage agents</li>
                                <li><i class="material-icons tiny blue-text">check</i> Process claims</li>
                                <li><i class="material-icons tiny blue-text">check</i> Generate reports</li>
                                <li><i class="material-icons tiny blue-text">check</i> System settings</li>
                            </ul>
                        </div>
                        <div class="card-action center-align">
                            <a href="auth/login.php?role=admin" class="btn btn-material red waves-effect waves-light" style="width: 100%;">
                                <i class="material-icons left">login</i>Admin Login
                            </a>
                            <p class="grey-text" style="font-size: 0.8rem; margin-top: 8px;">
                                <strong>Default:</strong> admin@healthsure.com / password
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Agent Portal -->
                <div class="col s12 m4 fade-in-up">
                    <div class="card portal-card agent-card hoverable">
                        <div class="card-content center-align" style="padding: 40px 24px;">
                            <div class="card-icon">
                                <i class="material-icons">support_agent</i>
                            </div>
                            <h5>Agent Portal</h5>
                            <p class="grey-text">Customer management and sales tracking</p>
                            <ul class="left-align" style="margin: 20px 0;">
                                <li><i class="material-icons tiny blue-text">check</i> Manage assigned customers</li>
                                <li><i class="material-icons tiny blue-text">check</i> Assist with policy applications</li>
                                <li><i class="material-icons tiny blue-text">check</i> Help with claims</li>
                                <li><i class="material-icons tiny blue-text">check</i> Track performance</li>
                                <li><i class="material-icons tiny blue-text">check</i> Customer support</li>
                            </ul>
                        </div>
                        <div class="card-action center-align">
                            <a href="auth/login.php?role=agent" class="btn btn-material orange waves-effect waves-light" style="width: 100%;">
                                <i class="material-icons left">login</i>Agent Login
                            </a>
                            <p class="grey-text" style="font-size: 0.8rem; margin-top: 8px;">
                                <strong>Note:</strong> Admin creates agent accounts
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Customer Portal -->
                <div class="col s12 m4 fade-in-up">
                    <div class="card portal-card customer-card hoverable">
                        <div class="card-content center-align" style="padding: 40px 24px;">
                            <div class="card-icon">
                                <i class="material-icons">person</i>
                            </div>
                            <h5>Customer Portal</h5>
                            <p class="grey-text">Insurance policies and claims management</p>
                            <ul class="left-align" style="margin: 20px 0;">
                                <li><i class="material-icons tiny blue-text">check</i> Browse & buy policies</li>
                                <li><i class="material-icons tiny blue-text">check</i> File insurance claims</li>
                                <li><i class="material-icons tiny blue-text">check</i> Make premium payments</li>
                                <li><i class="material-icons tiny blue-text">check</i> Track policy status</li>
                                <li><i class="material-icons tiny blue-text">check</i> Download documents</li>
                            </ul>
                        </div>
                        <div class="card-action center-align">
                            <a href="auth/login.php?role=customer" class="btn btn-material green waves-effect waves-light" style="width: 100%; margin-bottom: 8px;">
                                <i class="material-icons left">login</i>Customer Login
                            </a>
                            <a href="auth/register.php" class="btn btn-material green lighten-1 waves-effect waves-light" style="width: 100%;">
                                <i class="material-icons left">person_add</i>New Customer? Register
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="section grey lighten-4" style="padding: 80px 0;">
        <div class="container">
            <div class="center-align" style="margin-bottom: 60px;">
                <h2 class="blue-text text-darken-2">System Features</h2>
                <p class="flow-text grey-text text-darken-1">Comprehensive insurance management capabilities</p>
            </div>
            
            <div class="row">
                <div class="col s12 m6 l4">
                    <div class="card feature-card hoverable">
                        <div class="card-content center-align">
                            <i class="material-icons feature-icon">assignment</i>
                            <h5>Policy Management</h5>
                            <p class="grey-text">Create and manage Health, Life, and Family insurance policies with specialized features and automated workflows.</p>
                        </div>
                    </div>
                </div>
                
                <div class="col s12 m6 l4">
                    <div class="card feature-card hoverable">
                        <div class="card-content center-align">
                            <i class="material-icons feature-icon">description</i>
                            <h5>Claims Processing</h5>
                            <p class="grey-text">Streamlined claim filing, review, and approval process with document management and real-time tracking.</p>
                        </div>
                    </div>
                </div>
                
                <div class="col s12 m6 l4">
                    <div class="card feature-card hoverable">
                        <div class="card-content center-align">
                            <i class="material-icons feature-icon">payment</i>
                            <h5>Payment Tracking</h5>
                            <p class="grey-text">Complete payment management for premiums and claim settlements with multiple payment methods.</p>
                        </div>
                    </div>
                </div>
                
                <div class="col s12 m6 l4">
                    <div class="card feature-card hoverable">
                        <div class="card-content center-align">
                            <i class="material-icons feature-icon">analytics</i>
                            <h5>Analytics & Reports</h5>
                            <p class="grey-text">Comprehensive reporting and analytics for business insights with customizable dashboards.</p>
                        </div>
                    </div>
                </div>
                
                <div class="col s12 m6 l4">
                    <div class="card feature-card hoverable">
                        <div class="card-content center-align">
                            <i class="material-icons feature-icon">security</i>
                            <h5>Secure & Reliable</h5>
                            <p class="grey-text">Role-based access control with secure data handling, encryption, and audit trails.</p>
                        </div>
                    </div>
                </div>
                
                <div class="col s12 m6 l4">
                    <div class="card feature-card hoverable">
                        <div class="card-content center-align">
                            <i class="material-icons feature-icon">devices</i>
                            <h5>Responsive Design</h5>
                            <p class="grey-text">Modern, mobile-friendly interface that works seamlessly on all devices and screen sizes.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Quick Links -->
    <section class="section" style="padding: 60px 0;">
        <div class="container">
            <div class="center-align">
                <h3 class="blue-text text-darken-2">Quick Links</h3>
                <div style="margin-top: 30px;">
                    <a href="user-guide.php" class="btn btn-material blue waves-effect waves-light" style="margin: 8px;">
                        <i class="material-icons left">book</i>User Guide
                    </a>
                    <a href="setup.php" class="btn btn-material orange waves-effect waves-light" style="margin: 8px;">
                        <i class="material-icons left">settings</i>System Setup
                    </a>
                    <a href="debug.php" class="btn btn-material green waves-effect waves-light" style="margin: 8px;">
                        <i class="material-icons left">bug_report</i>System Status
                    </a>
                    <a href="add_sample_data.php" class="btn btn-material purple waves-effect waves-light" style="margin: 8px;">
                        <i class="material-icons left">data_usage</i>Sample Data
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer id="contact" class="page-footer">
        <div class="container">
            <div class="row">
                <div class="col l6 s12">
                    <h5 class="white-text">HealthSure</h5>
                    <p class="grey-text text-lighten-4">
                        Complete Health Insurance Management System designed to streamline policy management, 
                        claims processing, and customer service for insurance providers and their clients.
                    </p>
                    <div style="margin-top: 20px;">
                        <a href="#" class="btn-floating blue"><i class="material-icons">facebook</i></a>
                        <a href="#" class="btn-floating light-blue" style="margin-left: 8px;"><i class="material-icons">alternate_email</i></a>
                        <a href="#" class="btn-floating blue darken-3" style="margin-left: 8px;"><i class="material-icons">business</i></a>
                        <a href="#" class="btn-floating red" style="margin-left: 8px;"><i class="material-icons">email</i></a>
                    </div>
                </div>
                <div class="col l3 s12">
                    <h5 class="white-text">Quick Links</h5>
                    <ul class="footer-links">
                        <li><a href="#portals">Portal Access</a></li>
                        <li><a href="#features">Features</a></li>
                        <li><a href="user-guide.php">User Guide</a></li>
                        <li><a href="setup.php">System Setup</a></li>
                    </ul>
                </div>
                <div class="col l3 s12">
                    <h5 class="white-text">Support</h5>
                    <ul class="footer-links">
                        <li><a href="auth/login.php">Customer Login</a></li>
                        <li><a href="auth/register.php">Registration</a></li>
                        <li><a href="user-guide.php">Help Center</a></li>
                        <li><a href="#contact">Contact Us</a></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="footer-copyright">
            <div class="container">
                © 2024 HealthSure Insurance Management System. All rights reserved.
                <span class="grey-text text-lighten-4 right">Built with PHP, MySQL & Material Design</span>
            </div>
        </div>
    </footer>

    <!-- Materialize JavaScript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
    
    <script>
        // Initialize Materialize components
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize sidenav
            var sidenavs = document.querySelectorAll('.sidenav');
            M.Sidenav.init(sidenavs);
            
            // Smooth scrolling for anchor links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        const headerHeight = 64; // Material navbar height
                        const targetPosition = target.offsetTop - headerHeight;
                        
                        window.scrollTo({
                            top: targetPosition,
                            behavior: 'smooth'
                        });
                    }
                });
            });
            
            // Intersection Observer for animations
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };
            
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, observerOptions);
            
            // Observe fade-in elements
            document.querySelectorAll('.fade-in-up').forEach(el => {
                observer.observe(el);
            });
        });
    </script>
</body>
</html>
