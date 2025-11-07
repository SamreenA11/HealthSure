<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

check_role('customer');

$customer_info = get_customer_info($_SESSION['user_id'], $conn);
$payment_history = [];
$payment_stats = [
    'total_payments' => 0,
    'total_premiums' => 0,
    'total_settlements' => 0,
    'yearly_total' => 0,
    'last_payment_date' => null
];

try {
    // Get customer's payment history
    $stmt = $conn->prepare("SELECT p.*, 
                           po.policy_name, po.policy_type,
                           ph.premium_amount, ph.coverage_amount,
                           c.claim_id, c.claim_amount
                           FROM payments p 
                           JOIN policy_holders ph ON p.holder_id = ph.holder_id 
                           JOIN policies po ON ph.policy_id = po.policy_id 
                           LEFT JOIN claims c ON p.claim_id = c.claim_id
                           WHERE ph.customer_id = ? 
                           ORDER BY p.created_at DESC");
    $stmt->execute([$customer_info['customer_id']]);
    $payment_history = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get payment statistics
    $stmt = $conn->prepare("SELECT 
                           COUNT(*) as total_payments,
                           COALESCE(SUM(CASE WHEN payment_type = 'premium' THEN amount ELSE 0 END), 0) as total_premiums,
                           COALESCE(SUM(CASE WHEN payment_type = 'claim_settlement' THEN amount ELSE 0 END), 0) as total_settlements,
                           COALESCE(SUM(CASE WHEN YEAR(payment_date) = YEAR(CURDATE()) THEN amount ELSE 0 END), 0) as yearly_total,
                           MAX(payment_date) as last_payment_date
                           FROM payments p 
                           JOIN policy_holders ph ON p.holder_id = ph.holder_id 
                           WHERE ph.customer_id = ?");
    $stmt->execute([$customer_info['customer_id']]);
    $payment_stats = $stmt->fetch(PDO::FETCH_ASSOC) ?: $payment_stats;
    
} catch (PDOException $e) {
    error_log("Payment History Error: " . $e->getMessage());
    set_flash_message('error', 'Unable to load payment history. Please try again later.');
}

$message = get_flash_message();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment History - HealthSure</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/animations.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Payment History Specific Styles */
        .payment-filters {
            display: flex;
            gap: 0.5rem;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .payment-filters select {
            min-width: 120px;
        }
        
        .table-responsive {
            border-radius: 0.5rem;
            overflow: hidden;
        }
        
        .table th {
            background: var(--light-bg);
            font-weight: 600;
            border-bottom: 2px solid var(--border-color);
        }
        
        .table td {
            vertical-align: middle;
            padding: 1rem 0.75rem;
        }
        
        .payment-amount {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--success-color);
        }
        
        .payment-actions {
            display: flex;
            gap: 0.25rem;
            justify-content: center;
        }
        
        .receipt-modal {
            backdrop-filter: blur(4px);
        }
        
        .receipt-content {
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        
        /* Mobile Responsiveness */
        @media (max-width: 768px) {
            .payment-filters {
                flex-direction: column;
                align-items: stretch;
            }
            
            .payment-filters select {
                min-width: 100%;
                margin-bottom: 0.5rem;
            }
            
            .table th:nth-child(n+6),
            .table td:nth-child(n+6) {
                display: none;
            }
            
            .table td {
                padding: 0.5rem;
                font-size: 0.875rem;
            }
            
            .payment-actions {
                flex-direction: column;
            }
            
            .receipt-content {
                margin: 1rem;
                padding: 1rem;
                max-width: calc(100% - 2rem);
            }
        }
        
        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 1rem;
            }
            
            .table th:nth-child(n+4),
            .table td:nth-child(n+4) {
                display: none;
            }
            
            .card-header {
                flex-direction: column;
                align-items: stretch !important;
            }
            
            .card-header h3 {
                margin-bottom: 1rem;
            }
        }
        
        /* Pagination Styles */
        .pagination {
            margin: 0;
        }
        
        .page-item.active .page-link {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .page-link {
            color: var(--primary-color);
            border: 1px solid var(--border-color);
        }
        
        .page-link:hover {
            background-color: var(--light-bg);
            border-color: var(--primary-color);
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 3rem 2rem;
        }
        
        .empty-state-icon {
            font-size: 4rem;
            opacity: 0.3;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <?php include 'includes/header.php'; ?>
        
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>Payment History</h1>
                <a href="make-payment.php" class="btn btn-primary">Make New Payment</a>
            </div>
            
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message['type']; ?>">
                    <?php echo $message['message']; ?>
                </div>
            <?php endif; ?>
            
            <!-- Payment Statistics -->
            <div class="stats-grid mb-4">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $payment_stats['total_payments'] ?? 0; ?></div>
                    <div class="stat-label">Total Payments</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo format_currency($payment_stats['total_premiums'] ?? 0); ?></div>
                    <div class="stat-label">Premiums Paid</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo format_currency($payment_stats['total_settlements'] ?? 0); ?></div>
                    <div class="stat-label">Claims Received</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo format_currency($payment_stats['yearly_total'] ?? 0); ?></div>
                    <div class="stat-label">This Year</div>
                </div>
            </div>
            
            <?php if (empty($payment_history)): ?>
                <div class="card">
                    <div class="card-body">
                        <div class="empty-state">
                            <div class="empty-state-icon">💳</div>
                            <h4>No Payment History</h4>
                            <p class="text-light">You haven't made any payments yet. Start by making your first premium payment or browse available policies.</p>
                            <div class="d-flex justify-content-center" style="gap: 1rem; margin-top: 2rem;">
                                <a href="make-payment.php" class="btn btn-primary">Make Payment</a>
                                <a href="browse-policies.php" class="btn btn-outline">Browse Policies</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3>Payment Transactions</h3>
                        <div class="payment-filters">
                            <select id="filterType" class="form-control">
                                <option value="">All Types</option>
                                <option value="premium">Premium</option>
                                <option value="claim_settlement">Claim Settlement</option>
                            </select>
                            <select id="filterStatus" class="form-control">
                                <option value="">All Status</option>
                                <option value="completed">Completed</option>
                                <option value="pending">Pending</option>
                                <option value="failed">Failed</option>
                            </select>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="paymentsTable">
                                <thead>
                                    <tr>
                                        <th>Payment ID</th>
                                        <th>Policy</th>
                                        <th>Type</th>
                                        <th>Amount</th>
                                        <th>Method</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($payment_history as $payment): ?>
                                        <tr data-type="<?php echo $payment['payment_type']; ?>" data-status="<?php echo $payment['status']; ?>">
                                            <td>
                                                <strong>#<?php echo $payment['payment_id']; ?></strong>
                                                <?php if (!empty($payment['transaction_id'])): ?>
                                                    <br><small class="text-light"><?php echo htmlspecialchars($payment['transaction_id']); ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div>
                                                    <strong><?php echo htmlspecialchars($payment['policy_name']); ?></strong>
                                                    <br><span class="badge badge-primary"><?php echo ucfirst($payment['policy_type']); ?></span>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge badge-<?php echo $payment['payment_type'] === 'premium' ? 'success' : 'info'; ?>">
                                                    <?php echo ucfirst(str_replace('_', ' ', $payment['payment_type'])); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <strong style="color: var(--success-color);"><?php echo format_currency($payment['amount']); ?></strong>
                                            </td>
                                            <td>
                                                <span class="text-capitalize"><?php echo str_replace('_', ' ', $payment['payment_method']); ?></span>
                                            </td>
                                            <td>
                                                <span><?php echo format_date($payment['payment_date']); ?></span>
                                                <br><small class="text-light"><?php echo date('g:i A', strtotime($payment['payment_date'])); ?></small>
                                            </td>
                                            <td>
                                                <span class="badge badge-<?php echo $payment['status'] === 'completed' ? 'success' : ($payment['status'] === 'failed' ? 'danger' : 'warning'); ?>">
                                                    <?php echo ucfirst($payment['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="d-flex" style="gap: 0.25rem;">
                                                    <button class="btn btn-sm btn-outline" onclick="viewPayment(<?php echo $payment['payment_id']; ?>)" title="View Receipt">
                                                        📄
                                                    </button>
                                                    <?php if ($payment['status'] === 'completed'): ?>
                                                        <button class="btn btn-sm btn-success" onclick="downloadReceipt(<?php echo $payment['payment_id']; ?>)" title="Download Receipt">
                                                            ⬇️
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <?php if (count($payment_history) > 10): ?>
                            <div class="d-flex justify-content-center mt-3">
                                <nav>
                                    <ul class="pagination" id="pagination">
                                        <!-- Pagination will be generated by JavaScript -->
                                    </ul>
                                </nav>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Payment Receipt Modal -->
                <div id="receiptModal" class="receipt-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
                    <div class="receipt-content" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 2rem; border-radius: 0.75rem; max-width: 500px; width: 90%; max-height: 80vh; overflow-y: auto;">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4 style="margin: 0;">Payment Receipt</h4>
                            <button onclick="closeReceiptModal()" class="btn btn-sm btn-outline" style="padding: 0.25rem 0.5rem;">✕</button>
                        </div>
                        <div id="receiptDetails"></div>
                        <div class="d-flex justify-content-center" style="gap: 1rem; margin-top: 2rem;">
                            <button onclick="printReceipt()" class="btn btn-primary">🖨️ Print</button>
                            <button onclick="downloadReceipt(getCurrentReceiptId())" class="btn btn-success">⬇️ Download</button>
                            <button onclick="closeReceiptModal()" class="btn btn-outline">Close</button>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        const payments = <?php echo json_encode($payment_history); ?>;
        let filteredPayments = [...payments];
        let currentPage = 1;
        const itemsPerPage = 10;
        let currentReceiptId = null;
        
        // Initialize filters and pagination
        document.addEventListener('DOMContentLoaded', function() {
            setupFilters();
            updateTable();
        });
        
        function setupFilters() {
            const typeFilter = document.getElementById('filterType');
            const statusFilter = document.getElementById('filterStatus');
            
            if (typeFilter) {
                typeFilter.addEventListener('change', applyFilters);
            }
            if (statusFilter) {
                statusFilter.addEventListener('change', applyFilters);
            }
        }
        
        function applyFilters() {
            const typeFilter = document.getElementById('filterType').value;
            const statusFilter = document.getElementById('filterStatus').value;
            
            filteredPayments = payments.filter(payment => {
                const typeMatch = !typeFilter || payment.payment_type === typeFilter;
                const statusMatch = !statusFilter || payment.status === statusFilter;
                return typeMatch && statusMatch;
            });
            
            currentPage = 1;
            updateTable();
        }
        
        function updateTable() {
            const tbody = document.querySelector('#paymentsTable tbody');
            const rows = tbody.querySelectorAll('tr');
            
            // Hide all rows first
            rows.forEach(row => row.style.display = 'none');
            
            // Show filtered rows for current page
            const startIndex = (currentPage - 1) * itemsPerPage;
            const endIndex = startIndex + itemsPerPage;
            
            filteredPayments.slice(startIndex, endIndex).forEach(payment => {
                const row = tbody.querySelector(`tr[data-type="${payment.payment_type}"][data-status="${payment.status}"]`);
                if (row && row.querySelector('strong').textContent.includes(payment.payment_id)) {
                    row.style.display = '';
                }
            });
            
            updatePagination();
        }
        
        function updatePagination() {
            const totalPages = Math.ceil(filteredPayments.length / itemsPerPage);
            const paginationContainer = document.getElementById('pagination');
            
            if (!paginationContainer || totalPages <= 1) return;
            
            let paginationHTML = '';
            
            // Previous button
            if (currentPage > 1) {
                paginationHTML += `<li class="page-item"><a class="page-link" href="#" onclick="changePage(${currentPage - 1})">Previous</a></li>`;
            }
            
            // Page numbers
            for (let i = 1; i <= totalPages; i++) {
                if (i === currentPage) {
                    paginationHTML += `<li class="page-item active"><span class="page-link">${i}</span></li>`;
                } else {
                    paginationHTML += `<li class="page-item"><a class="page-link" href="#" onclick="changePage(${i})">${i}</a></li>`;
                }
            }
            
            // Next button
            if (currentPage < totalPages) {
                paginationHTML += `<li class="page-item"><a class="page-link" href="#" onclick="changePage(${currentPage + 1})">Next</a></li>`;
            }
            
            paginationContainer.innerHTML = paginationHTML;
        }
        
        function changePage(page) {
            currentPage = page;
            updateTable();
        }
        
        function getCurrentReceiptId() {
            return currentReceiptId;
        }
        
        function viewPayment(paymentId) {
            const payment = payments.find(p => p.payment_id == paymentId);
            if (!payment) return;
            
            currentReceiptId = paymentId;
            const statusColor = payment.status === 'completed' ? 'success' : (payment.status === 'failed' ? 'danger' : 'warning');
            const typeColor = payment.payment_type === 'premium' ? 'success' : 'info';
            
            document.getElementById('receiptDetails').innerHTML = `
                <div class="text-center mb-4">
                    <h3>HealthSure Insurance</h3>
                    <p>Payment Receipt</p>
                    <hr>
                </div>
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Receipt #:</span>
                        <strong>${payment.payment_id}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Date:</span>
                        <strong>${new Date(payment.payment_date).toLocaleDateString()}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Policy:</span>
                        <strong>${payment.policy_name}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Type:</span>
                        <span class="badge badge-${typeColor}">${payment.payment_type.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Method:</span>
                        <strong>${payment.payment_method.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())}</strong>
                    </div>
                    ${payment.transaction_id ? `
                    <div class="d-flex justify-content-between mb-2">
                        <span>Transaction ID:</span>
                        <strong>${payment.transaction_id}</strong>
                    </div>
                    ` : ''}
                </div>
                
                <hr>
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-2" style="font-size: 1.1rem;">
                        <span><strong>Amount Paid:</strong></span>
                        <strong style="color: var(--success-color);">₹${parseFloat(payment.amount).toLocaleString('en-IN')}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Status:</span>
                        <span class="badge badge-${statusColor}">${payment.status.charAt(0).toUpperCase() + payment.status.slice(1)}</span>
                    </div>
                </div>
                
                ${payment.payment_type === 'claim_settlement' && payment.claim_id ? `
                <div class="mb-3" style="background: #f8f9fa; padding: 1rem; border-radius: 0.25rem;">
                    <strong>Claim Settlement Details:</strong><br>
                    Claim ID: #${payment.claim_id}<br>
                    Claim Amount: ₹${parseFloat(payment.claim_amount || 0).toLocaleString('en-IN')}
                </div>
                ` : ''}
                
                <div class="text-center mt-3" style="font-size: 0.875rem; color: var(--text-light);">
                    <p>Thank you for choosing HealthSure Insurance</p>
                    <p>Keep this receipt for your records</p>
                </div>
            `;
            
            document.getElementById('receiptModal').style.display = 'block';
        }
        
        function downloadReceipt(paymentId) {
            const payment = payments.find(p => p.payment_id == paymentId);
            if (!payment) return;
            
            // Create a temporary link to download receipt as PDF-like content
            const receiptContent = generateReceiptHTML(payment);
            const blob = new Blob([receiptContent], { type: 'text/html' });
            const url = URL.createObjectURL(blob);
            
            const a = document.createElement('a');
            a.href = url;
            a.download = `HealthSure_Receipt_${payment.payment_id}.html`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        }
        
        function generateReceiptHTML(payment) {
            const statusColor = payment.status === 'completed' ? 'success' : (payment.status === 'failed' ? 'danger' : 'warning');
            const typeColor = payment.payment_type === 'premium' ? 'success' : 'info';
            
            return `
                <!DOCTYPE html>
                <html>
                <head>
                    <title>HealthSure Payment Receipt #${payment.payment_id}</title>
                    <style>
                        body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
                        .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 20px; margin-bottom: 20px; }
                        .d-flex { display: flex; justify-content: space-between; margin-bottom: 10px; }
                        .badge { padding: 4px 8px; border-radius: 4px; font-size: 12px; }
                        .badge-success { background: #28a745; color: white; }
                        .badge-info { background: #17a2b8; color: white; }
                        .badge-warning { background: #ffc107; color: black; }
                        .badge-danger { background: #dc3545; color: white; }
                        .amount { font-size: 18px; font-weight: bold; color: #28a745; }
                        .footer { text-align: center; margin-top: 30px; font-size: 12px; color: #666; }
                    </style>
                </head>
                <body>
                    <div class="header">
                        <h1>HealthSure Insurance</h1>
                        <h2>Payment Receipt</h2>
                    </div>
                    
                    <div class="d-flex"><span>Receipt #:</span><strong>${payment.payment_id}</strong></div>
                    <div class="d-flex"><span>Date:</span><strong>${new Date(payment.payment_date).toLocaleDateString()}</strong></div>
                    <div class="d-flex"><span>Policy:</span><strong>${payment.policy_name}</strong></div>
                    <div class="d-flex"><span>Type:</span><span class="badge badge-${typeColor}">${payment.payment_type.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())}</span></div>
                    <div class="d-flex"><span>Method:</span><strong>${payment.payment_method.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())}</strong></div>
                    ${payment.transaction_id ? `<div class="d-flex"><span>Transaction ID:</span><strong>${payment.transaction_id}</strong></div>` : ''}
                    
                    <hr>
                    
                    <div class="d-flex"><span><strong>Amount Paid:</strong></span><span class="amount">₹${parseFloat(payment.amount).toLocaleString('en-IN')}</span></div>
                    <div class="d-flex"><span>Status:</span><span class="badge badge-${statusColor}">${payment.status.charAt(0).toUpperCase() + payment.status.slice(1)}</span></div>
                    
                    ${payment.payment_type === 'claim_settlement' && payment.claim_id ? `
                    <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin-top: 20px;">
                        <strong>Claim Settlement Details:</strong><br>
                        Claim ID: #${payment.claim_id}<br>
                        Claim Amount: ₹${parseFloat(payment.claim_amount || 0).toLocaleString('en-IN')}
                    </div>
                    ` : ''}
                    
                    <div class="footer">
                        <p>Thank you for choosing HealthSure Insurance</p>
                        <p>Keep this receipt for your records</p>
                        <p>Generated on ${new Date().toLocaleString()}</p>
                    </div>
                </body>
                </html>
            `;
        }
        
        function closeReceiptModal() {
            document.getElementById('receiptModal').style.display = 'none';
        }
        
        function printReceipt() {
            const receiptContent = document.getElementById('receiptDetails').innerHTML;
            const printWindow = window.open('', '_blank');
            printWindow.document.write(`
                <html>
                <head>
                    <title>Payment Receipt</title>
                    <style>
                        body { font-family: Arial, sans-serif; margin: 20px; }
                        .d-flex { display: flex; }
                        .justify-content-between { justify-content: space-between; }
                        .mb-2 { margin-bottom: 0.5rem; }
                        .mb-3 { margin-bottom: 1rem; }
                        .text-center { text-align: center; }
                        .badge { padding: 0.25rem 0.5rem; border-radius: 0.25rem; font-size: 0.75rem; }
                        .badge-success { background: #28a745; color: white; }
                        .badge-info { background: #17a2b8; color: white; }
                        .badge-warning { background: #ffc107; color: black; }
                        .badge-danger { background: #dc3545; color: white; }
                        hr { border: 1px solid #ddd; }
                    </style>
                </head>
                <body>
                    ${receiptContent}
                </body>
                </html>
            `);
            printWindow.document.close();
            printWindow.print();
        }
        
        // Close modal when clicking outside
        document.getElementById('receiptModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeReceiptModal();
            }
        });
    </script>
    <script src="../assets/js/main.js"></script>
</body>
</html>
