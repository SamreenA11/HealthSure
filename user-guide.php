<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Guide - HealthSure</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/animations.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container" style="margin-top: 2rem; margin-bottom: 2rem;">
        <div class="text-center mb-5">
            <h1>HealthSure User Guide</h1>
            <p class="text-light">Complete guide on how to use the HealthSure Insurance Management System</p>
        </div>
        
        <!-- Quick Navigation -->
        <div class="card mb-4">
            <div class="card-header">
                <h3>Quick Navigation</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-4">
                        <h5>🔐 Getting Started</h5>
                        <ul>
                            <li><a href="#setup">System Setup</a></li>
                            <li><a href="#login">Login Credentials</a></li>
                            <li><a href="#roles">User Roles</a></li>
                        </ul>
                    </div>
                    <div class="col-4">
                        <h5>👤 For Customers</h5>
                        <ul>
                            <li><a href="#customer-register">Registration</a></li>
                            <li><a href="#customer-policies">Browse & Buy Policies</a></li>
                            <li><a href="#customer-claims">File Claims</a></li>
                        </ul>
                    </div>
                    <div class="col-4">
                        <h5>🤝 For Agents</h5>
                        <ul>
                            <li><a href="#agent-access">Getting Access</a></li>
                            <li><a href="#agent-customers">Manage Customers</a></li>
                            <li><a href="#agent-performance">Track Performance</a></li>
                        </ul>
                    </div>
                </div>
                <div class="text-center mt-3">
                    <a href="landing.php" class="btn btn-primary">Go to Home Page</a>
                    <a href="auth/login.php" class="btn btn-outline">Direct Login</a>
                    <a href="debug.php" class="btn btn-outline">System Debug</a>
                </div>
            </div>
        </div>
        
        <!-- System Setup -->
        <div id="setup" class="card mb-4">
            <div class="card-header">
                <h3>🔧 System Setup</h3>
            </div>
            <div class="card-body">
                <h5>Step 1: Initialize Database</h5>
                <ol>
                    <li>Make sure XAMPP is running (Apache + MySQL)</li>
                    <li>Go to: <code>http://localhost/HealthSure/setup.php</code></li>
                    <li>Click "Retry Setup" if needed</li>
                    <li>Wait for "Setup Completed Successfully!" message</li>
                </ol>
                
                <h5>Step 2: Add Sample Data (Optional)</h5>
                <ol>
                    <li>Go to: <code>http://localhost/HealthSure/add_sample_data.php</code></li>
                    <li>This creates test customers, policies, claims, and payments</li>
                </ol>
                
                <div class="alert alert-info">
                    <strong>Troubleshooting:</strong> If you encounter issues, use <code>debug.php</code> to check system status.
                </div>
            </div>
        </div>
        
        <!-- Login Credentials -->
        <div id="login" class="card mb-4">
            <div class="card-header">
                <h3>🔑 Login Credentials</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-4">
                        <div class="alert alert-primary">
                            <h6>👨‍💼 Admin Access</h6>
                            <strong>Email:</strong> admin@healthsure.com<br>
                            <strong>Password:</strong> password<br>
                            <small>Full system control</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="alert alert-success">
                            <h6>👤 Customer Access</h6>
                            <strong>Registration:</strong> Self-register<br>
                            <strong>Or use:</strong> customer@test.com / password<br>
                            <small>(If sample data was added)</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="alert alert-warning">
                            <h6>🤝 Agent Access</h6>
                            <strong>Creation:</strong> Admin creates agents<br>
                            <strong>Login:</strong> Use provided credentials<br>
                            <small>Cannot self-register</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- User Roles -->
        <div id="roles" class="card mb-4">
            <div class="card-header">
                <h3>👥 User Roles & Permissions</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-4">
                        <h6>👨‍💼 Admin</h6>
                        <ul style="font-size: 0.875rem;">
                            <li>Manage all policies</li>
                            <li>Create/manage agents</li>
                            <li>View all customers</li>
                            <li>Process claims</li>
                            <li>Generate reports</li>
                            <li>System settings</li>
                        </ul>
                    </div>
                    <div class="col-4">
                        <h6>🤝 Agent</h6>
                        <ul style="font-size: 0.875rem;">
                            <li>View assigned customers</li>
                            <li>Help customers with policies</li>
                            <li>Assist with claims</li>
                            <li>Track performance</li>
                            <li>Limited system access</li>
                        </ul>
                    </div>
                    <div class="col-4">
                        <h6>👤 Customer</h6>
                        <ul style="font-size: 0.875rem;">
                            <li>Browse policies</li>
                            <li>Apply for insurance</li>
                            <li>File claims</li>
                            <li>Make payments</li>
                            <li>View policy history</li>
                            <li>Contact support</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Customer Guide -->
        <div id="customer-register" class="card mb-4">
            <div class="card-header">
                <h3>👤 Customer Guide</h3>
            </div>
            <div class="card-body">
                <h5>Step 1: Registration</h5>
                <ol>
                    <li>Go to: <code>http://localhost/HealthSure/auth/register.php</code></li>
                    <li>Fill in your personal details</li>
                    <li>Use a valid email and strong password</li>
                    <li>Click "Create Account"</li>
                </ol>
                
                <h5 id="customer-policies">Step 2: Browse & Apply for Policies</h5>
                <ol>
                    <li>Login and go to "Browse Policies"</li>
                    <li>Filter by policy type (Health, Life, Family)</li>
                    <li>Click "Apply Now" on desired policy</li>
                    <li>Fill application form with accurate details</li>
                    <li>For Life Insurance, add nominee information</li>
                    <li>Accept terms and submit</li>
                </ol>
                
                <h5 id="customer-claims">Step 3: File Claims</h5>
                <ol>
                    <li>Go to "File New Claim" from dashboard</li>
                    <li>Select the policy to claim against</li>
                    <li>Enter claim amount (within coverage limit)</li>
                    <li>Provide detailed incident description</li>
                    <li>List supporting documents</li>
                    <li>Submit for admin review</li>
                </ol>
                
                <h5>Step 4: Track Your Insurance</h5>
                <ul>
                    <li><strong>My Policies:</strong> View all your active/expired policies</li>
                    <li><strong>My Claims:</strong> Track claim status and history</li>
                    <li><strong>Payment History:</strong> View all premium payments</li>
                    <li><strong>Make Payment:</strong> Pay policy premiums</li>
                </ul>
            </div>
        </div>
        
        <!-- Agent Guide -->
        <div id="agent-access" class="card mb-4">
            <div class="card-header">
                <h3>🤝 Agent Guide</h3>
            </div>
            <div class="card-body">
                <h5>Step 1: Getting Agent Access</h5>
                <ol>
                    <li>Contact admin to create your agent account</li>
                    <li>Admin goes to "Agent Management" → "Add New Agent"</li>
                    <li>Admin provides your details and creates login</li>
                    <li>You receive email and password from admin</li>
                    <li>Login at: <code>http://localhost/HealthSure/auth/login.php</code></li>
                </ol>
                
                <h5 id="agent-customers">Step 2: Manage Customers</h5>
                <ul>
                    <li><strong>View Customers:</strong> See customers assigned to you</li>
                    <li><strong>Help with Policies:</strong> Guide customers through policy selection</li>
                    <li><strong>Assist Claims:</strong> Help customers file and track claims</li>
                    <li><strong>Customer Support:</strong> Answer policy-related questions</li>
                </ul>
                
                <h5 id="agent-performance">Step 3: Track Performance</h5>
                <ul>
                    <li><strong>Dashboard Stats:</strong> View your sales metrics</li>
                    <li><strong>Customer Count:</strong> Total customers assigned</li>
                    <li><strong>Policy Sales:</strong> Number of active policies sold</li>
                    <li><strong>Premium Collection:</strong> Total premiums from your customers</li>
                </ul>
                
                <div class="alert alert-info">
                    <strong>Note:</strong> Agents have limited access and can only view customers assigned to them by the admin.
                </div>
            </div>
        </div>
        
        <!-- Common Tasks -->
        <div class="card mb-4">
            <div class="card-header">
                <h3>📋 Common Tasks</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6">
                        <h6>🔄 For All Users</h6>
                        <ul>
                            <li><strong>Login:</strong> <code>/auth/login.php</code></li>
                            <li><strong>Logout:</strong> Click logout in sidebar</li>
                            <li><strong>Profile:</strong> Update personal information</li>
                            <li><strong>Password:</strong> Contact admin for reset</li>
                        </ul>
                        
                        <h6>🛠️ Troubleshooting</h6>
                        <ul>
                            <li><strong>Can't login:</strong> Use <code>reset_admin_password.php</code></li>
                            <li><strong>Database error:</strong> Run <code>setup.php</code> again</li>
                            <li><strong>No data:</strong> Use <code>add_sample_data.php</code></li>
                            <li><strong>System check:</strong> Use <code>debug.php</code></li>
                        </ul>
                    </div>
                    <div class="col-6">
                        <h6>📊 Available Policy Types</h6>
                        <ul>
                            <li><strong>Health Insurance:</strong> Medical coverage, cashless treatment</li>
                            <li><strong>Life Insurance:</strong> Term life with nominee benefits</li>
                            <li><strong>Family Insurance:</strong> Coverage for dependents</li>
                        </ul>
                        
                        <h6>💳 Payment Methods</h6>
                        <ul>
                            <li>Cash payments</li>
                            <li>Credit/Debit cards</li>
                            <li>Bank transfers</li>
                            <li>Online payments</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- URLs Reference -->
        <div class="card mb-4">
            <div class="card-header">
                <h3>🔗 Important URLs</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-4">
                        <h6>🔧 Setup & Debug</h6>
                        <ul style="font-size: 0.875rem;">
                            <li><code>/setup.php</code> - Database setup</li>
                            <li><code>/debug.php</code> - System diagnostics</li>
                            <li><code>/add_sample_data.php</code> - Test data</li>
                            <li><code>/reset_admin_password.php</code> - Password reset</li>
                        </ul>
                    </div>
                    <div class="col-4">
                        <h6>🔐 Authentication</h6>
                        <ul style="font-size: 0.875rem;">
                            <li><code>/auth/login.php</code> - Login page</li>
                            <li><code>/auth/register.php</code> - Customer registration</li>
                            <li><code>/auth/logout.php</code> - Logout</li>
                            <li><code>/index.php</code> - Home (redirects by role)</li>
                        </ul>
                    </div>
                    <div class="col-4">
                        <h6>👨‍💼 Admin Panel</h6>
                        <ul style="font-size: 0.875rem;">
                            <li><code>/admin/dashboard.php</code> - Admin dashboard</li>
                            <li><code>/admin/policies.php</code> - Policy management</li>
                            <li><code>/admin/customers.php</code> - Customer management</li>
                            <li><code>/admin/agents.php</code> - Agent management</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="text-center">
            <a href="landing.php" class="btn btn-primary btn-lg">Start Using HealthSure</a>
        </div>
    </div>
    <script src="assets/js/main.js"></script>
</body>
</html>
