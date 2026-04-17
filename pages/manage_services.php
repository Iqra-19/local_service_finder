<?php
$pageTitle = 'Manage Services';
require_once __DIR__ . '/../includes/dashboard_header.php';
requireRole('provider');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/sidebar_provider.php';

$providerId = $_SESSION['user_id'];
$services = [];

$stmt = $pdo->prepare("SELECT s.*, 
       (SELECT COUNT(*) FROM bookings WHERE service_id = s.id) as booking_count 
       FROM services s WHERE provider_id = ? ORDER BY created_at DESC");
$stmt->execute([$providerId]);
$services = $stmt->fetchAll();

?>
<div class="dashboard-main">
  <?php require_once __DIR__ . '/../includes/topbar.php'; ?>
  <div class="dashboard-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h5 class="mb-0 fw-bold">Your Services</h5>
      <a href="add_service.php" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Add New Service</a>
    </div>

    <?php if (empty($services)): ?>
      <div class="card shadow-sm border-0">
        <div class="card-body text-center py-5">
          <i class="bi bi-tools display-1 text-muted mb-3 d-block"></i>
          <h4 class="text-muted">No Services Yet</h4>
          <p class="text-muted mb-4">Start offering your skills by adding your first service.</p>
          <a href="add_service.php" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Add Service</a>
        </div>
      </div>
    <?php else: ?>
      <div class="card shadow-sm border-0">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-clean table-hover mb-0 align-middle">
              <thead class="bg-light">
                <tr>
                  <th class="ps-4">Title</th>
                  <th>Category</th>
                  <th>Price</th>
                  <th>Bookings</th>
                  <th>Status</th>
                  <th class="text-end pe-4">Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($services as $s): ?>
                  <tr>
                    <td class="ps-4 fw-medium text-dark"><?= htmlspecialchars($s['title']) ?></td>
                    <td><span class="badge bg-light text-primary border border-primary-subtle"><?= htmlspecialchars($s['category']) ?></span></td>
                    <td class="fw-semibold">₹<?= number_format($s['price'], 2) ?></td>
                    <td><span class="badge bg-secondary"><?= $s['booking_count'] ?></span></td>
                    <td><span class="badge badge-<?= $s['status'] === 'active' ? 'success' : 'secondary' ?>"><?= ucfirst($s['status']) ?></span></td>
                    <td class="text-end pe-4">
                      <div class="btn-group">
                          <a href="edit_service.php?id=<?= $s['id'] ?>" class="btn btn-sm btn-outline-primary" title="Edit"><i class="bi bi-pencil"></i></a>
                          <a href="delete_service.php?id=<?= $s['id'] ?>" class="btn btn-sm btn-outline-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this service?')"><i class="bi bi-trash"></i></a>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/dashboard_footer.php'; ?>
