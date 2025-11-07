<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'HealthSure'; ?></title>
    
    <!-- Local Library CSS -->
    <?php if (isset($library_name)): ?>
        <?php if ($library_name === 'mdb5'): ?>
            <link rel="stylesheet" href="<?php echo $base_url ?? ''; ?>assets/mdb5/css/mdb.min.css">
        <?php else: ?>
            <link rel="stylesheet" href="<?php echo $base_url ?? ''; ?>assets/css/<?php echo $library_name; ?>.min.css">
            <!-- Fallback to non-minified version -->
            <link rel="stylesheet" href="<?php echo $base_url ?? ''; ?>assets/css/<?php echo $library_name; ?>.css">
        <?php endif; ?>
    <?php endif; ?>
    
    <!-- Material Icons (if using Material Design) -->
    <?php if (isset($use_material_icons) && $use_material_icons): ?>
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <?php endif; ?>
    
    <!-- Font Awesome (if using Font Awesome) -->
    <?php if (isset($use_font_awesome) && $use_font_awesome): ?>
        <link rel="stylesheet" href="<?php echo $base_url ?? ''; ?>assets/css/font-awesome.min.css">
    <?php endif; ?>
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo $base_url ?? ''; ?>assets/css/style.css">
    
    <style>
        /* Custom overrides for your library */
        <?php if (isset($custom_css)): ?>
            <?php echo $custom_css; ?>
        <?php endif; ?>
    </style>
</head>
<body class="<?php echo $body_class ?? ''; ?>">
    
    <!-- Navigation (customize based on your library) -->
    <?php if (isset($show_nav) && $show_nav): ?>
        <nav class="<?php echo $nav_class ?? 'navbar'; ?>">
            <div class="<?php echo $container_class ?? 'container'; ?>">
                <a href="<?php echo $base_url ?? ''; ?>landing.php" class="<?php echo $brand_class ?? 'brand'; ?>">
                    <i class="<?php echo $brand_icon ?? 'icon-hospital'; ?>"></i>
                    HealthSure
                </a>
                
                <!-- Navigation items -->
                <?php if (isset($nav_items)): ?>
                    <ul class="<?php echo $nav_list_class ?? 'nav-list'; ?>">
                        <?php foreach ($nav_items as $item): ?>
                            <li class="<?php echo $nav_item_class ?? 'nav-item'; ?>">
                                <a href="<?php echo $item['url']; ?>" 
                                   class="<?php echo $nav_link_class ?? 'nav-link'; ?> <?php echo $item['class'] ?? ''; ?>">
                                    <?php if (isset($item['icon'])): ?>
                                        <i class="<?php echo $item['icon']; ?>"></i>
                                    <?php endif; ?>
                                    <?php echo $item['text']; ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
                
                <!-- Mobile menu toggle -->
                <button class="<?php echo $mobile_toggle_class ?? 'mobile-toggle'; ?>" data-target="mobile-nav">
                    <i class="<?php echo $menu_icon ?? 'icon-menu'; ?>"></i>
                </button>
            </div>
        </nav>
    <?php endif; ?>
    
    <!-- Main Content -->
    <main class="<?php echo $main_class ?? 'main-content'; ?>">
        <?php if (isset($page_header)): ?>
            <div class="<?php echo $header_section_class ?? 'page-header'; ?>">
                <div class="<?php echo $container_class ?? 'container'; ?>">
                    <h1 class="<?php echo $title_class ?? 'page-title'; ?>"><?php echo $page_header; ?></h1>
                    <?php if (isset($page_description)): ?>
                        <p class="<?php echo $description_class ?? 'page-description'; ?>"><?php echo $page_description; ?></p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
        
        <div class="<?php echo $container_class ?? 'container'; ?>">
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
    <?php if (isset($show_footer) && $show_footer): ?>
        <footer class="<?php echo $footer_class ?? 'footer'; ?>">
            <div class="<?php echo $container_class ?? 'container'; ?>">
                <div class="<?php echo $footer_content_class ?? 'footer-content'; ?>">
                    <div class="<?php echo $footer_section_class ?? 'footer-section'; ?>">
                        <h5>HealthSure</h5>
                        <p>Complete Health Insurance Management System</p>
                    </div>
                    <div class="<?php echo $footer_section_class ?? 'footer-section'; ?>">
                        <h5>Quick Links</h5>
                        <ul class="<?php echo $footer_links_class ?? 'footer-links'; ?>">
                            <li><a href="<?php echo $base_url ?? ''; ?>landing.php">Home</a></li>
                            <li><a href="<?php echo $base_url ?? ''; ?>user-guide.php">User Guide</a></li>
                            <li><a href="#contact">Contact</a></li>
                        </ul>
                    </div>
                </div>
                <div class="<?php echo $footer_bottom_class ?? 'footer-bottom'; ?>">
                    <p>&copy; 2024 HealthSure Insurance Management System</p>
                </div>
            </div>
        </footer>
    <?php endif; ?>
    
    <!-- Local Library JavaScript -->
    <?php if (isset($library_name)): ?>
        <?php if ($library_name === 'mdb5'): ?>
            <script src="<?php echo $base_url ?? ''; ?>assets/mdb5/js/mdb.umd.min.js"></script>
        <?php else: ?>
            <script src="<?php echo $base_url ?? ''; ?>assets/js/<?php echo $library_name; ?>.min.js"></script>
            <!-- Fallback to non-minified version -->
            <script src="<?php echo $base_url ?? ''; ?>assets/js/<?php echo $library_name; ?>.js"></script>
        <?php endif; ?>
    <?php endif; ?>
    
    <!-- Custom JavaScript -->
    <script src="<?php echo $base_url ?? ''; ?>assets/js/main.js"></script>
    
    <script>
        // Initialize your library components
        document.addEventListener('DOMContentLoaded', function() {
            <?php if (isset($library_init_js)): ?>
                <?php echo $library_init_js; ?>
            <?php endif; ?>
            
            <?php if (isset($custom_js)): ?>
                <?php echo $custom_js; ?>
            <?php endif; ?>
        });
    </script>
</body>
</html>
