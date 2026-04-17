<?php
$pageTitle = 'My Reviews';
require_once __DIR__ . '/../includes/dashboard_header.php';
requireRole('provider');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/sidebar_provider.php';

$providerId = $_SESSION['user_id'];

// Fetch all reviews for this provider's services
$stmt = $pdo->prepare("SELECT r.*, s.title AS service_title, u.name AS customer_name 
                       FROM reviews r 
                       JOIN services s ON r.service_id = s.id 
                       JOIN users u ON r.user_id = u.id 
                       WHERE r.provider_id = ? 
                       ORDER BY r.created_at DESC");
$stmt->execute([$providerId]);
$reviews = $stmt->fetchAll();

// Calculate average
$avgRating = 0;
if (count($reviews) > 0) {
    $sum = array_sum(array_column($reviews, 'rating'));
    $avgRating = $sum / count($reviews);
}
?>

<div class="dashboard-main">
  <?php require_once __DIR__ . '/../includes/topbar.php'; ?>
  <div class="dashboard-content">

    <div class="d-flex justify-content-between align-items-center mb-4">
      <h5 class="mb-0 fw-bold"><i class="bi bi-star-fill text-warning me-2"></i> Review Analytics</h5>
      <?php if (count($reviews) > 0): ?>
          <span class="badge bg-light text-dark px-3 py-2 border rounded-pill shadow-sm fs-6">
              Average Rating: <strong class="ms-1"><i class="bi bi-star-fill text-warning"></i> <?= number_format($avgRating, 1) ?></strong> (<?= count($reviews) ?> total)
          </span>
      <?php endif; ?>
    </div>

    <div class="card shadow-sm border-0">
      <div class="card-body p-0">
        <?php if (empty($reviews)): ?>
          <div class="text-center py-5">
            <i class="bi bi-star display-4 text-muted mb-3 d-block"></i>
            <h5 class="text-muted">No reviews yet</h5>
            <p class="text-muted">Complete bookings successfully to earn ratings from customers.</p>
          </div>
        <?php else: ?>
          <div class="list-group list-group-flush">
            <?php foreach ($reviews as $rev): ?>
              <div class="list-group-item p-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                  <h6 class="mb-0 fw-bold text-dark"><?= htmlspecialchars($rev['service_title']) ?></h6>
                  <small class="text-muted"><?= date('M d, Y', strtotime($rev['created_at'])) ?></small>
                </div>
                
                <div class="d-flex align-items-center mb-3">
                  <div class="text-warning me-3">
                    <?php for($i=1; $i<=5; $i++): ?>
                        <i class="bi bi-star<?= $i <= $rev['rating'] ? '-fill' : '' ?>"></i>
                    <?php endfor; ?>
                  </div>
                  <span class="badge bg-primary bg-opacity-10 text-primary small">
                      <i class="bi bi-person me-1"></i> <?= htmlspecialchars($rev['customer_name']) ?>
                  </span>
                </div>

                <p class="mb-0 text-secondary" style="font-size: 0.95rem;">
                   "<?= nl2br(htmlspecialchars($rev['comment'])) ?>"
                </p>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/dashboard_footer.php'; ?>
