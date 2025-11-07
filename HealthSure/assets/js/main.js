// HealthSure Main JavaScript
document.addEventListener('DOMContentLoaded', function() {
    
    // Mobile sidebar toggle
    initMobileSidebar();
    
    // Page loading animations
    initPageAnimations();
    
    // Form enhancements
    initFormEnhancements();
    
    // Table enhancements
    initTableEnhancements();
    
    // Notification auto-hide
    initNotifications();
    
    // Smooth scrolling
    initSmoothScrolling();
});

// Mobile Sidebar Toggle
function initMobileSidebar() {
    // Create mobile menu button
    const mobileMenuBtn = document.createElement('button');
    mobileMenuBtn.className = 'mobile-menu-btn';
    mobileMenuBtn.innerHTML = '☰';
    mobileMenuBtn.setAttribute('aria-label', 'Toggle Menu');
    
    // Add to navbar or create one if it doesn't exist
    let navbar = document.querySelector('.navbar');
    if (!navbar) {
        navbar = document.createElement('div');
        navbar.className = 'navbar mobile-navbar';
        document.body.insertBefore(navbar, document.body.firstChild);
    }
    
    // Only add on mobile
    if (window.innerWidth <= 768) {
        navbar.appendChild(mobileMenuBtn);
    }
    
    // Toggle sidebar on mobile
    mobileMenuBtn.addEventListener('click', function() {
        const sidebar = document.querySelector('.sidebar');
        const mainContent = document.querySelector('.main-content');
        
        sidebar.classList.toggle('active');
        document.body.classList.toggle('sidebar-open');
        
        // Close sidebar when clicking outside
        if (sidebar.classList.contains('active')) {
            document.addEventListener('click', closeSidebarOnOutsideClick);
        }
    });
    
    function closeSidebarOnOutsideClick(e) {
        const sidebar = document.querySelector('.sidebar');
        const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
        
        if (!sidebar.contains(e.target) && !mobileMenuBtn.contains(e.target)) {
            sidebar.classList.remove('active');
            document.body.classList.remove('sidebar-open');
            document.removeEventListener('click', closeSidebarOnOutsideClick);
        }
    }
    
    // Handle window resize
    window.addEventListener('resize', function() {
        const sidebar = document.querySelector('.sidebar');
        if (window.innerWidth > 768) {
            sidebar.classList.remove('active');
            document.body.classList.remove('sidebar-open');
            if (mobileMenuBtn.parentNode) {
                mobileMenuBtn.remove();
            }
        } else if (!document.querySelector('.mobile-menu-btn')) {
            navbar.appendChild(mobileMenuBtn);
        }
    });
}

// Page Loading Animations
function initPageAnimations() {
    // Add page transition class
    document.body.classList.add('page-transition');
    
    // Trigger loaded state
    setTimeout(() => {
        document.body.classList.add('loaded');
    }, 100);
    
    // Animate elements on scroll
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-in');
            }
        });
    }, observerOptions);
    
    // Observe cards and stat cards
    document.querySelectorAll('.card, .stat-card').forEach(el => {
        observer.observe(el);
    });
}

// Form Enhancements
function initFormEnhancements() {
    // Add floating labels effect
    document.querySelectorAll('.form-control').forEach(input => {
        // Add focus/blur animations
        input.addEventListener('focus', function() {
            this.parentNode.classList.add('focused');
        });
        
        input.addEventListener('blur', function() {
            if (!this.value) {
                this.parentNode.classList.remove('focused');
            }
        });
        
        // Check if already has value
        if (input.value) {
            input.parentNode.classList.add('focused');
        }
    });
    
    // Form validation feedback
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function(e) {
            const requiredFields = form.querySelectorAll('[required]');
            let hasErrors = false;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.classList.add('error-shake');
                    hasErrors = true;
                    
                    // Remove shake class after animation
                    setTimeout(() => {
                        field.classList.remove('error-shake');
                    }, 500);
                }
            });
            
            if (hasErrors) {
                e.preventDefault();
            }
        });
    });
}

// Table Enhancements
function initTableEnhancements() {
    // Make tables responsive
    document.querySelectorAll('.table').forEach(table => {
        if (!table.parentNode.classList.contains('table-responsive')) {
            const wrapper = document.createElement('div');
            wrapper.className = 'table-responsive';
            table.parentNode.insertBefore(wrapper, table);
            wrapper.appendChild(table);
        }
    });
    
    // Add row click effects
    document.querySelectorAll('.table tbody tr').forEach(row => {
        row.addEventListener('click', function() {
            // Remove active class from other rows
            document.querySelectorAll('.table tbody tr.active').forEach(r => {
                r.classList.remove('active');
            });
            
            // Add active class to clicked row
            this.classList.add('active');
        });
    });
}

// Notification System
function initNotifications() {
    // Auto-hide alerts after 5 seconds
    document.querySelectorAll('.alert').forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transform = 'translateX(100%)';
            setTimeout(() => {
                if (alert.parentNode) {
                    alert.remove();
                }
            }, 300);
        }, 5000);
        
        // Add close button
        const closeBtn = document.createElement('button');
        closeBtn.innerHTML = '×';
        closeBtn.className = 'alert-close';
        closeBtn.addEventListener('click', () => {
            alert.style.opacity = '0';
            alert.style.transform = 'translateX(100%)';
            setTimeout(() => {
                if (alert.parentNode) {
                    alert.remove();
                }
            }, 300);
        });
        
        alert.appendChild(closeBtn);
    });
}

// Smooth Scrolling
function initSmoothScrolling() {
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
}

// Utility Functions
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type}`;
    notification.textContent = message;
    
    // Add to page
    const container = document.querySelector('.container') || document.body;
    container.insertBefore(notification, container.firstChild);
    
    // Auto-hide
    setTimeout(() => {
        notification.style.opacity = '0';
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 300);
    }, 4000);
}

function showLoading(element) {
    const spinner = document.createElement('div');
    spinner.className = 'loading-spinner';
    element.appendChild(spinner);
    element.disabled = true;
}

function hideLoading(element) {
    const spinner = element.querySelector('.loading-spinner');
    if (spinner) {
        spinner.remove();
    }
    element.disabled = false;
}

// Format currency
function formatCurrency(amount) {
    return new Intl.NumberFormat('en-IN', {
        style: 'currency',
        currency: 'INR'
    }).format(amount);
}

// Format date
function formatDate(dateString) {
    return new Date(dateString).toLocaleDateString('en-IN', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

// Copy to clipboard
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        showNotification('Copied to clipboard!', 'success');
    }).catch(() => {
        showNotification('Failed to copy', 'danger');
    });
}

// Export functions for global use
window.HealthSure = {
    showNotification,
    showLoading,
    hideLoading,
    formatCurrency,
    formatDate,
    copyToClipboard
};
