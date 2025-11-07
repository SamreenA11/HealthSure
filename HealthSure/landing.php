<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HealthSure - Insurance Management System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/animations.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Landing Page Specific Styles */
        body {
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }
        
        .hero-section {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 4rem 0;
            text-align: center;
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
        
        .role-card {
            background: white;
            padding: 2rem;
            border-radius: 1rem;
            box-shadow: var(--shadow);
            text-align: center;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 2px solid transparent;
            position: relative;
            overflow: hidden;
            height: 100%;
            min-height: 500px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        
        .role-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
            transition: left 0.6s;
        }
        
        .role-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15);
            border-color: var(--primary-color);
        }
        
        .role-card:hover::before {
            left: 100%;
        }
        
        .role-icon {
            width: 80px;
            height: 80px;
            background: var(--light-bg);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin: 0 auto 1rem;
            transition: all 0.3s ease;
        }
        
        .role-card:hover .role-icon {
            transform: scale(1.1) rotate(5deg);
        }
        
        .admin-theme { 
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            box-shadow: 0 4px 15px rgba(220, 38, 38, 0.3);
        }
        .agent-theme { 
            background: linear-gradient(135deg, #d97706, #b45309);
            box-shadow: 0 4px 15px rgba(217, 119, 6, 0.3);
        }
        .customer-theme { 
            background: linear-gradient(135deg, #059669, #047857);
            box-shadow: 0 4px 15px rgba(5, 150, 105, 0.3);
        }
        
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
            margin: 3rem 0;
        }
        
        .feature-card {
            background: white;
            padding: 2rem;
            border-radius: 0.75rem;
            box-shadow: var(--shadow);
            text-align: center;
            transition: all 0.3s ease;
            border: 1px solid var(--border-color);
        }
        
        .feature-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
            border-color: var(--primary-color);
        }
        
        .feature-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            display: block;
        }
        
        /* Button Fixes */
        .role-card .btn {
            white-space: normal;
            overflow: visible;
            text-overflow: clip;
            border-radius: 0.5rem;
            font-size: 0.95rem;
            line-height: 1.4;
            min-height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            word-wrap: break-word;
            padding: 0.75rem 0.5rem !important;
        }
        
        .role-card .btn-success {
            background: var(--success-color);
            border-color: var(--success-color);
            color: white;
        }
        
        .role-card .btn-outline {
            background: transparent;
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
        }
        
        .role-card .btn-outline:hover {
            background: var(--primary-color);
            color: white;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .hero-section {
                padding: 2rem 0;
            }
            
            .hero-section h1 {
                font-size: 2rem !important;
            }
            
            .role-card {
                padding: 1.5rem;
                margin-bottom: 2rem;
                min-height: 450px;
            }
            
            .col-3 {
                flex: 0 0 100%;
                max-width: 100%;
            }
            
            .features-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }
            
            .role-card .btn {
                font-size: 0.9rem;
                padding: 0.6rem 0.4rem !important;
                text-align: center !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
            }
        }
        
        @media (max-width: 480px) {
            .container {
                padding: 0 1rem;
            }
            
            .role-card {
                padding: 1rem;
                min-height: 400px;
            }
            
            .role-icon {
                width: 60px;
                height: 60px;
                font-size: 1.5rem;
            }
            
            .role-card .btn {
                font-size: 0.85rem;
                padding: 0.5rem 0.3rem !important;
                min-height: 40px;
                text-align: center !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
            }
            
            .role-card ul {
                font-size: 0.8rem !important;
                margin-bottom: 1.5rem !important;
            }
        }
        
        /* Loading Animation */
        .fade-in {
            opacity: 0;
            transform: translateY(30px);
            animation: fadeInUp 0.8s ease-out forwards;
        }
        
        .fade-in:nth-child(1) { animation-delay: 0.1s; }
        .fade-in:nth-child(2) { animation-delay: 0.2s; }
        .fade-in:nth-child(3) { animation-delay: 0.3s; }
        
        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Button Enhancements */
        .btn {
            position: relative;
            overflow: hidden;
            font-weight: 600;
            letter-spacing: 0.025em;
        }
        
        .btn::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }
        
        .btn:active::after {
            width: 300px;
            height: 300px;
        }
    </style>
