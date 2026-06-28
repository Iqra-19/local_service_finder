<?php
$pageTitle = 'Admin - Payment Transactions';
require_once __DIR__ . '/../includes/dashboard_header.php';
requireRole('admin');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/payment_helper.php';

// Search & Filter parameters
$search = trim($_GET['search'] ?? '');
$statusFilter = trim($_GET['status'] ?? '');
$methodFilter = trim($_GET['method'] ?? '');

// Financial Analytics Queries
$totalRevStmt = $pdo->query("SELECT SUM(amount) FROM payments WHERE payment_status = 'Success'");
$totalRevenue = (float)($totalRevStmt->fetchColumn() ?: 0);

$countSuccessStmt = $pdo->query("SELECT COUNT(*) FROM payments WHERE payment_status = 'Success'");
$totalSuccessCount = (int)$countSuccessStmt->fetchColumn();

$pendingAmtStmt = $pdo->query("SELECT SUM(amount) FROM payments WHERE payment_status = 'Pending'");
$totalPendingAmount = (float)($pendingAmtStmt->fetchColumn() ?: 0);

// Build Filtered Main Query
$query = "
    SELECT p.*, s.title AS service_title, 
           u_cust.name AS customer_name, u_cust.email AS customer_email,
           u_prov.name AS provider_name
    FROM payments p
    JOIN bookings b ON p.booking_id = b.id
    JOIN services s ON b.service_id = s.id
    JOIN users u_cust ON p.user_id = u_cust.id
    JOIN users u_prov ON p.provider_id = u_prov.id
    WHERE 1=1
";
$params = [];

if (!empty($search)) {
    $query .= " AND (p.transaction_id LIKE ? OR u_cust.name LIKE ? OR u_prov.name LIKE ? OR s.title LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($statusFilter)) {
    $query .= " AND p.payment_status = ?";
    $params[] = $statusFilter;
}

if (!empty($methodFilter)) {
    $query .= " AND p.payment_method = ?";
    $params[] = $methodFilter;
}

$query .= " ORDER BY p.created_at DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$payments = $stmt->fetchAll();
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h2 class="fw-bold mb-1"><i class="bi bi-bank me-2 text-primary"></i>Payment Management Panel</h2>
            <p class="text-muted mb-0">Monitor platform financials, search transaction logs, and analyze revenue streams.</p>
        </div>
        <div>
            <a href="user_dashboard.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Back to Dashboard</a>
        </div>
    </div>

    <!-- Analytics Stats Widgets -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-primary text-white p-3 rounded-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-white-50 small fw-bold text-uppercase">Total Collected Revenue</div>
                        <div class="fs-2 fw-bold">₹<?= number_format($totalRevenue, 2) ?></div>
                    </div>
                    <div class="fs-1 text-white-50"><i class="bi bi-cash-stack"></i></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-success text-white p-3 rounded-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-white-50 small fw-bold text-uppercase">Successful Transactions</div>
                        <div class="fs-2 fw-bold"><?= $totalSuccessCount ?></div>
                    </div>
                    <div class="fs-1 text-white-50"><i class="bi bi-check-circle-fill"></i></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-warning text-dark p-3 rounded-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-dark-50 small fw-bold text-uppercase">Pending Pipeline</div>
                        <div class="fs-2 fw-bold">₹<?= number_format($totalPendingAmount, 2) ?></div>
                    </div>
                    <div class="fs-1 text-dark-50"><i class="bi bi-clock-history"></i></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form method="GET" class="row g-2 align-items-center">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Search Txn ID, Customer, Provider, Service..." value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">-- All Statuses --</option>
                        <option value="Success" <?= $statusFilter === 'Success' ? 'selected' : '' ?>>Success</option>
                        <option value="Pending" <?= $statusFilter === 'Pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="Processing" <?= $statusFilter === 'Processing' ? 'selected' : '' ?>>Processing</option>
                        <option value="Failed" <?= $statusFilter === 'Failed' ? 'selected' : '' ?>>Failed</option>
                        <option value="Refunded" <?= $statusFilter === 'Refunded' ? 'selected' : '' ?>>Refunded</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="method" class="form-select">
                        <option value="">-- All Methods --</option>
                        <option value="UPI" <?= $methodFilter === 'UPI' ? 'selected' : '' ?>>UPI</option>
                        <option value="Credit Card" <?= $methodFilter === 'Credit Card' ? 'selected' : '' ?>>Credit Card</option>
                        <option value="Debit Card" <?= $methodFilter === 'Debit Card' ? 'selected' : '' ?>>Debit Card</option>
                        <option value="Net Banking" <?= $methodFilter === 'Net Banking' ? 'selected' : '' ?>>Net Banking</option>
                        <option value="Wallet" <?= $methodFilter === 'Wallet' ? 'selected' : '' ?>>Wallet</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-funnel me-1"></i> Filter</button>
                    <?php if (!empty($search) || !empty($statusFilter) || !empty($methodFilter)): ?>
                        <a href="admin_payments.php" class="btn btn-outline-secondary" title="Reset Filters"><i class="bi bi-arrow-counterclockwise"></i></a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Payments Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <?php if (empty($payments)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-search text-muted display-4 d-block mb-3"></i>
                    <h5 class="fw-bold text-dark">No Matching Transactions</h5>
                    <p class="text-muted">Try adjusting your search or filter options.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Txn ID</th>
                                <th>Customer</th>
                                <th>Provider</th>
                                <th>Service</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th class="text-end pe-4">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($payments as $p): ?>
                                <tr>
                                    <td class="ps-4 fw-bold"><code><?= htmlspecialchars($p['transaction_id']) ?></code></td>
                                    <td>
                                        <div class="fw-semibold text-dark"><?= htmlspecialchars($p['customer_name']) ?></div>
                                        <div class="text-muted small"><?= htmlspecialchars($p['customer_email']) ?></div>
                                    </td>
                                    <td><span class="text-dark fw-semibold"><?= htmlspecialchars($p['provider_name']) ?></span></td>
                                    <td><?= htmlspecialchars($p['service_title']) ?></td>
                                    <td class="fw-bold text-success">₹<?= number_format($p['amount'], 2) ?></td>
                                    <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($p['payment_method']) ?></span></td>
                                    <td><?= getPaymentStatusBadge($p['payment_status']) ?></td>
                                    <td class="text-muted small"><?= date('M j, Y H:i', strtotime($p['created_at'])) ?></td>
                                    <td class="text-end pe-4">
                                        <a href="invoice.php?txn=<?= urlencode($p['transaction_id']) ?>" target="_blank" class="btn btn-sm btn-outline-primary" title="View Official Receipt">
                                            <i class="bi bi-file-earmark-text"></i> Receipt
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/dashboard_footer.php'; ?>
