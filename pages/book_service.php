<?php
$pageTitle = 'Book Service';
require_once __DIR__ . '/../includes/dashboard_header.php';
requireRole('user');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/sidebar_user.php';

$serviceId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$service = null;

if ($serviceId) {
    $stmt = $pdo->prepare("SELECT s.*, u.name AS provider_name FROM services s JOIN users u ON s.provider_id = u.id WHERE s.id = ? AND s.status = 'active'");
    $stmt->execute([$serviceId]);
    $service = $stmt->fetch();
}

if (!$service) {
    setFlash('danger', 'Service not found or inactive.');
    header('Location: browse_services.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bookingDate = trim($_POST['booking_date'] ?? '');
    $notes = trim($_POST['notes'] ?? '');

    if (empty($bookingDate)) {
        setFlash('danger', 'Please select a booking date.');
    } elseif (strtotime($bookingDate) < strtotime('today')) {
        setFlash('danger', 'Booking date cannot be in the past.');
    } else {
        // Prevent booking own service (though unlikely if provider_id differs from user session, but as a safety check)
        if ($service['provider_id'] === $_SESSION['user_id']) {
            setFlash('danger', 'You cannot book your own service.');
        } else {
            // Check for duplicate pending/accepted booking
            $checkStmt = $pdo->prepare("SELECT id FROM bookings WHERE user_id = ? AND service_id = ? AND booking_date = ? AND status IN ('pending', 'accepted')");
            $checkStmt->execute([$_SESSION['user_id'], $serviceId, $bookingDate]);

            if ($checkStmt->fetch()) {
                setFlash('danger', 'You already have an active booking for this service on that date.');
            } else {
                $insertStmt = $pdo->prepare("INSERT INTO bookings (user_id, provider_id, service_id, booking_date, notes, status) VALUES (?, ?, ?, ?, ?, 'pending')");
                $insertStmt->execute([
                    $_SESSION['user_id'],
                    $service['provider_id'],
                    $serviceId,
                    $bookingDate,
                    htmlspecialchars($notes, ENT_QUOTES, 'UTF-8')
                ]);
                setFlash('success', 'Booking request submitted successfully!');
                header('Location: booking_history.php');
                exit;
            }
        }
    }
}
?>

<div class="dashboard-main">
    <?php require_once __DIR__ . '/../includes/topbar.php'; ?>

    <div class="dashboard-content">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <!-- Service Summary -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h4 class="fw-bold mb-1"><?= htmlspecialchars($service['title']) ?></h4>
                                <p class="text-muted mb-2">
                                    <i class="bi bi-person me-1"></i><?= htmlspecialchars($service['provider_name']) ?>
                                    <span class="mx-2">•</span>
                                    <span class="badge bg-light text-primary"><?= htmlspecialchars($service['category']) ?></span>
                                </p>
                                <p class="mb-0"><?= htmlspecialchars($service['description']) ?></p>
                            </div>
                            <div class="text-end">
                                <span class="fs-4 fw-bold text-primary">₹<?= number_format($service['price'], 2) ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Booking Form -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-primary text-white py-3">
                        <h5 class="mb-0"><i class="bi bi-calendar-check me-2"></i>Book This Service</h5>
                    </div>
                    <div class="card-body p-4">
                        <form method="POST" action="">
                            <div class="mb-4">
                                <label for="booking_date" class="form-label fw-semibold">Preferred Date</label>
                                <input type="date" class="form-control form-control-lg" id="booking_date" name="booking_date"
                                       min="<?= date('Y-m-d') ?>" required
                                       value="<?= htmlspecialchars($_POST['booking_date'] ?? '') ?>">
                            </div>
                            <div class="mb-4">
                                <label for="notes" class="form-label fw-semibold">Additional Notes <span class="text-muted fw-normal">(optional)</span></label>
                                <textarea class="form-control" id="notes" name="notes" rows="3"
                                          maxlength="500"
                                          placeholder="Any special requirements or preferences..."><?= htmlspecialchars($_POST['notes'] ?? '') ?></textarea>
                            </div>
                            <div class="d-flex gap-3">
                                <button type="submit" class="btn btn-primary btn-lg px-5">
                                    <i class="bi bi-check2-circle me-2"></i>Confirm Booking
                                </button>
                                <a href="browse_services.php" class="btn btn-outline-secondary btn-lg">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/dashboard_footer.php'; ?>
