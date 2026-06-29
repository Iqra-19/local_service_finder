<?php
if (function_exists('getFlash')) {
    $flash = getFlash();
    if ($flash):
        $bgClass = ($flash['type'] === 'success') ? 'bg-success' : (($flash['type'] === 'danger' || $flash['type'] === 'error') ? 'bg-danger' : 'bg-primary');
        $iconClass = ($flash['type'] === 'success') ? 'fa-circle-check' : (($flash['type'] === 'danger' || $flash['type'] === 'error') ? 'fa-circle-exclamation' : 'fa-info-circle');
?>
    <!-- Toast Notification Container -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1090;">
        <div id="globalToast" class="toast align-items-center text-white <?= $bgClass ?> border-0 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body d-flex align-items-center gap-2 fs-6">
                    <i class="fa-solid <?= $iconClass ?> fs-5"></i>
                    <span><?= htmlspecialchars($flash['message'], ENT_QUOTES, 'UTF-8') ?></span>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var toastEl = document.getElementById('globalToast');
            if (toastEl) {
                var toast = new bootstrap.Toast(toastEl, { delay: 5000 });
                toast.show();
            }
        });
    </script>
<?php 
    endif;
} 
?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

