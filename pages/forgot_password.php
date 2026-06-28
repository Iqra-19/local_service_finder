<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/db.php';

if (isLoggedIn()) redirectByRole();

$error = '';
$successMsg = '';
$simulatedResetUrl = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (!$email) {
        $error = 'Please enter your registered email address.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $stmt = $pdo->prepare('SELECT id, name FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', time() + 3600); // 1 hour expiration

            $update = $pdo->prepare('UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?');
            $update->execute([$token, $expires, $user['id']]);

            // Construct local simulation reset URL
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'];
            $simulatedResetUrl = $protocol . '://' . $host . getBaseUrl() . '/pages/reset_password.php?token=' . $token;

            $successMsg = 'Password reset instructions have been generated!';
        } else {
            // Friendly message without leaking user existence, or explicit for local dev
            $successMsg = 'If that email address is registered, you will receive password reset instructions shortly.';
        }
    }
}

$pageTitle = 'Forgot Password';
include __DIR__ . '/../includes/auth_header.php';
?>

<div class="auth-wrapper">
    <div class="glass-card">
        <div class="card-body p-4 p-md-5">
            <div class="text-center mb-4">
                <i class="bi bi-key-fill text-primary opacity-75 display-4 mb-2 d-block"></i>
                <h2 class="mb-1 text-dark">Reset Password</h2>
                <p class="text-muted small">Enter your email to receive a recovery link</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-glass-danger d-flex align-items-center mb-4 rounded-3 alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2 flex-shrink-0 fs-5"></i>
                    <div><?= htmlspecialchars($error) ?></div>
                    <button type="button" class="btn-close ms-auto opacity-50" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($successMsg): ?>
                <div class="alert alert-glass-success mb-4 rounded-3 fade show" role="alert">
                    <div class="d-flex align-items-center mb-2">
                        <i class="bi bi-check-circle-fill me-2 flex-shrink-0 fs-5"></i>
                        <div class="fw-bold"><?= htmlspecialchars($successMsg) ?></div>
                    </div>
                    <?php if ($simulatedResetUrl): ?>
                        <div class="mt-3 pt-2 border-top border-success border-opacity-25 small">
                            <span class="badge bg-success mb-1">Local Environment Link</span><br>
                            <span class="text-muted">Click below to simulate email link access:</span><br>
                            <a href="<?= htmlspecialchars($simulatedResetUrl) ?>" class="fw-bold text-break text-decoration-underline mt-1 d-inline-block">
                                Reset Password Now &rarr;
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" id="forgotForm" class="needs-validation" novalidate>
                <div class="form-floating mb-4">
                    <input type="email" name="email" id="email" class="form-control rounded-3" 
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" placeholder="name@example.com" autofocus required>
                    <label for="email"><i class="bi bi-envelope me-1"></i>Registered Email Address</label>
                    <div class="invalid-feedback text-danger opacity-75">Please enter a valid email address.</div>
                </div>

                <button type="submit" id="forgotSubmitBtn" class="btn btn-glass btn-lg w-100 rounded-3 mb-3 position-relative">
                    <span class="spinner-border spinner-border-sm position-absolute top-50 start-0 translate-middle-y ms-3 d-none" id="forgotSpinner"></span>
                    <span id="forgotText"><i class="bi bi-send-fill me-1"></i> Send Recovery Link</span>
                </button>
            </form>

            <div class="text-center mt-4 border-top border-dark border-opacity-10 pt-4">
                <p class="text-muted small mb-0">
                    Remembered your password? <br>
                    <a href="login.php" class="fw-bold mt-1 d-inline-block">Return to Login</a>
                </p>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('forgotForm');
    const submitBtn = document.getElementById('forgotSubmitBtn');
    
    form.addEventListener('submit', function (event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        } else {
            submitBtn.classList.add('disabled');
            document.getElementById('forgotSpinner').classList.remove('d-none');
            document.getElementById('forgotText').textContent = ' Processing...';
        }
        form.classList.add('was-validated');
    }, false);
});
</script>

<?php include __DIR__ . '/../includes/auth_footer.php'; ?>
