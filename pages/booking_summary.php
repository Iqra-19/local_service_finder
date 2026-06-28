<?php
$pageTitle = 'Booking Summary';
require_once __DIR__ . '/../includes/dashboard_header.php';
requireRole('user');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/payment_helper.php';
require_once __DIR__ . '/../includes/sidebar_user.php';

$bookingId = filter_input(INPUT_GET, 'booking_id', FILTER_VALIDATE_INT);

if (!$bookingId) {
    setFlash('danger', 'Invalid booking reference.');
    header('Location: booking_history.php');
    exit;
}

// Fetch booking with service and provider info
$stmt = $pdo->prepare("
    SELECT b.*, s.title AS service_title, s.category, s.price, s.description, 
           u_prov.name AS provider_name, u_prov.email AS provider_email
    FROM bookings b
    JOIN services s ON b.service_id = s.id
    JOIN users u_prov ON b.provider_id = u_prov.id
    WHERE b.id = ? AND b.user_id = ?
");
$stmt->execute([$bookingId, $_SESSION['user_id']]);
$booking = $stmt->fetch();

if (!$booking) {
    setFlash('danger', 'Booking not found or access denied.');
    header('Location: booking_history.php');
    exit;
}

// Price Calculations
$basePrice = (float)$booking['price'];
$convenienceFee = round($basePrice * 0.05, 2); // 5% platform fee
$taxAmount = round(($basePrice + $convenienceFee) * 0.18, 2); // 18% GST
$totalAmount = $basePrice + $convenienceFee + $taxAmount;
?>

<div class="dashboard-main">
    <?php require_once __DIR__ . '/../includes/topbar.php'; ?>

    <div class="dashboard-content">
        <div class="container-fluid max-width-1000">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h3 class="fw-bold mb-1">📋 Booking Summary</h3>
                    <p class="text-muted mb-0">Review your service details before proceeding to payment simulation.</p>
                </div>
                <div>
                    <?= getPaymentStatusBadge($booking['payment_status']) ?>
                </div>
            </div>

            <div class="row g-4">
                <!-- Service & Booking Details -->
                <div class="col-lg-7">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-bottom py-3">
                            <h5 class="mb-0 text-primary"><i class="bi bi-info-circle me-2"></i>Service & Provider Info</h5>
                        </div>
                        <div class="card-body p-4">
                            <h4 class="fw-bold text-dark mb-2"><?= htmlspecialchars($booking['service_title']) ?></h4>
                            <p class="text-muted small mb-3">
                                <span class="badge bg-light text-dark border me-2"><?= htmlspecialchars($booking['category']) ?></span>
                                Provided by <strong><?= htmlspecialchars($booking['provider_name']) ?></strong>
                            </p>
                            
                            <hr class="text-muted opacity-25">

                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <label class="text-muted small d-block">Booking ID</label>
                                    <span class="fw-bold">#BK-<?= str_pad($booking['id'], 5, '0', STR_PAD_LEFT) ?></span>
                                </div>
                                <div class="col-sm-6">
                                    <label class="text-muted small d-block">Scheduled Date</label>
                                    <span class="fw-bold text-dark"><i class="bi bi-calendar3 me-1"></i><?= date('F j, Y', strtotime($booking['booking_date'])) ?></span>
                                </div>
                                <?php if (!empty($booking['notes'])): ?>
                                    <div class="col-12">
                                        <label class="text-muted small d-block">Special Instructions / Notes</label>
                                        <div class="p-2 bg-light rounded text-dark small"><?= nl2br(htmlspecialchars($booking['notes'])) ?></div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Breakdown & Action -->
                <div class="col-lg-5">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-primary text-white py-3">
                            <h5 class="mb-0"><i class="bi bi-receipt me-2"></i>Payment Breakdown</h5>
                        </div>
                        <div class="card-body p-4">
                            <ul class="list-group list-group-flush mb-3">
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0 bg-transparent">
                                    <span class="text-muted">Base Service Fee</span>
                                    <span class="fw-semibold">₹<?= number_format($basePrice, 2) ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0 bg-transparent">
                                    <span class="text-muted">Platform & Booking Fee (5%)</span>
                                    <span class="fw-semibold">₹<?= number_format($convenienceFee, 2) ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0 bg-transparent">
                                    <span class="text-muted">GST & Taxes (18%)</span>
                                    <span class="fw-semibold">₹<?= number_format($taxAmount, 2) ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0 bg-transparent border-top border-2 pt-3">
                                    <strong class="fs-5 text-dark">Total Amount</strong>
                                    <strong class="fs-4 text-primary">₹<?= number_format($totalAmount, 2) ?></strong>
                                </li>
                            </ul>

                            <div class="alert alert-info border-0 small mb-4">
                                <i class="bi bi-shield-lock me-1"></i> <strong>Simulation Mode:</strong> No real money will be deducted. All methods are simulated for project testing.
                            </div>

                            <?php if ($booking['payment_status'] === 'paid'): ?>
                                <div class="alert alert-success text-center py-3">
                                    <i class="bi bi-check-circle-fill fs-3 d-block mb-1"></i>
                                    <strong>Payment Completed</strong>
                                </div>
                                <a href="payment_history.php" class="btn btn-outline-primary w-100">View Payment History</a>
                            <?php elseif ($booking['booking_status'] === 'accepted'): ?>
                                <a href="payment_select.php?booking_id=<?= $booking['id'] ?>" class="btn btn-success btn-lg w-100 fw-bold py-3 shadow-sm">
                                    Proceed to Payment <i class="bi bi-arrow-right ms-2"></i>
                                </a>
                            <?php elseif ($booking['booking_status'] === 'pending'): ?>
                                <div class="alert alert-warning border-0 small text-center py-3 mb-0">
                                    <i class="bi bi-clock-history fs-4 d-block mb-1"></i>
                                    <strong>Awaiting Provider Acceptance</strong><br>
                                    Payment option will be enabled once the service provider accepts your booking request.
                                </div>
                            <?php else: ?>
                                <div class="alert alert-secondary border-0 text-center py-3 mb-0">
                                    <i class="bi bi-slash-circle fs-4 d-block mb-1"></i>
                                    <strong>Booking <?= ucfirst($booking['booking_status']) ?></strong><br>
                                    Payment is disabled for this booking.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/dashboard_footer.php'; ?>
