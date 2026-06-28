<?php
$pageTitle = 'Processing Payment...';
require_once __DIR__ . '/../includes/dashboard_header.php';
requireRole('user');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/payment_helper.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' && !isset($_GET['txn'])) {
    header('Location: booking_history.php');
    exit;
}

$transactionId = '';
$bookingId = 0;
$amount = 0.00;
$method = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bookingId = filter_input(INPUT_POST, 'booking_id', FILTER_VALIDATE_INT);
    $amount = filter_input(INPUT_POST, 'amount', FILTER_VALIDATE_FLOAT);
    $method = trim($_POST['payment_method'] ?? 'UPI');

    // Extract details based on method
    $details = '';
    if ($method === 'UPI') {
        $details = trim($_POST['upi_id'] ?? 'demo@upi');
    } elseif ($method === 'Credit Card' || $method === 'Debit Card') {
        $details = trim($_POST['card_number'] ?? '4532881299014242');
    } elseif ($method === 'Net Banking') {
        $details = trim($_POST['bank_name'] ?? 'HDFC Bank');
    } elseif ($method === 'Wallet') {
        $details = trim($_POST['wallet_provider'] ?? 'Paytm Wallet');
    }

    if (!$bookingId || !$amount) {
        setFlash('danger', 'Invalid transaction details.');
        header('Location: booking_history.php');
        exit;
    }

    // Fetch booking to verify
    $stmt = $pdo->prepare("SELECT id, provider_id, user_id FROM bookings WHERE id = ? AND user_id = ?");
    $stmt->execute([$bookingId, $_SESSION['user_id']]);
    $booking = $stmt->fetch();

    if (!$booking) {
        setFlash('danger', 'Booking record not found.');
        header('Location: booking_history.php');
        exit;
    }

    // Generate Unique Transaction ID
    $transactionId = generateTransactionId();

    try {
        $pdo->beginTransaction();

        // Insert Payment Record
        $payStmt = $pdo->prepare("
            INSERT INTO payments (transaction_id, booking_id, user_id, provider_id, amount, payment_method, payment_status, payment_details)
            VALUES (?, ?, ?, ?, ?, ?, 'Success', ?)
        ");
        $payStmt->execute([
            $transactionId,
            $bookingId,
            $_SESSION['user_id'],
            $booking['provider_id'],
            $amount,
            $method,
            $details
        ]);

        // Update Booking Status to Confirmed and Payment Status to Paid
        $bookStmt = $pdo->prepare("
            UPDATE bookings 
            SET payment_status = 'paid', booking_status = 'confirmed', updated_at = CURRENT_TIMESTAMP 
            WHERE id = ?
        ");
        $bookStmt->execute([$bookingId]);

        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        setFlash('danger', 'Payment simulation failed: ' . $e->getMessage());
        header('Location: payment_select.php?booking_id=' . $bookingId);
        exit;
    }
}
?>

<div class="container py-5 text-center">
    <div class="row justify-content-center py-5">
        <div class="col-md-6 col-lg-5">
            <div class="card border-0 shadow-lg p-4 rounded-4 position-relative overflow-hidden">
                <div class="card-body py-5">
                    <!-- Animated Spinner -->
                    <div class="mb-4">
                        <div class="spinner-border text-primary" style="width: 4rem; height: 4rem;" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>

                    <h4 class="fw-bold mb-2">Processing Your Payment</h4>
                    <p class="text-muted mb-4">Please do not refresh or close this window...</p>

                    <!-- Simulation Steps Indicator -->
                    <div class="bg-light p-3 rounded-3 mb-4 text-start">
                        <div class="d-flex align-items-center mb-2" id="step1">
                            <i class="bi bi-check-circle-fill text-success me-2 fs-5"></i>
                            <span class="small fw-semibold">Connecting to Payment Gateway</span>
                        </div>
                        <div class="d-flex align-items-center mb-2 text-muted" id="step2">
                            <div class="spinner-border spinner-border-sm me-2 text-primary" role="status"></div>
                            <span class="small fw-semibold">Verifying Credentials & Processing Funds</span>
                        </div>
                        <div class="d-flex align-items-center text-muted" id="step3">
                            <i class="bi bi-circle me-2 fs-5"></i>
                            <span class="small fw-semibold">Generating Transaction Receipt</span>
                        </div>
                    </div>

                    <div class="d-flex justify-content-center align-items-center gap-2 text-muted small">
                        <i class="bi bi-shield-lock-fill text-primary"></i> 256-Bit SSL Encrypted Transaction
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const step2 = document.getElementById('step2');
    const step3 = document.getElementById('step3');

    // Simulated step transitions during the 2.5 second wait
    setTimeout(() => {
        step2.innerHTML = '<i class="bi bi-check-circle-fill text-success me-2 fs-5"></i><span class="small fw-semibold">Funds Verified & Authorized</span>';
        step3.innerHTML = '<div class="spinner-border spinner-border-sm me-2 text-primary" role="status"></div><span class="small fw-semibold">Generating Transaction Receipt</span>';
    }, 1200);

    // Final Redirect after 2.5 seconds (2500ms)
    setTimeout(() => {
        window.location.href = 'payment_success.php?transaction_id=<?= urlencode($transactionId) ?>';
    }, 2500);
});
</script>

<?php require_once __DIR__ . '/../includes/dashboard_footer.php'; ?>
