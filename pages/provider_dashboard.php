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

$statusCounts = ['pending' => 0, 'accepted' => 0, 'completed' => 0, 'rejected' => 0, 'cancelled' => 0];

$statsStmt = $pdo->prepare("SELECT booking_status, COUNT(*) as count FROM bookings WHERE provider_id = ? GROUP BY booking_status");
$statsStmt->execute([$providerId]);
while ($row = $statsStmt->fetch()) {
    $totalBookings += $row['count'];
    if (isset($statusCounts[$row['booking_status']])) {
        $statusCounts[$row['booking_status']] = intval($row['count']);
    }
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

// Fetch Monthly Earnings for the last 6 months
$trendStmt = $pdo->prepare("
    SELECT 
        DATE_FORMAT(b.booking_date, '%M %Y') AS month_label,
        SUM(s.price) AS monthly_earnings,
        DATE_FORMAT(b.booking_date, '%Y-%m') as sort_label
    FROM bookings b
    JOIN services s ON b.service_id = s.id
    WHERE b.provider_id = ? AND b.booking_status = 'completed' AND b.booking_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY month_label, sort_label
    ORDER BY sort_label ASC
");
$trendStmt->execute([$providerId]);
$rawTrend = $trendStmt->fetchAll();

// Fill missing months with zero values
$months = [];
for ($i = 5; $i >= 0; $i--) {
    $monthKey = date('Y-m', strtotime("-$i months"));
    $monthName = date('F Y', strtotime("-$i months"));
    $months[$monthKey] = [
        'label' => $monthName,
        'earnings' => 0.0
    ];
}
foreach ($rawTrend as $t) {
    if (isset($months[$t['sort_label']])) {
        $months[$t['sort_label']]['earnings'] = floatval($t['monthly_earnings']);
    }
}
$chartLabels = [];
$chartEarnings = [];
foreach ($months as $m) {
    $chartLabels[] = $m['label'];
    $chartEarnings[] = $m['earnings'];
}

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
<!-- Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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

    <!-- Charts Row -->
    <div class="row g-4 mb-4">
      <!-- Earnings Chart -->
      <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-4 h-100">
          <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3 px-4">
            <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-graph-up text-primary me-2"></i>Monthly Earnings Trend</h6>
            <div class="btn-group btn-group-sm" role="group">
              <button type="button" class="btn btn-outline-primary active" id="btn-chart-line">Line</button>
              <button type="button" class="btn btn-outline-primary" id="btn-chart-bar">Bar</button>
            </div>
          </div>
          <div class="card-body p-4">
            <div style="position: relative; height: 280px;">
              <canvas id="earningsChart"></canvas>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Status Distribution Chart -->
      <div class="col-lg-5">
        <div class="card border-0 shadow-sm rounded-4 h-100">
          <div class="card-header bg-white border-bottom py-3 px-4">
            <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-pie-chart text-success me-2"></i>Booking Status Split</h6>
          </div>
          <div class="card-body p-4 d-flex align-items-center justify-content-center">
            <div style="position: relative; height: 280px; width: 100%;">
              <canvas id="statusChart"></canvas>
            </div>
          </div>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const earningsLabels = <?= json_encode($chartLabels) ?>;
    const earningsData = <?= json_encode($chartEarnings) ?>;
    const statusLabels = ['Pending', 'Accepted', 'Completed', 'Rejected', 'Cancelled'];
    const statusData = [
        <?= $statusCounts['pending'] ?>,
        <?= $statusCounts['accepted'] ?>,
        <?= $statusCounts['completed'] ?>,
        <?= $statusCounts['rejected'] ?>,
        <?= $statusCounts['cancelled'] ?>
    ];

    // 1. Earnings Trend Chart (Line / Bar)
    const ctxEarnings = document.getElementById('earningsChart').getContext('2d');
    let earningsChart = new Chart(ctxEarnings, {
        type: 'line',
        data: {
            labels: earningsLabels,
            datasets: [{
                label: 'Monthly Earnings (₹)',
                data: earningsData,
                borderColor: '#1a73e8',
                backgroundColor: 'rgba(26, 115, 232, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) { return '₹' + value.toLocaleString(); }
                    }
                }
            }
        }
    });

    // Chart Type Toggles
    document.getElementById('btn-chart-line').addEventListener('click', function() {
        this.classList.add('active');
        document.getElementById('btn-chart-bar').classList.remove('active');
        updateEarningsChartType('line');
    });

    document.getElementById('btn-chart-bar').addEventListener('click', function() {
        this.classList.add('active');
        document.getElementById('btn-chart-line').classList.remove('active');
        updateEarningsChartType('bar');
    });

    function updateEarningsChartType(type) {
        earningsChart.destroy();
        earningsChart = new Chart(ctxEarnings, {
            type: type,
            data: {
                labels: earningsLabels,
                datasets: [{
                    label: 'Monthly Earnings (₹)',
                    data: earningsData,
                    borderColor: '#1a73e8',
                    backgroundColor: type === 'bar' ? 'rgba(26, 115, 232, 0.8)' : 'rgba(26, 115, 232, 0.1)',
                    borderWidth: type === 'bar' ? 0 : 3,
                    fill: type !== 'bar',
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) { return '₹' + value.toLocaleString(); }
                        }
                    }
                }
            }
        });
    }

    // 2. Booking Status Distribution Chart (Doughnut)
    const ctxStatus = document.getElementById('statusChart').getContext('2d');
    const statusChart = new Chart(ctxStatus, {
        type: 'doughnut',
        data: {
            labels: statusLabels,
            datasets: [{
                data: statusData,
                backgroundColor: [
                    '#ffc107', // Warning (Pending)
                    '#0d6efd', // Primary (Accepted)
                    '#198754', // Success (Completed)
                    '#dc3545', // Danger (Rejected)
                    '#6c757d'  // Secondary (Cancelled)
                ],
                borderWidth: 2,
                hoverOffset: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { boxWidth: 12, padding: 12, font: { size: 11 } }
                }
            },
            cutout: '65%'
        }
    });
});
</script>

<?php require_once __DIR__ . '/../includes/dashboard_footer.php'; ?>

