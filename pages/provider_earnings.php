<?php
$pageTitle = 'Provider Earnings & Payouts';
require_once __DIR__ . '/../includes/dashboard_header.php';
requireRole('provider');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/payment_helper.php';
require_once __DIR__ . '/../includes/sidebar_provider.php';

$providerId = $_SESSION['user_id'];

// Financial Metrics for Provider
$stmtEarned = $pdo->prepare("
    SELECT SUM(p.amount) 
    FROM payments p 
    WHERE p.provider_id = ? AND p.payment_status = 'Success'
");
$stmtEarned->execute([$providerId]);
$totalEarnings = (float)($stmtEarned->fetchColumn() ?: 0);

$stmtJobs = $pdo->prepare("
    SELECT COUNT(*) 
    FROM bookings 
    WHERE provider_id = ? AND booking_status IN ('confirmed', 'accepted', 'completed')
");
$stmtJobs->execute([$providerId]);
$activeJobsCount = (int)$stmtJobs->fetchColumn();

// Fetch Earnings Breakdown list
$stmtList = $pdo->prepare("
    SELECT b.*, s.title AS service_title, s.price, u_cust.name AS customer_name,
           p.transaction_id, p.payment_status, p.payment_method, p.amount AS paid_amount, p.created_at AS payment_date
    FROM bookings b
    JOIN services s ON b.service_id = s.id
    JOIN users u_cust ON b.user_id = u_cust.id
    LEFT JOIN payments p ON b.id = p.booking_id
    WHERE b.provider_id = ?
    ORDER BY b.created_at DESC
");
$stmtList->execute([$providerId]);
$jobs = $stmtList->fetchAll();
?>

<div class="dashboard-main">
    <?php require_once __DIR__ . '/../includes/topbar.php'; ?>

    <div class="dashboard-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                <div>
                    <h3 class="fw-bold mb-1">💰 Service Earnings & Job Payouts</h3>
                    <p class="text-muted mb-0">Track your completed bookings, client payments, and revenue summaries.</p>
                </div>
            </div>

            <!-- Provider Metrics Cards -->
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm bg-success text-white p-4 rounded-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-white-50 small fw-bold text-uppercase">Total Earned Revenue</div>
                                <div class="fs-1 fw-bold">₹<?= number_format($totalEarnings, 2) ?></div>
                            </div>
                            <div class="display-4 text-white-50"><i class="bi bi-cash-coin"></i></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm bg-primary text-white p-4 rounded-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-white-50 small fw-bold text-uppercase">Active / Confirmed Bookings</div>
                                <div class="fs-1 fw-bold"><?= $activeJobsCount ?></div>
                            </div>
                            <div class="display-4 text-white-50"><i class="bi bi-calendar-check-fill"></i></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Jobs & Earnings Table -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-list-task me-2 text-primary"></i>Job Earnings Details</h5>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($jobs)): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-inbox text-muted display-4 d-block mb-3"></i>
                            <h5 class="fw-bold text-dark">No Service Bookings Received Yet</h5>
                            <p class="text-muted">Once clients book your services, payment statuses will appear here.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">Booking Ref</th>
                                        <th>Service Name</th>
                                        <th>Customer</th>
                                        <th>Service Date</th>
                                        <th>Job Status</th>
                                        <th>Client Payment</th>
                                        <th class="text-end pe-4">Payout Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($jobs as $j): ?>
                                        <tr>
                                            <td class="ps-4 fw-bold">#BK-<?= str_pad($j['id'], 5, '0', STR_PAD_LEFT) ?></td>
                                            <td class="fw-semibold text-dark"><?= htmlspecialchars($j['service_title']) ?></td>
                                            <td><?= htmlspecialchars($j['customer_name']) ?></td>
                                            <td class="text-muted small"><?= date('M j, Y', strtotime($j['booking_date'])) ?></td>
                                            <td><?= getBookingStatusBadge($j['booking_status']) ?></td>
                                            <td>
                                                <?= getPaymentStatusBadge($j['payment_status'] ?: $j['payment_status_col'] ?? 'unpaid') ?>
                                                <?php if(!empty($j['transaction_id'])): ?>
                                                    <div class="small text-muted font-monospace mt-1"><?= htmlspecialchars($j['transaction_id']) ?></div>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-end pe-4 fw-bold text-success fs-6">
                                                ₹<?= number_format($j['paid_amount'] ?: $j['price'], 2) ?>
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
