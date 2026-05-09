<?php
$pageTitle = 'Customer Dashboard';
require_once __DIR__ . '/../includes/dashboard_header.php';
requireRole('user');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/sidebar_user.php';

$userId = $_SESSION['user_id'];

// 1. Fetch Stats
$statsStmt = $pdo->prepare("SELECT booking_status, COUNT(*) as count FROM bookings WHERE user_id = ? GROUP BY booking_status");
$statsStmt->execute([$userId]);
$stats = ['total' => 0, 'confirmed' => 0, 'pending' => 0];
while ($row = $statsStmt->fetch()) {
    $stats['total'] += $row['count'];
    if ($row['booking_status'] === 'accepted' || $row['booking_status'] === 'completed') $stats['confirmed'] += $row['count'];
    if ($row['booking_status'] === 'pending') $stats['pending'] += $row['count'];
}

// 2. Fetch Active/Upcoming Bookings (Pending or Accepted targeting today or future)
$upcomingStmt = $pdo->prepare("SELECT b.*, s.title AS service_title, s.price, u.name as provider_name 
                               FROM bookings b 
                               JOIN services s ON b.service_id = s.id 
                               JOIN users u ON s.provider_id = u.id
                               WHERE b.user_id = ? AND b.booking_status IN ('pending', 'accepted') AND b.booking_date >= CURDATE()
                               ORDER BY b.booking_date ASC LIMIT 3");
$upcomingStmt->execute([$userId]);
$upcomingBookings = $upcomingStmt->fetchAll();

// 3. Fetch Recent Activity / Past items for 'Book Again'
$recentStmt = $pdo->prepare("SELECT b.*, s.title AS service_title 
                             FROM bookings b 
                             JOIN services s ON b.service_id = s.id 
                             WHERE b.user_id = ? AND b.booking_status IN ('completed', 'rejected', 'cancelled')
                             ORDER BY b.updated_at DESC LIMIT 4");
$recentStmt->execute([$userId]);
$recentBookings = $recentStmt->fetchAll();

// 4. Fetch Top Rated Services System-wide
$topStmt = $pdo->query("SELECT s.*, u.name as provider_name,
                        (SELECT AVG(rating) FROM reviews WHERE service_id = s.id) as avg_rating 
                        FROM services s 
                        JOIN users u ON s.provider_id = u.id 
                        WHERE s.status = 'active'
                        HAVING avg_rating > 3 
                        ORDER BY avg_rating DESC LIMIT 3");
$topServices = $topStmt->fetchAll();
?>
<div class="dashboard-main">
  <?php require_once __DIR__ . '/../includes/topbar.php'; ?>
  <div class="dashboard-content">
    
    <!-- Stats Row -->
    <div class="row g-4 mb-4">
      <div class="col-md-4">
        <div class="card border-0 shadow-sm bg-primary text-white h-100 p-3 overflow-hidden position-relative rounded-4">
          <div class="card-body">
            <h6 class="text-white-50 text-uppercase fw-bold mb-1">Total Bookings</h6>
            <h2 class="display-5 fw-bold mb-0"><?= $stats['total'] ?></h2>
            <i class="bi bi-calendar-check position-absolute text-white opacity-25" style="font-size: 5rem; right: -10px; bottom: -20px;"></i>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card border-0 shadow-sm bg-success text-white h-100 p-3 overflow-hidden position-relative rounded-4">
          <div class="card-body">
            <h6 class="text-white-50 text-uppercase fw-bold mb-1">Confirmed & Completed</h6>
            <h2 class="display-5 fw-bold mb-0"><?= $stats['confirmed'] ?></h2>
            <i class="bi bi-check-circle position-absolute text-white opacity-25" style="font-size: 5rem; right: -10px; bottom: -20px;"></i>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card border-0 shadow-sm bg-warning text-dark h-100 p-3 overflow-hidden position-relative rounded-4">
          <div class="card-body">
            <h6 class="text-dark text-opacity-50 text-uppercase fw-bold mb-1">Pending Requests</h6>
            <h2 class="display-5 fw-bold mb-0"><?= $stats['pending'] ?></h2>
            <i class="bi bi-hourglass-split position-absolute text-dark opacity-25" style="font-size: 5rem; right: -10px; bottom: -20px;"></i>
          </div>
        </div>
      </div>
    </div>

    <div class="row g-4">
      
      <!-- Center Column: Upcoming & Past Activity -->
      <div class="col-lg-8">
        <!-- Upcoming Bookings -->
        <h6 class="text-muted text-uppercase fw-bold mb-3"><i class="bi bi-clock-history me-1"></i> Active & Upcoming</h6>
        <div class="card border-0 shadow-sm rounded-4 mb-4">
          <div class="card-body p-0">
            <?php if (empty($upcomingBookings)): ?>
                <div class="text-center py-5 px-3">
                    <i class="bi bi-calendar-x text-muted opacity-50 display-1 mb-3"></i>
                    <h5 class="text-muted fw-bold">No upcoming bookings</h5>
                    <p class="text-muted mb-3">You don't have any pending or accepted services scheduled.</p>
                    <a href="browse_services.php" class="btn btn-primary rounded-pill px-4">Find a Service</a>
                </div>
            <?php else: ?>
                <div class="list-group list-group-flush rounded-4">
                    <?php foreach ($upcomingBookings as $ub): ?>
                        <div class="list-group-item p-4 d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="fw-bold text-dark mb-1"><?= htmlspecialchars($ub['service_title']) ?></h5>
                                <p class="text-muted small mb-1"><i class="bi bi-person me-1"></i> <?= htmlspecialchars($ub['provider_name']) ?> &nbsp;&bull;&nbsp; <i class="bi bi-calendar me-1"></i> <?= date('M d, Y', strtotime($ub['booking_date'])) ?></p>
                            </div>
                            <div class="text-end">
                                <span class="badge badge-<?= strtolower($ub['booking_status']) ?> mb-2 fs-6 px-3 py-2 rounded-pill"><?= ucfirst($ub['booking_status']) ?></span>
                                <div class="fw-bold text-primary">₹<?= number_format($ub['price'], 2) ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- Recent Past Activity Feed -->
        <h6 class="text-muted text-uppercase fw-bold mb-3"><i class="bi bi-activity me-1"></i> Recent History</h6>
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-0">
                <?php if (empty($recentBookings)): ?>
                    <p class="text-center text-muted py-4 mb-0">No past activity recorded.</p>
                <?php else: ?>
                    <div class="list-group list-group-flush rounded-4">
                    <?php foreach ($recentBookings as $rb): ?>
                        <div class="list-group-item p-3 d-flex justify-content-between align-items-center bg-light">
                            <div>
                                <span class="badge badge-<?= strtolower($rb['booking_status']) ?> me-2"><?= ucfirst($rb['booking_status']) ?></span>
                                <span class="fw-bold text-dark"><?= htmlspecialchars($rb['service_title']) ?></span> 
                                <span class="text-muted small d-block mt-1">on <?= date('M d, Y', strtotime($rb['booking_date'])) ?></span>
                            </div>
                            <?php if ($rb['booking_status'] === 'completed'): ?>
                                <a href="book_service.php?id=<?= $rb['service_id'] ?>" class="btn btn-sm btn-outline-primary rounded-pill"><i class="bi bi-arrow-repeat"></i> Book Again</a>
                            <?php else: ?>
                                <a href="service_details.php?id=<?= $rb['service_id'] ?>" class="btn btn-sm btn-outline-secondary rounded-pill">View Detail</a>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
      </div>

      <!-- Right Column: Top Rated Recommendations -->
      <div class="col-lg-4">
        <h6 class="text-muted text-uppercase fw-bold mb-3"><i class="bi bi-star-fill text-warning me-1"></i> Top Rated Near You</h6>
        <?php foreach ($topServices as $ts): ?>
            <div class="card border-0 shadow-sm rounded-4 mb-3 transition-hover h-100">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between">
                        <span class="badge bg-primary bg-opacity-10 text-primary mb-2"><?= htmlspecialchars($ts['category']) ?></span>
                        <div class="text-warning small fw-bold"><i class="bi bi-star-fill"></i> <?= number_format($ts['avg_rating'], 1) ?></div>
                    </div>
                    <h6 class="fw-bold mb-1"><?= htmlspecialchars($ts['title']) ?></h6>
                    <p class="text-muted small mb-2" style="font-size: 0.8rem;">By <?= htmlspecialchars($ts['provider_name']) ?></p>
                    <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
                        <span class="fw-bold text-dark">₹<?= number_format($ts['price'], 2) ?></span>
                        <a href="book_service.php?id=<?= $ts['id'] ?>" class="btn btn-sm btn-primary rounded-pill px-3">Book</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        <?php if(empty($topServices)): ?>
            <div class="card border-0 shadow-sm rounded-4"><div class="card-body text-center text-muted py-4">No recommendations to show at this time.</div></div>
        <?php endif; ?>
      </div>

    </div>
  </div>
</div>
<style>
.transition-hover:hover { transform: translateY(-2px); box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important; transition: all .2s;}
</style>
<?php require_once __DIR__ . '/../includes/dashboard_footer.php'; ?>

