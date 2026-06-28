<?php
$pageTitle = 'Payment Successful';
require_once __DIR__ . '/../includes/dashboard_header.php';
requireRole('user');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/payment_helper.php';
require_once __DIR__ . '/../includes/sidebar_user.php';

$txnId = trim($_GET['transaction_id'] ?? '');

if (empty($txnId)) {
    setFlash('danger', 'Transaction reference missing.');
    header('Location: booking_history.php');
    exit;
}

$stmt = $pdo->prepare("
    SELECT p.*, b.booking_date, b.notes, s.title AS service_title, s.category, u.name AS provider_name
    FROM payments p
    JOIN bookings b ON p.booking_id = b.id
    JOIN services s ON b.service_id = s.id
    JOIN users u ON p.provider_id = u.id
    WHERE p.transaction_id = ? AND p.user_id = ?
");
$stmt->execute([$txnId, $_SESSION['user_id']]);
$payment = $stmt->fetch();

if (!$payment) {
    setFlash('danger', 'Transaction not found.');
    header('Location: booking_history.php');
    exit;
}
?>

<div class="dashboard-main">
    <?php require_once __DIR__ . '/../includes/topbar.php'; ?>

    <div class="dashboard-content">
        <div class="container-fluid max-width-800">
            <div class="card border-0 shadow-sm text-center p-4 py-5 rounded-4">
                <div class="card-body">
                    <!-- Success Icon Animation -->
                    <div class="mb-4">
                        <span class="d-inline-flex align-items-center justify-content-center bg-success-subtle text-success rounded-circle p-4" style="width: 90px; height: 90px;">
                            <i class="bi bi-check-lg display-4"></i>
                        </span>
                    </div>

                    <h2 class="fw-bold text-dark mb-1">Payment Successful!</h2>
                    <p class="text-muted mb-4">Your service booking has been confirmed and payment was processed successfully.</p>

                    <div class="badge bg-success-subtle text-success px-3 py-2 rounded-pill fs-6 mb-4">
                        Transaction ID: <strong><?= htmlspecialchars($payment['transaction_id']) ?></strong>
                    </div>

                    <!-- Receipt Summary Table -->
                    <div class="row justify-content-center mb-4 text-start">
                        <div class="col-md-10">
                            <div class="border rounded-3 p-4 bg-light">
                                <h6 class="fw-bold border-bottom pb-2 mb-3 text-primary"><i class="bi bi-file-earmark-text me-2"></i>Receipt Summary</h6>
                                
                                <div class="row g-3 mb-2">
                                    <div class="col-sm-6">
                                        <span class="text-muted small d-block">Service Name</span>
                                        <span class="fw-semibold text-dark"><?= htmlspecialchars($payment['service_title']) ?></span>
                                    </div>
                                    <div class="col-sm-6">
                                        <span class="text-muted small d-block">Provider</span>
                                        <span class="fw-semibold text-dark"><?= htmlspecialchars($payment['provider_name']) ?></span>
                                    </div>
                                </div>

                                <div class="row g-3 mb-2">
                                    <div class="col-sm-6">
                                        <span class="text-muted small d-block">Booking Date</span>
                                        <span class="fw-semibold text-dark"><?= date('F j, Y', strtotime($payment['booking_date'])) ?></span>
                                    </div>
                                    <div class="col-sm-6">
                                        <span class="text-muted small d-block">Payment Method</span>
                                        <span class="fw-semibold text-dark"><?= htmlspecialchars($payment['payment_method']) ?> (<?= maskPaymentDetails($payment['payment_method'], $payment['payment_details']) ?>)</span>
                                    </div>
                                </div>

                                <hr class="my-3">

                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-bold text-dark">Amount Paid</span>
                                    <span class="fw-bold fs-4 text-success">₹<?= number_format($payment['amount'], 2) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex flex-wrap justify-content-center gap-3">
                        <a href="invoice.php?txn=<?= urlencode($payment['transaction_id']) ?>" target="_blank" class="btn btn-outline-primary btn-lg px-4">
                            <i class="bi bi-printer me-2"></i>View / Print Invoice
                        </a>
                        <a href="booking_history.php" class="btn btn-primary btn-lg px-4">
                            <i class="bi bi-calendar-check me-2"></i>My Bookings
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/dashboard_footer.php'; ?>
