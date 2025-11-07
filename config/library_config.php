<?php
// Library Configuration Helper
// This file helps configure different UI libraries

class LibraryConfig {
    
    // Bootstrap 5 Configuration
    public static function bootstrap5() {
        return [
            'library_name' => 'bootstrap',
            'use_font_awesome' => true,
            'container_class' => 'container',
            'nav_class' => 'navbar navbar-expand-lg navbar-dark bg-primary',
            'brand_class' => 'navbar-brand',
            'nav_list_class' => 'navbar-nav ms-auto',
            'nav_item_class' => 'nav-item',
            'nav_link_class' => 'nav-link',
            'mobile_toggle_class' => 'navbar-toggler',
            'main_class' => 'main-content',
            'header_section_class' => 'bg-light py-5',
            'title_class' => 'display-4 text-primary',
            'description_class' => 'lead text-muted',
            'footer_class' => 'bg-dark text-light py-4',
            'footer_content_class' => 'row',
            'footer_section_class' => 'col-md-6',
            'footer_links_class' => 'list-unstyled',
            'footer_bottom_class' => 'text-center border-top pt-3 mt-3',
            'body_class' => '',
            'brand_icon' => 'fas fa-hospital',
            'menu_icon' => 'fas fa-bars',
            'library_init_js' => '
                // Initialize Bootstrap components
                var tooltipTriggerList = [].slice.call(document.querySelectorAll(\'[data-bs-toggle="tooltip"]\'));
                var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });
            '
        ];
    }
    
    // Materialize CSS Configuration
    public static function materialize() {
        return [
            'library_name' => 'materialize',
            'use_material_icons' => true,
            'container_class' => 'container',
            'nav_class' => 'nav-wrapper',
            'brand_class' => 'brand-logo',
            'nav_list_class' => 'right hide-on-med-and-down',
            'nav_item_class' => '',
            'nav_link_class' => '',
            'mobile_toggle_class' => 'sidenav-trigger',
            'main_class' => 'main-content',
            'header_section_class' => 'section',
            'title_class' => 'blue-text text-darken-2',
            'description_class' => 'flow-text grey-text text-darken-1',
            'footer_class' => 'page-footer blue darken-2',
            'footer_content_class' => 'row',
            'footer_section_class' => 'col l6 s12',
            'footer_links_class' => '',
            'footer_bottom_class' => 'footer-copyright',
            'body_class' => '',
            'brand_icon' => 'material-icons',
            'menu_icon' => 'material-icons',
            'library_init_js' => '
                // Initialize Materialize components
                M.AutoInit();
                var sidenavs = document.querySelectorAll(\'.sidenav\');
                M.Sidenav.init(sidenavs);
            '
        ];
    }
    
    // Bulma CSS Configuration
    public static function bulma() {
        return [
            'library_name' => 'bulma',
            'use_font_awesome' => true,
            'container_class' => 'container',
            'nav_class' => 'navbar is-primary',
            'brand_class' => 'navbar-brand',
            'nav_list_class' => 'navbar-menu',
            'nav_item_class' => 'navbar-item',
            'nav_link_class' => 'navbar-item',
            'mobile_toggle_class' => 'navbar-burger',
            'main_class' => 'main-content',
            'header_section_class' => 'hero is-light',
            'title_class' => 'title is-2 has-text-primary',
            'description_class' => 'subtitle is-4 has-text-grey',
            'footer_class' => 'footer has-background-dark has-text-light',
            'footer_content_class' => 'columns',
            'footer_section_class' => 'column',
            'footer_links_class' => '',
            'footer_bottom_class' => 'has-text-centered',
            'body_class' => '',
            'brand_icon' => 'fas fa-hospital',
            'menu_icon' => 'fas fa-bars',
            'library_init_js' => '
                // Initialize Bulma mobile menu
                const burger = document.querySelector(\'.navbar-burger\');
                const menu = document.querySelector(\'.navbar-menu\');
                if (burger && menu) {
                    burger.addEventListener(\'click\', () => {
                        burger.classList.toggle(\'is-active\');
                        menu.classList.toggle(\'is-active\');
                    });
                }
            '
        ];
    }
    
    // Foundation CSS Configuration
    public static function foundation() {
        return [
            'library_name' => 'foundation',
            'use_font_awesome' => true,
            'container_class' => 'grid-container',
            'nav_class' => 'top-bar',
            'brand_class' => 'menu-text',
            'nav_list_class' => 'menu',
            'nav_item_class' => '',
            'nav_link_class' => '',
            'mobile_toggle_class' => 'menu-icon',
            'main_class' => 'main-content',
            'header_section_class' => 'callout primary',
            'title_class' => 'text-center',
            'description_class' => 'lead text-center',
            'footer_class' => 'callout secondary',
            'footer_content_class' => 'grid-x grid-margin-x',
            'footer_section_class' => 'cell medium-6',
            'footer_links_class' => 'no-bullet',
            'footer_bottom_class' => 'text-center',
            'body_class' => '',
            'brand_icon' => 'fas fa-hospital',
            'menu_icon' => 'fas fa-bars',
            'library_init_js' => '
                // Initialize Foundation
                $(document).foundation();
            '
        ];
    }
    
    // MDB5 (Material Design for Bootstrap 5) Configuration
    public static function mdb5() {
        return [
            'library_name' => 'mdb5',
            'use_material_icons' => true,
            'container_class' => 'container',
            'nav_class' => 'navbar navbar-expand-lg navbar-dark',
            'brand_class' => 'navbar-brand',
            'nav_list_class' => 'navbar-nav ms-auto',
            'nav_item_class' => 'nav-item',
            'nav_link_class' => 'nav-link',
            'mobile_toggle_class' => 'navbar-toggler',
            'main_class' => 'main-content',
            'header_section_class' => 'bg-light py-5',
            'title_class' => 'display-4 text-primary',
            'description_class' => 'lead text-muted',
            'footer_class' => 'bg-dark text-light py-4',
            'footer_content_class' => 'row',
            'footer_section_class' => 'col-md-6',
            'footer_links_class' => 'list-unstyled',
            'footer_bottom_class' => 'text-center border-top pt-3 mt-3',
            'body_class' => '',
            'brand_icon' => 'fas fa-hospital',
            'menu_icon' => 'fas fa-bars',
            'library_init_js' => '
                // Initialize MDB5 components
                // MDB5 auto-initializes most components
                
                // Initialize tooltips
                const tooltipTriggerList = [].slice.call(document.querySelectorAll(\'[data-mdb-toggle="tooltip"]\'));
                const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new mdb.Tooltip(tooltipTriggerEl);
                });
                
                // Initialize ripple effect
                mdb.Ripple.init(document.querySelectorAll(\'.btn\'));
            '
        ];
    }

    // Get configuration by library name
    public static function get($library_name) {
        switch (strtolower($library_name)) {
            case 'bootstrap':
            case 'bootstrap5':
                return self::bootstrap5();
            case 'materialize':
                return self::materialize();
            case 'bulma':
                return self::bulma();
            case 'foundation':
                return self::foundation();
            case 'mdb5':
            case 'mdb':
                return self::mdb5();
            default:
                return self::bootstrap5(); // Default fallback
        }
    }
}

// Helper function to easily get library config
function getLibraryConfig($library_name) {
    return LibraryConfig::get($library_name);
}
?>
