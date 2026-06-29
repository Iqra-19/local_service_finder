<div class="dashboard-topbar d-flex justify-content-between align-items-center bg-white p-3 shadow-sm mb-4">
  <div class="welcome-text fw-bold fs-5 text-primary">
    <?= htmlspecialchars($pageTitle ?? 'Dashboard') ?>
  </div>
  
  <div class="d-flex align-items-center gap-3">
    <?php if (isset($flash) && $flash): ?>
      <div id="autoDismissAlert" class="alert alert-<?= htmlspecialchars($flash['type']) ?> alert-dismissible fade show mb-0 py-2 px-3 me-3 d-flex align-items-center shadow-sm" role="alert" style="font-size: 0.9rem; border-left: 4px solid <?= $flash['type'] === 'success' ? '#198754' : '#dc3545' ?>;">
        <i class="bi bi-<?= $flash['type'] === 'success' ? 'check-circle-fill text-success' : 'exclamation-triangle-fill text-danger' ?> me-2 fs-5"></i>
        <div class="fw-medium text-dark"><?= htmlspecialchars($flash['message']) ?></div>
        <button type="button" class="btn-close py-2 px-2" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      <script>
        document.addEventListener('DOMContentLoaded', function() {
            const alertNode = document.getElementById('autoDismissAlert');
            if (alertNode) {
                setTimeout(() => {
                    alertNode.style.transition = 'opacity 0.5s ease';
                    alertNode.style.opacity = '0';
                    setTimeout(() => alertNode.remove(), 500);
                }, 3000);
            }
        });
      </script>
    <?php endif; ?>

    <?php
    $unreadCount = 0;
    if (isset($_SESSION['user_id'])) {
        require_once __DIR__ . '/../config/db.php';
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
            $stmt->execute([$_SESSION['user_id']]);
            $unreadCount = (int)$stmt->fetchColumn();
        } catch (Exception $e) {
            $unreadCount = 0;
        }
    }
    ?>

    <!-- Notification Icon with Badge -->
    <div class="position-relative me-2">
      <a href="support_messages.php" class="text-secondary fs-5 position-relative text-decoration-none" title="Notifications & Support">
        <i class="bi bi-bell-fill"></i>
        <?php if ($unreadCount > 0): ?>
          <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem;">
            <?= $unreadCount ?>
          </span>
        <?php endif; ?>
      </a>
    </div>

    <div class="user-info d-flex align-items-center gap-2">
      <span class="text-muted fw-medium"><?= htmlspecialchars($userName ?? 'Guest') ?></span>
      <div class="user-avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 38px; height: 38px;">
        <?= htmlspecialchars($initials ?? '?') ?>
      </div>
    </div>
  </div>
</div>

