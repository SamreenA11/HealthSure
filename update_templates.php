<?php
// Script to update all HTML templates with new CSS and JS files
echo "<h2>Updating HealthSure Templates</h2>";

$directories = [
    'admin',
    'customer', 
    'agent',
    'auth'
];

$updated_files = 0;

foreach ($directories as $dir) {
    if (!is_dir($dir)) continue;
    
    $files = glob($dir . '/*.php');
    
    foreach ($files as $file) {
        $content = file_get_contents($file);
        
        // Skip if already updated
        if (strpos($content, 'animations.css') !== false) {
            continue;
        }
        
        // Add animations.css after style.css
        $content = str_replace(
            '<link rel="stylesheet" href="../assets/css/style.css">',
            '<link rel="stylesheet" href="../assets/css/style.css">' . "\n    " . '<link rel="stylesheet" href="../assets/css/animations.css">',
            $content
        );
        
        // Add main.js before closing body tag
        $content = str_replace(
            '</body>',
            '    <script src="../assets/js/main.js"></script>' . "\n" . '</body>',
            $content
        );
        
        // Write updated content
        if (file_put_contents($file, $content)) {
            echo "✓ Updated: $file<br>";
            $updated_files++;
        } else {
            echo "❌ Failed to update: $file<br>";
        }
    }
}

// Update root level files
$root_files = ['landing.php', 'user-guide.php'];

foreach ($root_files as $file) {
    if (!file_exists($file)) continue;
    
    $content = file_get_contents($file);
    
    // Skip if already updated
    if (strpos($content, 'animations.css') !== false) {
        continue;
    }
    
    // Add animations.css after style.css
    $content = str_replace(
        '<link rel="stylesheet" href="assets/css/style.css">',
        '<link rel="stylesheet" href="assets/css/style.css">' . "\n    " . '<link rel="stylesheet" href="assets/css/animations.css">',
        $content
    );
    
    // Add main.js before closing body tag
    $content = str_replace(
        '</body>',
        '    <script src="assets/js/main.js"></script>' . "\n" . '</body>',
        $content
    );
    
    // Write updated content
    if (file_put_contents($file, $content)) {
        echo "✓ Updated: $file<br>";
        $updated_files++;
    } else {
        echo "❌ Failed to update: $file<br>";
    }
}

echo "<br><strong>✅ Template Update Complete!</strong><br>";
echo "Updated $updated_files files with enhanced CSS and JavaScript.<br><br>";

echo "<h3>Enhancements Added:</h3>";
echo "<ul>";
echo "<li>✨ <strong>Smooth animations</strong> for page loads and interactions</li>";
echo "<li>📱 <strong>Mobile-responsive design</strong> with hamburger menu</li>";
echo "<li>🎨 <strong>Enhanced visual effects</strong> with hover animations</li>";
echo "<li>⚡ <strong>Better performance</strong> with optimized CSS</li>";
echo "<li>🔧 <strong>Interactive JavaScript</strong> for better UX</li>";
echo "<li>📊 <strong>Improved table visibility</strong> and responsiveness</li>";
echo "<li>🎯 <strong>Better focus states</strong> for accessibility</li>";
echo "<li>🖨️ <strong>Print-friendly styles</strong> for reports</li>";
echo "</ul>";

echo "<br><a href='landing.php' class='btn btn-primary'>View Enhanced Site</a>";
echo " | <a href='debug.php' class='btn btn-outline'>System Status</a>";
?>

<style>
body { 
    font-family: 'Inter', sans-serif; 
    line-height: 1.6; 
    max-width: 800px; 
    margin: 2rem auto; 
    padding: 2rem;
    background: #f8fafc;
}
h2, h3 { color: #1e293b; }
.btn {
    display: inline-block;
    padding: 0.75rem 1.5rem;
    background: #2563eb;
    color: white;
    text-decoration: none;
    border-radius: 0.5rem;
    margin-right: 1rem;
    transition: all 0.3s;
}
.btn:hover { background: #1d4ed8; transform: translateY(-2px); }
.btn-outline { background: transparent; border: 2px solid #2563eb; color: #2563eb; }
.btn-outline:hover { background: #2563eb; color: white; }
ul { background: white; padding: 1.5rem; border-radius: 0.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
li { margin-bottom: 0.5rem; }
</style>
