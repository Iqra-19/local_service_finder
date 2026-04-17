<?php
$pageTitle = 'Booking Requests';
require_once __DIR__ . '/../includes/dashboard_header.php';
requireRole('provider');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/sidebar_provider.php';

$providerId = $_SESSION['user_id'];
$bookings = [];
try {
    $stmt = $pdo->prepare("SELECT b.*, s.title AS service_title, u.name AS customer_name 
                           FROM bookings b 
                           JOIN services s ON b.service_id = s.id 
                           JOIN users u ON b.user_id = u.id 
                           WHERE b.provider_id = ? 
                           ORDER BY b.created_at DESC");
    $stmt->execute([$providerId]);
    $bookings = $stmt->fetchAll();
} catch (PDOException $e) {
    // Tables may not exist yet
}
?>
<div class="dashboard-main">
  <?php require_once __DIR__ . '/../includes/topbar.php'; ?>
  <div class="dashboard-content">
    <div class="card shadow-sm border-0">
      <div class="card-header bg-white border-bottom py-3">
        <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-journal-check text-primary me-2"></i> Booking Requests</h5>
      </div>
      <div class="card-body p-0">
        <?php if (empty($bookings)): ?>
          <div class="text-center py-5">
            <i class="bi bi-inbox display-4 text-muted mb-3 d-block"></i>
            <h5 class="text-muted">No requests yet</h5>
            <p class="text-muted">You will see booking requests here once customers start booking your services.</p>
          </div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-clean table-hover mb-0 align-middle">
              <thead class="bg-light">
                <tr>
                  <th class="ps-4">Customer</th>
                  <th>Service</th>
                  <th>Date</th>
                  <th>Status</th>
                  <th class="text-end pe-4">Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($bookings as $b): ?>
                  <tr>
                    <td class="ps-4 fw-medium text-dark"><?= htmlspecialchars($b['customer_name']) ?></td>
                    <td><?= htmlspecialchars($b['service_title']) ?></td>
                    <td><?= date('M d, Y', strtotime($b['booking_date'])) ?></td>
                    <td>
                      <span class="badge badge-<?= strtolower($b['status']) ?> px-2 py-1">
                        <?= ucfirst($b['status']) ?>
                      </span>
                    </td>
                    <td class="text-end pe-4">
                      <?php if ($b['status'] === 'pending'): ?>
                        <div class="btn-group h-100">
                            <a href="update_booking.php?id=<?= $b['id'] ?>&action=accept" class="btn btn-sm btn-success py-1" onclick="return confirm('Accept this booking?');"><i class="bi bi-check-lg me-1"></i>Accept</a>
                            <a href="update_booking.php?id=<?= $b['id'] ?>&action=reject" class="btn btn-sm btn-danger py-1" onclick="return confirm('Reject this booking?');"><i class="bi bi-x-lg me-1"></i>Reject</a>
                        </div>
                      <?php elseif ($b['status'] === 'accepted'): ?>
                        <a href="update_booking.php?id=<?= $b['id'] ?>&action=complete" class="btn btn-sm btn-primary" onclick="return confirm('Mark this service as complete?');"><i class="bi bi-check2-all me-1"></i>Mark Complete</a>
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
