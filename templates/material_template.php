<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'HealthSure'; ?></title>
    
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
            display: flex;
            min-height: 100vh;
            flex-direction: column;
        }
        
        main {
            flex: 1 0 auto;
        }
        
        /* Custom Material Navbar */
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
        
        /* Material Cards */
        .card {
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        }
        
        .card:hover {
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
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
        
        /* Form Styles */
        .input-field input:focus + label,
        .input-field input.valid + label,
        .input-field input.invalid + label {
            color: var(--primary-color);
        }
        
        .input-field input:focus {
            border-bottom: 1px solid var(--primary-color);
            box-shadow: 0 1px 0 0 var(--primary-color);
        }
        
        /* Alert Styles */
        .alert {
            padding: 16px;
            border-radius: 8px;
            margin: 16px 0;
            display: flex;
            align-items: center;
        }
        
        .alert i {
            margin-right: 12px;
        }
        
        .alert-success {
            background: #e8f5e8;
            color: #2e7d32;
            border-left: 4px solid #4caf50;
        }
        
        .alert-error {
            background: #ffebee;
            color: #c62828;
            border-left: 4px solid #f44336;
        }
        
        .alert-warning {
            background: #fff8e1;
            color: #ef6c00;
            border-left: 4px solid #ff9800;
        }
        
        .alert-info {
            background: #e3f2fd;
            color: #1565c0;
            border-left: 4px solid #2196f3;
        }
        
        /* Footer */
        .page-footer {
            background: linear-gradient(135deg, #263238, #37474f);
        }
        
        /* Responsive adjustments */
        @media only screen and (max-width: 992px) {
            .container {
                width: 95%;
            }
        }
    </style>
    
    <?php if (isset($additional_css)): ?>
        <style><?php echo $additional_css; ?></style>
    <?php endif; ?>
</head>
<body>
    <!-- Navigation -->
    <div class="navbar-fixed">
        <nav>
            <div class="nav-wrapper container">
                <a href="<?php echo $base_url ?? '../'; ?>landing_material.php" class="brand-logo">
                    <div class="brand-icon">
                        <i class="material-icons">local_hospital</i>
                    </div>
                    HealthSure
                </a>
                
                <?php if (isset($nav_items)): ?>
                    <ul class="right hide-on-med-and-down">
                        <?php foreach ($nav_items as $item): ?>
                            <li>
                                <a href="<?php echo $item['url']; ?>" 
                                   class="<?php echo $item['class'] ?? ''; ?>">
                                    <?php if (isset($item['icon'])): ?>
                                        <i class="material-icons left"><?php echo $item['icon']; ?></i>
                                    <?php endif; ?>
                                    <?php echo $item['text']; ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
                
                <a href="#" data-target="mobile-nav" class="sidenav-trigger">
                    <i class="material-icons">menu</i>
                </a>
            </div>
        </nav>
    </div>

    <!-- Mobile Navigation -->
    <ul class="sidenav" id="mobile-nav">
        <?php if (isset($nav_items)): ?>
            <?php foreach ($nav_items as $item): ?>
                <li>
                    <a href="<?php echo $item['url']; ?>">
                        <?php if (isset($item['icon'])): ?>
                            <i class="material-icons"><?php echo $item['icon']; ?></i>
                        <?php endif; ?>
                        <?php echo $item['text']; ?>
                    </a>
                </li>
            <?php endforeach; ?>
        <?php endif; ?>
    </ul>

    <!-- Main Content -->
    <main>
        <?php if (isset($page_header)): ?>
            <div class="section" style="padding: 40px 0 20px;">
                <div class="container">
                    <h1 class="blue-text text-darken-2"><?php echo $page_header; ?></h1>
                    <?php if (isset($page_description)): ?>
                        <p class="flow-text grey-text text-darken-1"><?php echo $page_description; ?></p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
        
        <div class="container">
            <?php
            // Include the main content
            if (isset($content_file) && file_exists($content_file)) {
                include $content_file;
            } else {
                echo $content ?? '<p>Content not found.</p>';
            }
            ?>
        </div>
    </main>

    <!-- Footer -->
    <footer class="page-footer">
        <div class="container">
            <div class="row">
                <div class="col l6 s12">
                    <h5 class="white-text">HealthSure</h5>
                    <p class="grey-text text-lighten-4">
                        Complete Health Insurance Management System
                    </p>
                </div>
                <div class="col l4 offset-l2 s12">
                    <h5 class="white-text">Quick Links</h5>
                    <ul>
                        <li><a class="grey-text text-lighten-3" href="<?php echo $base_url ?? '../'; ?>landing_material.php">Home</a></li>
                        <li><a class="grey-text text-lighten-3" href="<?php echo $base_url ?? '../'; ?>user-guide.php">User Guide</a></li>
                        <li><a class="grey-text text-lighten-3" href="#contact">Contact</a></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="footer-copyright">
            <div class="container">
                © 2024 HealthSure Insurance Management System
                <a class="grey-text text-lighten-4 right" href="#!">Built with Material Design</a>
            </div>
        </div>
    </footer>

    <!-- Materialize JavaScript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
    
    <script>
        // Initialize Materialize components
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize all components
            M.AutoInit();
            
            // Custom initializations
            var sidenavs = document.querySelectorAll('.sidenav');
            M.Sidenav.init(sidenavs);
            
            var dropdowns = document.querySelectorAll('.dropdown-trigger');
            M.Dropdown.init(dropdowns);
            
            var modals = document.querySelectorAll('.modal');
            M.Modal.init(modals);
            
            var tooltips = document.querySelectorAll('.tooltipped');
            M.Tooltip.init(tooltips);
            
            var collapsibles = document.querySelectorAll('.collapsible');
            M.Collapsible.init(collapsibles);
        });
    </script>
    
    <?php if (isset($additional_js)): ?>
        <script><?php echo $additional_js; ?></script>
    <?php endif; ?>
</body>
</html>
