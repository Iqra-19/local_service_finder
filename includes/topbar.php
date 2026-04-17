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

    <div class="user-info d-flex align-items-center gap-2">
      <span class="text-muted fw-medium"><?= htmlspecialchars($userName ?? 'Guest') ?></span>
      <div class="user-avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 38px; height: 38px;">
        <?= htmlspecialchars($initials ?? '?') ?>
      </div>
    </div>
  </div>
</div>
