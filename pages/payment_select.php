<?php
$pageTitle = 'Select Payment Method';
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

$stmt = $pdo->prepare("
    SELECT b.*, s.title AS service_title, s.price
    FROM bookings b
    JOIN services s ON b.service_id = s.id
    WHERE b.id = ? AND b.user_id = ?
");
$stmt->execute([$bookingId, $_SESSION['user_id']]);
$booking = $stmt->fetch();

if (!$booking) {
    setFlash('danger', 'Booking not found.');
    header('Location: booking_history.php');
    exit;
}

if ($booking['payment_status'] === 'paid') {
    setFlash('info', 'This booking is already paid.');
    header('Location: booking_summary.php?booking_id=' . $bookingId);
    exit;
}

if ($booking['booking_status'] !== 'accepted') {
    setFlash('warning', 'Payment is only permitted after the provider accepts your booking request.');
    header('Location: booking_summary.php?booking_id=' . $bookingId);
    exit;
}

// Total price calculation
$basePrice = (float)$booking['price'];
$convenienceFee = round($basePrice * 0.05, 2);
$taxAmount = round(($basePrice + $convenienceFee) * 0.18, 2);
$totalAmount = $basePrice + $convenienceFee + $taxAmount;
?>

<div class="dashboard-main">
    <?php require_once __DIR__ . '/../includes/topbar.php'; ?>

    <div class="dashboard-content">
        <div class="container-fluid max-width-900">
            <div class="mb-4">
                <a href="booking_summary.php?booking_id=<?= $bookingId ?>" class="text-decoration-none text-muted mb-2 d-inline-block">
                    <i class="bi bi-arrow-left me-1"></i> Back to Summary
                </a>
                <h3 class="fw-bold mb-1">💳 Simulated Payment Checkout</h3>
                <p class="text-muted">Choose your preferred payment gateway simulation mode.</p>
            </div>

            <div class="row g-4">
                <!-- Payment Form Options -->
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white py-3 border-bottom">
                            <ul class="nav nav-pills card-header-pills nav-justified" id="paymentTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active fw-semibold" id="upi-tab" data-bs-toggle="tab" data-bs-target="#upi" type="button" role="tab">
                                        <i class="bi bi-qr-code-scan me-1"></i> UPI
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link fw-semibold" id="card-tab" data-bs-toggle="tab" data-bs-target="#card" type="button" role="tab">
                                        <i class="bi bi-credit-card me-1"></i> Card
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link fw-semibold" id="netbanking-tab" data-bs-toggle="tab" data-bs-target="#netbanking" type="button" role="tab">
                                        <i class="bi bi-bank me-1"></i> Net Banking
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link fw-semibold" id="wallet-tab" data-bs-toggle="tab" data-bs-target="#wallet" type="button" role="tab">
                                        <i class="bi bi-wallet2 me-1"></i> Wallet
                                    </button>
                                </li>
                            </ul>
                        </div>

                        <div class="card-body p-4">
                            <form action="payment_process.php" method="POST" id="paymentForm">
                                <input type="hidden" name="booking_id" value="<?= $bookingId ?>">
                                <input type="hidden" name="amount" value="<?= $totalAmount ?>">
                                <input type="hidden" name="payment_method" id="selectedMethod" value="UPI">

                                <div class="tab-content" id="paymentTabsContent">
                                    <!-- UPI Tab -->
                                    <div class="tab-pane fade show active" id="upi" role="tabpanel">
                                        <h6 class="fw-bold mb-3">Pay via UPI App / VPA</h6>
                                        <div class="mb-3">
                                            <label for="upi_id" class="form-label small text-muted">Virtual Private Address (VPA)</label>
                                            <input type="text" class="form-control form-control-lg" id="upi_id" name="upi_id" placeholder="e.g. user@okaxis or 9876543210@paytm" value="demo.user@upi">
                                            <div class="form-text">You can enter any dummy UPI ID for demonstration.</div>
                                        </div>
                                        <div class="d-flex gap-2 mb-3">
                                            <span class="badge bg-light text-dark border p-2"><i class="bi bi-phone me-1"></i> Google Pay</span>
                                            <span class="badge bg-light text-dark border p-2"><i class="bi bi-phone me-1"></i> PhonePe</span>
                                            <span class="badge bg-light text-dark border p-2"><i class="bi bi-phone me-1"></i> Paytm</span>
                                        </div>
                                    </div>

                                    <!-- Credit / Debit Card Tab -->
                                    <div class="tab-pane fade" id="card" role="tabpanel">
                                        <h6 class="fw-bold mb-3">Pay using Credit or Debit Card</h6>
                                        <div class="mb-3">
                                            <label class="form-label small text-muted">Card Type</label>
                                            <select class="form-select" name="card_type" id="cardTypeSelect">
                                                <option value="Credit Card">Credit Card</option>
                                                <option value="Debit Card">Debit Card</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="card_number" class="form-label small text-muted">Card Number</label>
                                            <input type="text" class="form-control form-control-lg" id="card_number" name="card_number" placeholder="4532 •••• •••• 8892" maxlength="19" value="4532 8812 9901 4242">
                                        </div>
                                        <div class="row g-3 mb-3">
                                            <div class="col-6">
                                                <label for="card_expiry" class="form-label small text-muted">Expiry (MM/YY)</label>
                                                <input type="text" class="form-control" id="card_expiry" name="card_expiry" placeholder="12/28" value="12/28">
                                            </div>
                                            <div class="col-6">
                                                <label for="card_cvv" class="form-label small text-muted">CVV</label>
                                                <input type="password" class="form-control" id="card_cvv" name="card_cvv" placeholder="•••" maxlength="3" value="123">
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="card_name" class="form-label small text-muted">Cardholder Name</label>
                                            <input type="text" class="form-control" id="card_name" name="card_name" placeholder="John Doe" value="<?= htmlspecialchars($_SESSION['user_name']) ?>">
                                        </div>
                                    </div>

                                    <!-- Net Banking Tab -->
                                    <div class="tab-pane fade" id="netbanking" role="tabpanel">
                                        <h6 class="fw-bold mb-3">Select Internet Banking Bank</h6>
                                        <div class="mb-3">
                                            <label for="bank_name" class="form-label small text-muted">Choose Bank</label>
                                            <select class="form-select form-select-lg" id="bank_name" name="bank_name">
                                                <option value="HDFC Bank">HDFC Bank</option>
                                                <option value="State Bank of India (SBI)">State Bank of India (SBI)</option>
                                                <option value="ICICI Bank">ICICI Bank</option>
                                                <option value="Axis Bank">Axis Bank</option>
                                                <option value="Kotak Mahindra Bank">Kotak Mahindra Bank</option>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Wallet Tab -->
                                    <div class="tab-pane fade" id="wallet" role="tabpanel">
                                        <h6 class="fw-bold mb-3">Select Digital Wallet</h6>
                                        <div class="mb-3">
                                            <label for="wallet_provider" class="form-label small text-muted">Wallet Provider</label>
                                            <select class="form-select form-select-lg" id="wallet_provider" name="wallet_provider">
                                                <option value="Paytm Wallet">Paytm Wallet</option>
                                                <option value="Amazon Pay">Amazon Pay</option>
                                                <option value="Mobikwik">Mobikwik</option>
                                                <option value="Freecharge">Freecharge</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <hr class="my-4">

                                <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold py-3 shadow-sm" id="paySubmitBtn">
                                    <i class="bi bi-lock-fill me-2"></i> Pay Simulated ₹<?= number_format($totalAmount, 2) ?>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Summary Sidebar -->
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm bg-light">
                        <div class="card-body p-4">
                            <h6 class="fw-bold text-uppercase text-muted mb-3">Order Summary</h6>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Service</span>
                                <span class="fw-semibold text-end"><?= htmlspecialchars($booking['service_title']) ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-3">
                                <span class="text-muted">Total Payable</span>
                                <span class="fw-bold fs-5 text-primary">₹<?= number_format($totalAmount, 2) ?></span>
                            </div>
                            <hr>
                            <div class="small text-muted">
                                <p class="mb-1"><i class="bi bi-shield-check text-success me-1"></i> 256-Bit SSL Mock Encryption</p>
                                <p class="mb-0"><i class="bi bi-info-circle text-primary me-1"></i> Simulation Mode: Auto-generates unique transaction ID.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const upiTab = document.getElementById('upi-tab');
    const cardTab = document.getElementById('card-tab');
    const netbankingTab = document.getElementById('netbanking-tab');
    const walletTab = document.getElementById('wallet-tab');
    const selectedMethodInput = document.getElementById('selectedMethod');
    const cardTypeSelect = document.getElementById('cardTypeSelect');

    upiTab.addEventListener('click', () => selectedMethodInput.value = 'UPI');
    cardTab.addEventListener('click', () => selectedMethodInput.value = cardTypeSelect.value);
    cardTypeSelect.addEventListener('change', () => {
        if(cardTab.classList.contains('active')) {
            selectedMethodInput.value = cardTypeSelect.value;
        }
    });
    netbankingTab.addEventListener('click', () => selectedMethodInput.value = 'Net Banking');
    walletTab.addEventListener('click', () => selectedMethodInput.value = 'Wallet');
});
</script>

<?php require_once __DIR__ . '/../includes/dashboard_footer.php'; ?>
