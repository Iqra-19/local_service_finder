<?php
$pageTitle = 'Provider Dashboard';
require_once __DIR__ . '/../includes/dashboard_header.php';
requireRole('provider');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/sidebar_provider.php';

$providerId = $_SESSION['user_id'];

// 1. Fetch Performance Stats & Financials
$totalServices = 0; $totalBookings = 0; $pendingBookings = 0; $totalEarnings = 0;
$acceptedCount = 0; $completedCount = 0;

$statsStmt = $pdo->prepare("SELECT booking_status, COUNT(*) as count FROM bookings WHERE provider_id = ? GROUP BY booking_status");
$statsStmt->execute([$providerId]);
while ($row = $statsStmt->fetch()) {
    $totalBookings += $row['count'];
    if ($row['booking_status'] === 'pending') $pendingBookings = $row['count'];
    if ($row['booking_status'] === 'accepted') $acceptedCount = $row['count'];
    if ($row['booking_status'] === 'completed') $completedCount = $row['count'];
}

$svcStmt = $pdo->prepare("SELECT COUNT(*) FROM services WHERE provider_id = ?");
$svcStmt->execute([$providerId]);
$totalServices = $svcStmt->fetchColumn();

// Earnings
$earnStmt = $pdo->prepare("SELECT SUM(s.price) FROM bookings b JOIN services s ON b.service_id = s.id WHERE b.provider_id = ? AND b.booking_status = 'completed'");
$earnStmt->execute([$providerId]);
$totalEarnings = $earnStmt->fetchColumn() ?: 0;

// Algorithm derived rates
$acceptanceRate = $totalBookings > 0 ? (($acceptedCount + $completedCount) / $totalBookings) * 100 : 0;
// Completion out of accepted
$completionRate = ($acceptedCount + $completedCount) > 0 ? ($completedCount / ($acceptedCount + $completedCount)) * 100 : 0;

