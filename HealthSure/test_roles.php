<?php
// Test page to verify role-based login functionality
require_once 'config/database.php';
require_once 'includes/functions.php';

echo "<h2>HealthSure Role-Based Login Test</h2>";

// Check available users and their roles
try {
    $stmt = $conn->query("SELECT user_id, email, role, status FROM users ORDER BY role, email");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($users)) {
        echo "<div class='alert alert-warning'>No users found in database. Please run setup first.</div>";
        echo "<a href='setup.php' class='btn btn-primary'>Run Setup</a>";
    } else {
        echo "<h3>Available Test Accounts:</h3>";
        echo "<table class='table'>";
        echo "<thead><tr><th>Email</th><th>Role</th><th>Status</th><th>Login Link</th></tr></thead>";
        echo "<tbody>";
        
        foreach ($users as $user) {
            $role_color = [
                'admin' => 'danger',
                'agent' => 'warning', 
                'customer' => 'success'
            ];
            
            $status_color = $user['status'] === 'active' ? 'success' : 'danger';
            
            echo "<tr>";
            echo "<td>" . htmlspecialchars($user['email']) . "</td>";
            echo "<td><span class='badge badge-{$role_color[$user['role']]}'>" . ucfirst($user['role']) . "</span></td>";
            echo "<td><span class='badge badge-{$status_color}'>" . ucfirst($user['status']) . "</span></td>";
            echo "<td><a href='auth/login.php?role={$user['role']}' class='btn btn-sm btn-outline'>Login as {$user['role']}</a></td>";
            echo "</tr>";
        }
        
        echo "</tbody></table>";
    }
    
} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>Database error: " . $e->getMessage() . "</div>";
}

echo "<h3>How Role-Based Login Works:</h3>";
echo "<ul>";
echo "<li><strong>Admin Portal:</strong> Only users with 'admin' role can access</li>";
echo "<li><strong>Agent Portal:</strong> Only users with 'agent' role can access</li>";
echo "<li><strong>Customer Portal:</strong> Only users with 'customer' role can access</li>";
echo "<li><strong>Role Validation:</strong> System checks user's actual role against expected role</li>";
echo "<li><strong>Error Handling:</strong> Shows helpful message if wrong portal is used</li>";
echo "</ul>";

echo "<h3>Test Instructions:</h3>";
echo "<ol>";
echo "<li>Click on a role-specific login link above</li>";
echo "<li>Try logging in with credentials for a different role</li>";
echo "<li>You should see an error message explaining the role mismatch</li>";
echo "<li>Use the correct credentials for the selected role</li>";
echo "<li>You should be redirected to the appropriate dashboard</li>";
echo "</ol>";

echo "<div class='mt-4'>";
echo "<a href='landing.php' class='btn btn-primary'>Back to Landing Page</a> ";
echo "<a href='add_sample_data.php' class='btn btn-success'>Add Sample Data</a> ";
echo "<a href='debug.php' class='btn btn-outline'>System Debug</a>";
echo "</div>";
?>

<style>
body { 
    font-family: 'Inter', sans-serif; 
    line-height: 1.6; 
    max-width: 1000px; 
    margin: 2rem auto; 
    padding: 2rem;
    background: #f8fafc;
}

h2, h3 { color: #1e293b; margin-top: 2rem; }
h2 { margin-top: 0; }

.table {
    width: 100%;
    border-collapse: collapse;
    background: white;
    border-radius: 0.5rem;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.table th,
.table td {
    padding: 1rem;
    text-align: left;
    border-bottom: 1px solid #e2e8f0;
}

.table th {
    background: #f8fafc;
    font-weight: 600;
}

.btn {
    display: inline-block;
    padding: 0.5rem 1rem;
    background: #2563eb;
    color: white;
    text-decoration: none;
    border-radius: 0.375rem;
    margin-right: 0.5rem;
    transition: all 0.3s;
    font-size: 0.875rem;
}

.btn:hover { 
    background: #1d4ed8; 
    transform: translateY(-1px); 
}

.btn-sm { padding: 0.375rem 0.75rem; font-size: 0.75rem; }
.btn-outline { background: transparent; border: 1px solid #2563eb; color: #2563eb; }
.btn-outline:hover { background: #2563eb; color: white; }
.btn-success { background: #059669; }
.btn-success:hover { background: #047857; }

.badge {
    display: inline-flex;
    align-items: center;
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
    font-weight: 500;
    border-radius: 9999px;
    text-transform: uppercase;
    letter-spacing: 0.025em;
}

.badge-success { background: rgba(16, 185, 129, 0.1); color: #059669; }
.badge-warning { background: rgba(245, 158, 11, 0.1); color: #d97706; }
.badge-danger { background: rgba(239, 68, 68, 0.1); color: #dc2626; }

.alert {
    padding: 1rem;
    border-radius: 0.5rem;
    margin: 1rem 0;
}

.alert-warning { background: #fef3c7; color: #92400e; border: 1px solid #f59e0b; }
.alert-danger { background: #fee2e2; color: #991b1b; border: 1px solid #ef4444; }

ul, ol { 
    background: white; 
    padding: 1.5rem; 
    border-radius: 0.5rem; 
    box-shadow: 0 1px 3px rgba(0,0,0,0.1); 
    margin: 1rem 0;
}

li { margin-bottom: 0.5rem; }

.mt-4 { margin-top: 2rem; }
</style>
