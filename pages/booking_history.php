<?php
$pageTitle = 'Booking History';
require_once __DIR__ . '/../includes/dashboard_header.php';
requireRole('user');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/sidebar_user.php';

$statusFilter = filter_input(INPUT_GET, 'status', FILTER_SANITIZE_SPECIAL_CHARS) ?: '';

$sql = "SELECT b.*, s.title AS service_title, s.price, s.category, u.name AS provider_name
        FROM bookings b
        JOIN services s ON b.service_id = s.id
        JOIN users u ON b.provider_id = u.id
        WHERE b.user_id = ?";
$params = [$_SESSION['user_id']];

if (in_array($statusFilter, ['pending', 'accepted', 'completed', 'rejected', 'cancelled'])) {
    $sql .= " AND b.booking_status = ?";
    $params[] = $statusFilter;
}

$sql .= " ORDER BY b.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$bookings = $stmt->fetchAll();

// Stats
$statsStmt = $pdo->prepare("SELECT booking_status, COUNT(*) as count FROM bookings WHERE user_id = ? GROUP BY booking_status");
$statsStmt->execute([$_SESSION['user_id']]);

$stats = ['pending' => 0, 'accepted' => 0, 'completed' => 0, 'rejected' => 0, 'cancelled' => 0];
while ($row = $statsStmt->fetch()) {
    if (isset($stats[$row['booking_status']])) {
        $stats[$row['booking_status']] = $row['count'];
    }
}
$totalBookings = array_sum($stats);
?>

<div class="dashboard-main">
    <?php require_once __DIR__ . '/../includes/topbar.php'; ?>

    <div class="dashboard-content">

        <!-- Stats -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card text-center p-3 shadow-sm border-0">
                    <h4 class="text-primary mb-1"><?= $totalBookings ?></h4>
                    <small class="text-muted fw-bold text-uppercase">Total Bookings</small>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card text-center p-3 shadow-sm border-0 bg-warning bg-opacity-10">
                    <h4 class="text-warning mb-1"><?= $stats['pending'] ?></h4>
                    <small class="text-muted fw-bold text-uppercase">Pending</small>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card text-center p-3 shadow-sm border-0 bg-primary bg-opacity-10">
                    <h4 class="text-primary mb-1"><?= $stats['accepted'] ?></h4>
                    <small class="text-muted fw-bold text-uppercase">Accepted</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center p-3 shadow-sm border-0 bg-success bg-opacity-10">
                    <h4 class="text-success mb-1"><?= $stats['completed'] ?></h4>
                    <small class="text-muted fw-bold text-uppercase">Completed</small>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card text-center p-3 shadow-sm border-0 bg-danger bg-opacity-10">
                    <h4 class="text-danger mb-1"><?= $stats['rejected'] ?></h4>
                    <small class="text-muted fw-bold text-uppercase">Rejected</small>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card text-center p-3 shadow-sm border-0 bg-secondary bg-opacity-10">
                    <h4 class="text-secondary mb-1"><?= $stats['cancelled'] ?></h4>
                    <small class="text-muted fw-bold text-uppercase">Cancelled</small>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <ul class="nav nav-pills mb-4 bg-white p-2 rounded shadow-sm">
            <li class="nav-item">
                <a class="nav-link <?= $statusFilter === '' ? 'active' : '' ?>" href="booking_history.php">All</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $statusFilter === 'pending' ? 'active' : '' ?>" href="?status=pending">Pending</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $statusFilter === 'accepted' ? 'active' : '' ?>" href="?status=accepted">Accepted</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $statusFilter === 'completed' ? 'active' : '' ?>" href="?status=completed">Completed</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $statusFilter === 'rejected' ? 'active' : '' ?>" href="?status=rejected">Rejected</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $statusFilter === 'cancelled' ? 'active' : '' ?>" href="?status=cancelled">Cancelled</a>
            </li>
        </ul>

        <!-- Table -->
        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <?php if (empty($bookings)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-calendar-x display-4 text-muted mb-3 d-block"></i>
                        <h5 class="text-muted">No bookings found</h5>
                        <p class="text-muted mb-4">You have not booked any services yet.</p>
                        <a href="browse_services.php" class="btn btn-primary"><i class="bi bi-search me-1"></i>Browse Services</a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-clean table-hover mb-0 align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Service</th>
                                    <th>Provider</th>
                                    <th>Date</th>
                                    <th>Price</th>
                                    <th>Status</th>
                                    <th class="text-end pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($bookings as $b): ?>
                                    <tr>
                                        <td class="ps-4 fw-medium text-dark"><?= htmlspecialchars($b['service_title']) ?></td>
                                        <td><?= htmlspecialchars($b['provider_name']) ?></td>
                                        <td><?= date('M d, Y', strtotime($b['booking_date'])) ?></td>
                                        <td class="fw-semibold">₹<?= number_format($b['price'], 2) ?></td>
                                        <td>
                                            <span class="badge badge-<?= strtolower($b['booking_status']) ?> px-2 py-1">
                                                <?= ucfirst($b['booking_status']) ?>
                                            </span>
                                        </td>
                                        <td class="text-end pe-4">
                                            <?php if ($b['booking_status'] === 'pending'): ?>
                                                <a href="cancel_booking.php?id=<?= $b['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to cancel this booking?');">Cancel</a>
                                            <?php elseif ($b['booking_status'] === 'completed'): ?>
                                                <a href="leave_review.php?booking_id=<?= $b['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-star me-1"></i>Review</a>
                                            <?php else: ?>
                                                <span class="text-muted small">—</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/../includes/dashboard_footer.php'; ?>
