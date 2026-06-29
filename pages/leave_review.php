<?php
$pageTitle = 'Leave a Review';
require_once __DIR__ . '/../includes/dashboard_header.php';
requireRole('user');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/sidebar_user.php';

$bookingId = filter_input(INPUT_GET, 'booking_id', FILTER_VALIDATE_INT);
if (!$bookingId) {
    setFlash('danger', 'Invalid booking ID.');
    header('Location: booking_history.php');
    exit;
}

// Ensure user owns booking and it is completed
$stmt = $pdo->prepare("SELECT b.*, s.title AS service_title FROM bookings b JOIN services s ON b.service_id = s.id WHERE b.id = ? AND b.user_id = ?");
$stmt->execute([$bookingId, $_SESSION['user_id']]);
$booking = $stmt->fetch();

if (!$booking) {
    setFlash('danger', 'Booking not found.');
    header('Location: booking_history.php');
    exit;
}

if ($booking['booking_status'] !== 'completed') {
    setFlash('danger', 'You can only review completed services.');
    header('Location: booking_history.php');
    exit;
}

// Check if review already exists
$checkStmt = $pdo->prepare("SELECT id FROM reviews WHERE booking_id = ?");
$checkStmt->execute([$bookingId]);
if ($checkStmt->fetch()) {
    setFlash('info', 'You have already reviewed this service.');
    header('Location: booking_history.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken()) {
        setFlash('danger', 'Invalid CSRF security token. Please try again.');
    } else {
        $rating = filter_input(INPUT_POST, 'rating', FILTER_VALIDATE_INT);
        $comment = trim($_POST['comment'] ?? '');

        if (!$rating || $rating < 1 || $rating > 5) {
            setFlash('danger', 'Please provide a valid rating between 1 and 5.');
        } else {
            $insertStmt = $pdo->prepare("INSERT INTO reviews (booking_id, user_id, provider_id, service_id, rating, comment) VALUES (?, ?, ?, ?, ?, ?)");
            $insertStmt->execute([
                $bookingId,
                $_SESSION['user_id'],
                $booking['provider_id'],
                $booking['service_id'],
                $rating,
                htmlspecialchars($comment, ENT_QUOTES, 'UTF-8')
            ]);
            setFlash('success', 'Thank you! Your review has been submitted.');
            header('Location: booking_history.php');
            exit;
        }
    }
}
?>

<div class="dashboard-main">
    <?php require_once __DIR__ . '/../includes/topbar.php'; ?>
    <div class="dashboard-content">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-primary text-white py-3">
                        <h5 class="mb-0"><i class="bi bi-star-fill me-2"></i>Review Service</h5>
                    </div>
                    <div class="card-body p-4">
                        <h4 class="mb-4"><?= htmlspecialchars($booking['service_title']) ?></h4>
                        <form method="POST">
                            <?= csrfInput() ?>
                            <div class="mb-4">
                                <label class="form-label fw-bold">Rating</label>
                                <select name="rating" class="form-select form-select-lg" required>
                                    <option value="">Select Rating</option>
                                    <option value="5">⭐⭐⭐⭐⭐ (5/5) - Excellent</option>
                                    <option value="4">⭐⭐⭐⭐ (4/5) - Very Good</option>
                                    <option value="3">⭐⭐⭐ (3/5) - Good</option>
                                    <option value="2">⭐⭐ (2/5) - Fair</option>
                                    <option value="1">⭐ (1/5) - Poor</option>
                                </select>
                            </div>
                            <div class="mb-4">
                                <label class="form-label fw-bold">Comment (Optional)</label>
                                <textarea name="comment" class="form-control" rows="4" placeholder="How was your experience?"></textarea>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary btn-lg"><i class="bi bi-send me-1"></i> Submit Review</button>
                                <a href="booking_history.php" class="btn btn-outline-secondary btn-lg">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/dashboard_footer.php'; ?>

