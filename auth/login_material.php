<?php
session_start();

// Page configuration
$page_title = 'Login - HealthSure';
$page_header = 'Welcome Back';
$page_description = 'Sign in to your HealthSure account';
$base_url = '../';

$nav_items = [
    ['url' => '../landing_material.php', 'text' => 'Home', 'icon' => 'home'],
    ['url' => '../user-guide.php', 'text' => 'Guide', 'icon' => 'help'],
    ['url' => 'register_material.php', 'text' => 'Register', 'icon' => 'person_add', 'class' => 'btn btn-material white blue-text']
];

$additional_css = '
    .login-container {
        max-width: 500px;
        margin: 40px auto;
    }
    
    .login-card {
        border-radius: 16px;
        overflow: hidden;
    }
    
    .login-header {
        background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
        color: white;
        padding: 40px 32px 32px;
        text-align: center;
    }
    
    .login-icon {
        width: 80px;
        height: 80px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
        font-size: 2.5rem;
    }
    
    .role-hint {
        background: rgba(255, 255, 255, 0.1);
        padding: 12px 20px;
        border-radius: 20px;
        display: inline-block;
        margin-top: 16px;
        font-size: 0.9rem;
    }
';

// Handle form submission (simplified for demo)
$error = '';
$role_hint = $_GET['role'] ?? null;

if ($_POST) {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields';
    } else {
        // Here you would normally validate against database
        // For demo, just redirect to dashboard
        header('Location: ../landing_material.php');
        exit();
    }
}

// Start output buffering for content
ob_start();
?>

<div class="login-container">
    <div class="card login-card">
        <div class="login-header">
            <div class="login-icon">
                <i class="material-icons">
                    <?php 
                    switch($role_hint) {
                        case 'admin': echo 'admin_panel_settings'; break;
                        case 'agent': echo 'support_agent'; break;
                        case 'customer': echo 'person'; break;
                        default: echo 'login'; break;
                    }
                    ?>
                </i>
            </div>
            <h4 style="margin: 0; font-weight: 300;">
                <?php 
                switch($role_hint) {
                    case 'admin': echo 'Admin Login'; break;
                    case 'agent': echo 'Agent Login'; break;
                    case 'customer': echo 'Customer Login'; break;
                    default: echo 'Sign In'; break;
                }
                ?>
            </h4>
            <?php if ($role_hint): ?>
                <div class="role-hint">
                    <?php echo ucfirst($role_hint); ?> Portal Access
                </div>
            <?php endif; ?>
        </div>
        
        <div class="card-content" style="padding: 32px;">
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="material-icons">error</i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="input-field">
                    <i class="material-icons prefix">email</i>
                    <input id="email" name="email" type="email" class="validate" required 
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                    <label for="email">Email Address</label>
                    <span class="helper-text" data-error="Please enter a valid email" data-success="Valid email"></span>
                </div>
                
                <div class="input-field">
                    <i class="material-icons prefix">lock</i>
                    <input id="password" name="password" type="password" class="validate" required>
                    <label for="password">Password</label>
                </div>
                
                <div style="margin: 32px 0 16px;">
                    <button type="submit" class="btn-large btn-material waves-effect waves-light blue" style="width: 100%;">
                        <i class="material-icons left">login</i>
                        Sign In
                    </button>
                </div>
                
                <div class="center-align">
                    <a href="#" class="blue-text">Forgot Password?</a>
                </div>
            </form>
        </div>
        
        <div class="card-action center-align" style="background: #fafafa;">
            <p class="grey-text">Don't have an account?</p>
            <a href="register_material.php" class="btn btn-material blue lighten-1 waves-effect waves-light">
                <i class="material-icons left">person_add</i>
                Create Account
            </a>
        </div>
    </div>
    
    <?php if ($role_hint === 'admin'): ?>
        <div class="card" style="margin-top: 20px;">
            <div class="card-content center-align">
                <h6 class="blue-text">Demo Credentials</h6>
                <p class="grey-text">
                    <strong>Email:</strong> admin@healthsure.com<br>
                    <strong>Password:</strong> password
                </p>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();

// Include the template
include '../templates/material_template.php';
?>