// 2. Fetch Pending Booking Requests directly
$reqStmt = $pdo->prepare("SELECT b.*, s.title as service_title, u.name as customer_name, s.price 
                          FROM bookings b 
                          JOIN services s ON b.service_id = s.id 
                          JOIN users u ON b.user_id = u.id
                          WHERE b.provider_id = ? AND b.booking_status = 'pending' 
                          ORDER BY b.created_at ASC LIMIT 4");
$reqStmt->execute([$providerId]);
$pendingRequests = $reqStmt->fetchAll();

// 3. Fetch Latest Reviews Received
$revStmt = $pdo->prepare("SELECT r.*, s.title as service_title, u.name as customer_name 
                          FROM reviews r JOIN services s ON r.service_id = s.id JOIN users u ON r.user_id = u.id 
                          WHERE r.provider_id = ? ORDER BY r.created_at DESC LIMIT 3");
$revStmt->execute([$providerId]);
$latestReviews = $revStmt->fetchAll();
?>

<div class="dashboard-main">
  <?php require_once __DIR__ . '/../includes/topbar.php'; ?>
  <div class="dashboard-content">
    
    <!-- Top Stats -->
    <div class="row g-4 mb-4">
      <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-primary text-white h-100 p-3 rounded-4 position-relative overflow-hidden">
          <p class="mb-1 text-white-50 text-uppercase fw-bold small">Total Bookings</p>
          <h3 class="display-6 fw-bold mb-0"><?= $totalBookings ?></h3>
          <i class="bi bi-journal-check position-absolute opacity-25" style="font-size: 4rem; right: -5px; bottom: -10px;"></i>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-warning text-dark h-100 p-3 rounded-4 position-relative overflow-hidden">
          <p class="mb-1 text-dark text-opacity-50 text-uppercase fw-bold small">Pending Requests</p>
          <h3 class="display-6 fw-bold mb-0"><?= $pendingBookings ?></h3>
          <i class="bi bi-hourglass-split position-absolute opacity-25" style="font-size: 4rem; right: -5px; bottom: -10px;"></i>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-info text-white h-100 p-3 rounded-4 position-relative overflow-hidden">
          <p class="mb-1 text-white-50 text-uppercase fw-bold small">Active Services</p>
          <h3 class="display-6 fw-bold mb-0"><?= $totalServices ?></h3>
          <i class="bi bi-tools position-absolute opacity-25" style="font-size: 4rem; right: -5px; bottom: -10px;"></i>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-success text-white h-100 p-3 rounded-4 position-relative overflow-hidden">
          <p class="mb-1 text-white-50 text-uppercase fw-bold small">Total Earnings</p>
          <h3 class="display-6 fw-bold mb-0">₹<?= number_format($totalEarnings, 2) ?></h3>
          <i class="bi bi-currency-rupee position-absolute opacity-25" style="font-size: 4rem; right: -5px; bottom: -10px;"></i>
        </div>
      </div>
    </div>

    <div class="row g-4">
      <!-- Left Column: Pending Actions & Performance -->
      <div class="col-lg-7">
        
        <!-- Inline Booking Approval System -->
        <h6 class="text-muted text-uppercase fw-bold mb-3"><i class="bi bi-bell-fill text-danger me-1"></i> Immediate Action Required</h6>
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-0">
                <?php if (empty($pendingRequests)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-check2-circle text-success opacity-50 display-1 mb-3 d-block"></i>
                        <h5 class="fw-bold text-muted">All caught up!</h5>
                        <p class="text-muted">You have resolved all pending booking requests.</p>
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush rounded-4">
                        <?php foreach($pendingRequests as $req): ?>
                            <div class="list-group-item p-4">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h6 class="fw-bold text-dark mb-1"><?= htmlspecialchars($req['service_title']) ?></h6>
                                        <span class="text-muted small"><i class="bi bi-person me-1"></i><?= htmlspecialchars($req['customer_name']) ?> wants to book for <?= date('d M', strtotime($req['booking_date'])) ?></span>
                                    </div>
                                    <span class="fw-bold text-success fs-5">₹<?= number_format($req['price'], 2) ?></span>
                                </div>
                                <div class="mt-3 bg-light p-2 rounded-3 text-end d-flex justify-content-end gap-2">
                                    <!-- Direct Integration Action Forms via POST -->
                                    <a href="update_booking.php?id=<?= $req['id'] ?>&action=accept" class="btn btn-success btn-sm rounded-pill px-3 shadow-sm"><i class="bi bi-check-lg me-1"></i> Accept</a>
                                    <a href="update_booking.php?id=<?= $req['id'] ?>&action=reject" class="btn btn-outline-danger btn-sm rounded-pill px-3"><i class="bi bi-x-lg me-1"></i> Reject</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <div class="card-footer bg-white border-top-0 text-center py-3">
                            <a href="booking_requests.php" class="text-primary fw-bold text-decoration-none">View All Queue <i class="bi bi-arrow-right ms-1"></i></a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

      </div>
      
      <!-- Right Column: Performance Analytics & Reviews -->
      <div class="col-lg-5">
        
        <!-- Performance Analytics -->
        <h6 class="text-muted text-uppercase fw-bold mb-3"><i class="bi bi-bar-chart-fill text-primary me-1"></i> Operational Health</h6>
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="fw-medium text-dark small">Acceptance Rate</span>
                        <span class="fw-bold text-primary"><?= number_format($acceptanceRate, 0) ?>%</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-primary rounded-pill" role="progressbar" style="width: <?= $acceptanceRate ?>%;"></div>
                    </div>
                    <small class="text-muted" style="font-size:0.75rem;">Total requests you accepted vs total received.</small>
                </div>
                <div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="fw-medium text-dark small">Completion Rate</span>
                        <span class="fw-bold text-success"><?= number_format($completionRate, 0) ?>%</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-success rounded-pill" role="progressbar" style="width: <?= $completionRate ?>%;"></div>
                    </div>
                    <small class="text-muted" style="font-size:0.75rem;">Services marked completed out of total accepted.</small>
                </div>
            </div>
        </div>

        <!-- Latest Received Reviews -->
        <h6 class="text-muted text-uppercase fw-bold mb-3"><i class="bi bi-star-fill text-warning me-1"></i> Recent Feedback</h6>
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-0">
                <?php if(empty($latestReviews)): ?>
                    <p class="text-center text-muted py-4 mb-0">No reviews generated yet.</p>
                <?php else: ?>
                    <div class="list-group list-group-flush rounded-4">
                        <?php foreach($latestReviews as $rev): ?>
                            <div class="list-group-item p-3 bg-light border-bottom">
                                <div class="d-flex justify-content-between mb-1">
                                    <strong class="text-dark small d-block text-truncate" style="max-width:200px;"><?= htmlspecialchars($rev['service_title']) ?></strong>
                                    <div class="text-warning small text-nowrap">
                                        <i class="bi bi-star-fill"></i> <?= number_format($rev['rating'],1) ?>
                                    </div>
                                </div>
                                <p class="mb-0 text-muted fst-italic" style="font-size: 0.85rem;">"<?= htmlspecialchars(mb_strimwidth($rev['comment'], 0, 70, "...")) ?>"</p>
                            </div>
                        <?php endforeach; ?>
                        <div class="p-2 text-center bg-white rounded-bottom-4">
                            <a href="provider_reviews.php" class="text-decoration-none small fw-bold">Read All Feedback</a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

      </div>
    
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/dashboard_footer.php'; ?>

