<?php
// Add sample data for testing claims and payments
require_once 'config/database.php';
require_once 'includes/functions.php';

echo "<h2>Adding Sample Data</h2>";

try {
    $conn->beginTransaction();
    
    // 1. Create a sample customer
    $email = 'customer@test.com';
    $password_hash = hash_password('password');
    
    // Check if customer already exists
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $existing_user = $stmt->fetch();
    
    if (!$existing_user) {
        // Create user
        $stmt = $conn->prepare("INSERT INTO users (email, password_hash, role) VALUES (?, ?, 'customer')");
        $stmt->execute([$email, $password_hash]);
        $user_id = $conn->lastInsertId();
        
        // Create customer profile
        $stmt = $conn->prepare("INSERT INTO customers (user_id, first_name, last_name, phone, address, date_of_birth, gender) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, 'John', 'Doe', '+1234567890', '123 Main Street, City', '1990-01-15', 'male']);
        $customer_id = $conn->lastInsertId();
        
        echo "✓ Sample customer created (john.doe@test.com)<br>";
    } else {
        $user_id = $existing_user['user_id'];
        $stmt = $conn->prepare("SELECT customer_id FROM customers WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $customer_id = $stmt->fetch()['customer_id'];
        echo "✓ Using existing customer<br>";
    }
    
    // 2. Create a policy holder (customer with a policy)
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM policy_holders WHERE customer_id = ?");
    $stmt->execute([$customer_id]);
    $existing_holders = $stmt->fetch()['count'];
    
    if ($existing_holders == 0) {
        // Get first available policy
        $stmt = $conn->query("SELECT policy_id, base_premium FROM policies WHERE status = 'active' LIMIT 1");
        $policy = $stmt->fetch();
        
        if ($policy) {
            $start_date = date('Y-m-d');
            $end_date = date('Y-m-d', strtotime('+1 year'));
            
            $stmt = $conn->prepare("INSERT INTO policy_holders (customer_id, policy_id, start_date, end_date, premium_amount, status) VALUES (?, ?, ?, ?, ?, 'active')");
            $stmt->execute([$customer_id, $policy['policy_id'], $start_date, $end_date, $policy['base_premium']]);
            $holder_id = $conn->lastInsertId();
            
            echo "✓ Policy holder created<br>";
        }
    } else {
        $stmt = $conn->prepare("SELECT holder_id FROM policy_holders WHERE customer_id = ? LIMIT 1");
        $stmt->execute([$customer_id]);
        $holder_id = $stmt->fetch()['holder_id'];
        echo "✓ Using existing policy holder<br>";
    }
    
    // 3. Create sample claims
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM claims WHERE holder_id = ?");
    $stmt->execute([$holder_id]);
    $existing_claims = $stmt->fetch()['count'];
    
    if ($existing_claims == 0) {
        // Create sample claims
        $claims = [
            ['amount' => 25000, 'reason' => 'Medical treatment for fever and infection', 'status' => 'pending'],
            ['amount' => 15000, 'reason' => 'Dental treatment and consultation', 'status' => 'approved'],
            ['amount' => 5000, 'reason' => 'Regular health checkup and tests', 'status' => 'rejected']
        ];
        
        foreach ($claims as $claim) {
            $stmt = $conn->prepare("INSERT INTO claims (holder_id, claim_amount, claim_reason, claim_date, status, approved_amount) VALUES (?, ?, ?, CURDATE(), ?, ?)");
            $approved_amount = $claim['status'] === 'approved' ? $claim['amount'] : 0;
            $stmt->execute([$holder_id, $claim['amount'], $claim['reason'], $claim['status'], $approved_amount]);
            
            if ($claim['status'] === 'approved') {
                $claim_id = $conn->lastInsertId();
                // Create payment for approved claim
                $stmt = $conn->prepare("INSERT INTO payments (holder_id, claim_id, payment_type, amount, payment_method, payment_date, status) VALUES (?, ?, 'claim_settlement', ?, 'bank_transfer', CURDATE(), 'completed')");
                $stmt->execute([$holder_id, $claim_id, $approved_amount]);
            }
        }
        
        echo "✓ Sample claims created<br>";
    } else {
        echo "✓ Claims already exist<br>";
    }
    
    // 4. Create sample premium payments
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM payments WHERE holder_id = ? AND payment_type = 'premium'");
    $stmt->execute([$holder_id]);
    $existing_payments = $stmt->fetch()['count'];
    
    if ($existing_payments == 0) {
        // Get premium amount
        $stmt = $conn->prepare("SELECT premium_amount FROM policy_holders WHERE holder_id = ?");
        $stmt->execute([$holder_id]);
        $premium_amount = $stmt->fetch()['premium_amount'];
        
        // Create premium payment
        $stmt = $conn->prepare("INSERT INTO payments (holder_id, payment_type, amount, payment_method, payment_date, status, transaction_id) VALUES (?, 'premium', ?, 'online', CURDATE(), 'completed', 'TXN123456789')");
        $stmt->execute([$holder_id, $premium_amount]);
        
        echo "✓ Sample premium payment created<br>";
    } else {
        echo "✓ Premium payments already exist<br>";
    }
    
    $conn->commit();
    echo "<br><strong>✅ Sample data added successfully!</strong><br>";
    echo "<br><a href='admin/claims.php'>View Claims</a> | <a href='admin/payments.php'>View Payments</a> | <a href='admin/dashboard.php'>Admin Dashboard</a>";
    
} catch (Exception $e) {
    $conn->rollBack();
    echo "❌ Error: " . $e->getMessage();
}
?>
