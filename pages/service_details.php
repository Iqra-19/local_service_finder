<?php
$pageTitle = 'Service Details';
require_once __DIR__ . '/../includes/dashboard_header.php';
requireRole('user');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/sidebar_user.php';

$serviceId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$serviceId) {
    header('Location: browse_services.php');
    exit;
}

$stmt = $pdo->prepare("SELECT s.*, u.name AS provider_name,
               (SELECT AVG(rating) FROM reviews WHERE service_id = s.id) AS avg_rating,
               (SELECT COUNT(*) FROM reviews WHERE service_id = s.id) AS total_reviews
        FROM services s 
        JOIN users u ON s.provider_id = u.id 
        WHERE s.id = ? AND s.status = 'active'");
$stmt->execute([$serviceId]);
$service = $stmt->fetch();

if (!$service) {
    setFlash('danger', 'Service not found or currently inactive.');
    header('Location: browse_services.php');
    exit;
}

// Fetch all reviews
$revStmt = $pdo->prepare("SELECT r.*, u.name AS author_name FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.service_id = ? ORDER BY r.created_at DESC");
$revStmt->execute([$serviceId]);
$reviews = $revStmt->fetchAll();
?>

<div class="dashboard-main">
    <?php require_once __DIR__ . '/../includes/topbar.php'; ?>

    <div class="dashboard-content">
        <div class="row">
            <!-- Left Column: Details -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4 p-md-5">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="badge bg-primary text-white px-3 py-2 fs-6 rounded-pill"><?= htmlspecialchars($service['category']) ?></span>
                            <?php if ($service['avg_rating']): ?>
                                <span class="badge bg-warning text-dark px-3 py-2 fs-6 rounded-pill">
                                    <i class="bi bi-star-fill text-dark me-1"></i>
                                    <?= number_format($service['avg_rating'], 1) ?> (<?= $service['total_reviews'] ?>)
                                </span>
                            <?php endif; ?>
                        </div>

                        <h2 class="fw-bold mb-3"><?= htmlspecialchars($service['title']) ?></h2>
                        <div class="d-flex align-items-center mb-4 pb-4 border-bottom">
                            <div class="bg-light rounded-circle p-3 me-3 text-secondary">
                                <i class="bi bi-person-fill fs-4"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 text-muted">Service Provider</h6>
                                <p class="mb-0 fw-semibold fs-5"><?= htmlspecialchars($service['provider_name']) ?></p>
                            </div>
                        </div>

                        <h5 class="fw-bold mb-3">Description</h5>
                        <p class="text-secondary lh-lg mb-0" style="font-size: 1.05rem;">
                            <?= nl2br(htmlspecialchars($service['description'])) ?>
                        </p>
                    </div>
                </div>

                <!-- Reviews Section -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom py-3 p-4">
                        <h5 class="mb-0 fw-bold"><i class="bi bi-chat-left-text me-2 text-primary"></i> Customer Reviews (<?= count($reviews) ?>)</h5>
                    </div>
                    <div class="card-body p-4">
                        <?php if (empty($reviews)): ?>
                            <div class="text-center py-4">
                                <i class="bi bi-star text-muted fs-1 mb-2 d-block"></i>
                                <p class="text-muted">No reviews yet for this service.</p>
                            </div>
                        <?php else: ?>
                            <div class="review-list">
                                <?php foreach ($reviews as $rev): ?>
                                    <div class="border-bottom pb-4 mb-4 last-child-no-border">
                                        <div class="d-flex justify-content-between mb-2">
                                            <h6 class="fw-bold mb-0"><?= htmlspecialchars($rev['author_name']) ?></h6>
                                            <small class="text-muted"><?= date('M d, Y', strtotime($rev['created_at'])) ?></small>
                                        </div>
                                        <div class="text-warning mb-2">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="bi bi-star<?= $i <= $rev['rating'] ? '-fill' : '' ?>"></i>
                                            <?php endfor; ?>
                                        </div>
                                        <p class="text-secondary mb-0"><?= nl2br(htmlspecialchars($rev['comment'])) ?></p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Right Column: Booking Card -->
            <div class="col-lg-4 mt-4 mt-lg-0">
                <div class="card border-0 shadow-sm sticky-top" style="top: 20px;">
                    <div class="card-body p-4 text-center border-bottom">
                        <h6 class="text-muted text-uppercase mb-2">Service Price</h6>
                        <h2 class="text-primary fw-bold mb-0">₹<?= number_format($service['price'], 2) ?></h2>
                    </div>
                    <div class="card-body p-4">
                        <p class="text-secondary small mb-4 text-center">Price indicated may vary depending on specific requirements. Consult provider upon booking.</p>
                        <a href="book_service.php?id=<?= $service['id'] ?>" class="btn btn-primary btn-lg w-100 fw-medium shadow-sm">
                            <i class="bi bi-calendar-check me-2"></i> Book This Service
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.last-child-no-border:last-child { border-bottom: 0 !important; margin-bottom: 0 !important; padding-bottom: 0 !important; }
</style>

<?php require_once __DIR__ . '/../includes/dashboard_footer.php'; ?>