</head>
<body>
    <!-- Hero Section -->
    <div class="hero-section">
        <div class="container hero-content">
            <h1 style="font-size: 3rem; margin-bottom: 1rem;">HealthSure</h1>
            <p style="font-size: 1.25rem; margin-bottom: 2rem; opacity: 0.9;">
                Complete Health Insurance Management System
            </p>
            <p style="font-size: 1rem; opacity: 0.8; max-width: 600px; margin: 0 auto 2rem;">
                Manage insurance policies, process claims, and track payments with our comprehensive digital platform designed for customers, agents, and administrators.
            </p>
            <div style="margin-top: 2rem;">
                <a href="#portals" class="btn btn-outline" style="background: rgba(255,255,255,0.1); border: 2px solid rgba(255,255,255,0.3); color: white; margin-right: 1rem;">
                    Get Started
                </a>
                <a href="user-guide.php" class="btn btn-outline" style="background: transparent; border: 2px solid rgba(255,255,255,0.3); color: white;">
                    Learn More
                </a>
            </div>
        </div>
    </div>

    <!-- Role Selection Section -->
    <div id="portals" style="padding: 4rem 0; background: var(--light-bg);">
        <div class="container">
            <div class="text-center mb-5">
                <h2>Choose Your Portal</h2>
                <p class="text-light">Select your role to access the appropriate dashboard</p>
            </div>
            
            <div class="row" style="gap: 2rem; justify-content: center;">
                <!-- Admin Portal -->
                <div class="col-3 fade-in">
                    <div class="role-card">
                        <div class="role-icon admin-theme" style="color: white;">
                            👨‍💼
                        </div>
                        <h3>Admin Portal</h3>
                        <p class="text-light mb-4">Complete system management and control</p>
                        <ul style="text-align: left; font-size: 0.875rem; color: var(--text-light); margin-bottom: 2rem;">
                            <li>Manage all policies</li>
                            <li>Create & manage agents</li>
                            <li>Process claims</li>
                            <li>Generate reports</li>
                            <li>System settings</li>
                        </ul>
                        <div style="margin-top: auto;">
                            <a href="auth/login.php?role=admin" class="btn btn-danger" style="width: 100%; padding: 0.75rem 0.5rem; font-weight: 600; text-align: center; display: flex; align-items: center; justify-content: center; min-height: 48px;">
                                Admin Login
                            </a>
                        </div>
                        <div class="mt-2" style="font-size: 0.75rem; color: var(--text-light);">
                            <strong>Default:</strong> admin@healthsure.com / password
                        </div>
                    </div>
                </div>

                <!-- Agent Portal -->
                <div class="col-3 fade-in">
                    <div class="role-card">
                        <div class="role-icon agent-theme" style="color: white;">
                            🤝
                        </div>
                        <h3>Agent Portal</h3>
                        <p class="text-light mb-4">Customer management and sales tracking</p>
                        <ul style="text-align: left; font-size: 0.875rem; color: var(--text-light); margin-bottom: 2rem;">
                            <li>Manage assigned customers</li>
                            <li>Assist with policy applications</li>
                            <li>Help with claims</li>
                            <li>Track performance</li>
                            <li>Customer support</li>
                        </ul>
                        <div style="margin-top: auto;">
                            <a href="auth/login.php?role=agent" class="btn btn-warning" style="width: 100%; padding: 0.75rem 0.5rem; font-weight: 600; text-align: center; display: flex; align-items: center; justify-content: center; min-height: 48px;">
                                Agent Login
                            </a>
                        </div>
                        <div class="mt-2" style="font-size: 0.75rem; color: var(--text-light);">
                            <strong>Note:</strong> Admin creates agent accounts
                        </div>
                    </div>
                </div>

                <!-- Customer Portal -->
                <div class="col-3 fade-in">
                    <div class="role-card">
                        <div class="role-icon customer-theme" style="color: white;">
                            👤
                        </div>
                        <h3>Customer Portal</h3>
                        <p class="text-light mb-4">Insurance policies and claims management</p>
                        <ul style="text-align: left; font-size: 0.875rem; color: var(--text-light); margin-bottom: 2rem;">
                            <li>Browse & buy policies</li>
                            <li>File insurance claims</li>
                            <li>Make premium payments</li>
                            <li>Track policy status</li>
                            <li>Download documents</li>
                        </ul>
                        <div class="d-flex flex-column" style="gap: 0.75rem; margin-top: auto;">
                            <a href="auth/login.php?role=customer" class="btn btn-success" style="width: 100%; padding: 0.75rem 0.5rem; font-weight: 600; text-align: center; display: flex; align-items: center; justify-content: center; min-height: 48px;">
                                Customer Login
                            </a>
                            <a href="auth/register.php" class="btn btn-outline" style="width: 100%; padding: 0.75rem 0.5rem; font-weight: 600; text-align: center; display: flex; align-items: center; justify-content: center; min-height: 48px;">
                                New Customer? Register
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Features Section -->
    <div style="padding: 4rem 0;">
        <div class="container">
            <div class="text-center mb-5">
                <h2>System Features</h2>
                <p class="text-light">Comprehensive insurance management capabilities</p>
            </div>
            
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">📋</div>
                    <h5>Policy Management</h5>
                    <p style="font-size: 0.875rem; color: var(--text-light);">
                        Create and manage Health, Life, and Family insurance policies with specialized features and automated workflows.
                    </p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">📄</div>
                    <h5>Claims Processing</h5>
                    <p style="font-size: 0.875rem; color: var(--text-light);">
                        Streamlined claim filing, review, and approval process with document management and real-time tracking.
                    </p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">💳</div>
                    <h5>Payment Tracking</h5>
                    <p style="font-size: 0.875rem; color: var(--text-light);">
                        Complete payment management for premiums and claim settlements with multiple payment methods.
                    </p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">📊</div>
                    <h5>Analytics & Reports</h5>
                    <p style="font-size: 0.875rem; color: var(--text-light);">
                        Comprehensive reporting and analytics for business insights with customizable dashboards.
                    </p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">🔒</div>
                    <h5>Secure & Reliable</h5>
                    <p style="font-size: 0.875rem; color: var(--text-light);">
                        Role-based access control with secure data handling, encryption, and audit trails.
                    </p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">📱</div>
                    <h5>Responsive Design</h5>
                    <p style="font-size: 0.875rem; color: var(--text-light);">
                        Modern, mobile-friendly interface that works seamlessly on all devices and screen sizes.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Links Section -->
    <div style="padding: 3rem 0; background: var(--light-bg);">
        <div class="container">
            <div class="text-center">
                <h3>Quick Links</h3>
                <div class="d-flex justify-content-center" style="gap: 1rem; margin-top: 2rem; flex-wrap: wrap;">
                    <a href="user-guide.php" class="btn btn-outline">📖 User Guide</a>
                    <a href="setup.php" class="btn btn-outline">⚙️ System Setup</a>
                    <a href="debug.php" class="btn btn-outline">🔧 System Status</a>
                    <a href="add_sample_data.php" class="btn btn-outline">📊 Add Sample Data</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div style="padding: 2rem 0; background: var(--text-dark); color: white; text-align: center;">
        <div class="container">
            <p style="margin: 0; opacity: 0.8;">
                HealthSure Insurance Management System &copy; 2024 | 
                Built with PHP, MySQL & Modern Web Technologies
            </p>
        </div>
    </div>

    <script>
        // Enhanced interactive effects and animations
        document.addEventListener('DOMContentLoaded', function() {
            // Smooth scrolling for anchor links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });
            
            // Intersection Observer for fade-in animations
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
            
            // Observe all fade-in elements
            document.querySelectorAll('.fade-in, .feature-card').forEach(el => {
                observer.observe(el);
            });
            
            // Enhanced role card interactions
            const roleCards = document.querySelectorAll('.role-card');
            roleCards.forEach((card, index) => {
                // Add staggered animation delay
                card.style.animationDelay = `${index * 0.1}s`;
                
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-8px) scale(1.02)';
                    this.style.boxShadow = '0 25px 50px -12px rgba(0, 0, 0, 0.15)';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0) scale(1)';
                    this.style.boxShadow = '';
                });
                
                // Add click animation
                card.addEventListener('mousedown', function() {
                    this.style.transform = 'translateY(-6px) scale(0.98)';
                });
                
                card.addEventListener('mouseup', function() {
                    this.style.transform = 'translateY(-8px) scale(1.02)';
                });
            });
            
            // Feature cards hover effects
            document.querySelectorAll('.feature-card').forEach(card => {
                card.addEventListener('mouseenter', function() {
                    const icon = this.querySelector('.feature-icon');
                    if (icon) {
                        icon.style.transform = 'scale(1.2) rotate(5deg)';
                    }
                });
                
                card.addEventListener('mouseleave', function() {
                    const icon = this.querySelector('.feature-icon');
                    if (icon) {
                        icon.style.transform = 'scale(1) rotate(0deg)';
                    }
                });
            });
            
            // Add loading animation to buttons
            document.querySelectorAll('.btn').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    // Don't prevent default for actual navigation
                    const ripple = document.createElement('span');
                    ripple.style.position = 'absolute';
                    ripple.style.borderRadius = '50%';
                    ripple.style.background = 'rgba(255, 255, 255, 0.6)';
                    ripple.style.transform = 'scale(0)';
                    ripple.style.animation = 'ripple 0.6s linear';
                    ripple.style.left = (e.clientX - e.target.offsetLeft) + 'px';
                    ripple.style.top = (e.clientY - e.target.offsetTop) + 'px';
                    
                    this.appendChild(ripple);
                    
                    setTimeout(() => {
                        ripple.remove();
                    }, 600);
                });
            });
            
            // Parallax effect for hero section
            window.addEventListener('scroll', function() {
                const scrolled = window.pageYOffset;
                const heroSection = document.querySelector('.hero-section');
                if (heroSection) {
                    heroSection.style.transform = `translateY(${scrolled * 0.5}px)`;
                }
            });
        });
        
        // Add CSS for ripple animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes ripple {
                to {
                    transform: scale(4);
                    opacity: 0;
                }
            }
            
            .feature-icon {
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            }
        `;
        document.head.appendChild(style);
    </script>
    <script src="assets/js/main.js"></script>
</body>
</html>
