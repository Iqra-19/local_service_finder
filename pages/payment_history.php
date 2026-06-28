<?php
$pageTitle = 'Payment History';
require_once __DIR__ . '/../includes/dashboard_header.php';
requireRole('user');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/payment_helper.php';
require_once __DIR__ . '/../includes/sidebar_user.php';

$userId = $_SESSION['user_id'];

// Search / Filter parameter
$search = trim($_GET['search'] ?? '');

$query = "
    SELECT p.*, s.title AS service_title, b.booking_date
    FROM payments p
    JOIN bookings b ON p.booking_id = b.id
    JOIN services s ON b.service_id = s.id
    WHERE p.user_id = ?
";
$params = [$userId];

if (!empty($search)) {
    $query .= " AND (p.transaction_id LIKE ? OR s.title LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$query .= " ORDER BY p.created_at DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$payments = $stmt->fetchAll();
?>

<div class="dashboard-main">
    <?php require_once __DIR__ . '/../includes/topbar.php'; ?>

    <div class="dashboard-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                <div>
                    <h3 class="fw-bold mb-1">💳 My Payment History</h3>
                    <p class="text-muted mb-0">View all transactions, simulated payment receipts, and billing history.</p>
                </div>
                <div>
                    <form method="GET" class="d-flex gap-2">
                        <input type="text" name="search" class="form-control" placeholder="Search Txn ID or Service..." value="<?= htmlspecialchars($search) ?>">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i></button>
                        <?php if(!empty($search)): ?>
                            <a href="payment_history.php" class="btn btn-outline-secondary"><i class="bi bi-x-circle"></i></a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <?php if (empty($payments)): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-wallet2 text-muted display-4 d-block mb-3"></i>
                            <h5 class="fw-bold text-dark">No Payment Transactions Found</h5>
                            <p class="text-muted">You have not completed any simulated service payments yet.</p>
                            <a href="browse_services.php" class="btn btn-primary mt-2">Browse Services</a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">Transaction ID</th>
                                        <th>Service Title</th>
                                        <th>Amount</th>
                                        <th>Method</th>
                                        <th>Date & Time</th>
                                        <th>Status</th>
                                        <th class="text-end pe-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($payments as $p): ?>
                                        <tr>
                                            <td class="ps-4 fw-bold text-primary">
                                                <code><?= htmlspecialchars($p['transaction_id']) ?></code>
                                            </td>
                                            <td><?= htmlspecialchars($p['service_title']) ?></td>
                                            <td class="fw-bold text-dark">₹<?= number_format($p['amount'], 2) ?></td>
                                            <td>
                                                <span class="badge bg-light text-dark border">
                                                    <?= htmlspecialchars($p['payment_method']) ?>
                                                </span>
                                            </td>
                                            <td class="text-muted small">
                                                <?= date('M j, Y, h:i A', strtotime($p['created_at'])) ?>
                                            </td>
                                            <td><?= getPaymentStatusBadge($p['payment_status']) ?></td>
                                            <td class="text-end pe-4">
                                                <a href="invoice.php?txn=<?= urlencode($p['transaction_id']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary" title="Print Invoice">
                                                    <i class="bi bi-printer me-1"></i> Invoice
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
    </div>
</div>

<?php require_once __DIR__ . '/../includes/dashboard_footer.php'; ?>
