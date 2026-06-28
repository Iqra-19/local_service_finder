<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/db.php';

if (isLoggedIn()) redirectByRole();

$token = trim($_GET['token'] ?? '');
$error = '';
$user = null;

if (empty($token)) {
    $error = 'No reset token provided. Please request a new password recovery link.';
} else {
    $stmt = $pdo->prepare('SELECT * FROM users WHERE reset_token = ? AND reset_expires > NOW()');
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if (!$user) {
        $error = 'This password reset link is invalid or has expired. Please request a new one.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $user) {
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (mb_strlen($password) < 8 || !preg_match("/[a-zA-Z]/", $password) || !preg_match("/[0-9]/", $password)) {
        $error = 'Password must be at least 8 chars, containing letters and numbers.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        $update = $pdo->prepare('UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?');
        $update->execute([$hash, $user['id']]);

        setFlash('success', 'Your password has been reset successfully! You can now log in.');
        header('Location: login.php');
        exit;
    }
}

$pageTitle = 'Set New Password';
include __DIR__ . '/../includes/auth_header.php';
?>

<div class="auth-wrapper">
    <div class="glass-card">
        <div class="card-body p-4 p-md-5">
            <div class="text-center mb-4">
                <i class="bi bi-shield-lock-fill text-primary opacity-75 display-4 mb-2 d-block"></i>
                <h2 class="mb-1 text-dark">Set New Password</h2>
                <p class="text-muted small">Create a strong replacement password</p>
            </div>

            <?php if ($error && !$user): ?>
                <div class="alert alert-glass-danger mb-4 rounded-3 text-center" role="alert">
                    <i class="bi bi-exclamation-octagon-fill fs-3 text-danger mb-2 d-block"></i>
                    <div class="mb-3"><?= htmlspecialchars($error) ?></div>
                    <a href="forgot_password.php" class="btn btn-sm btn-outline-danger rounded-pill px-3">Request New Link</a>
                </div>
            <?php else: ?>

                <?php if ($error): ?>
                    <div class="alert alert-glass-danger d-flex align-items-center mb-4 rounded-3 alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2 flex-shrink-0 fs-5"></i>
                        <div><?= htmlspecialchars($error) ?></div>
                        <button type="button" class="btn-close ms-auto opacity-50" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" id="resetForm" class="needs-validation" novalidate oninput='confirm_password.setCustomValidity(confirm_password.value != password.value ? "Passwords do not match." : "")'>
                    
                    <div class="form-floating mb-3 position-relative">
                        <input type="password" name="password" id="password" class="form-control rounded-3" 
                               placeholder="New Password" minlength="8" required autofocus>
                        <label for="password"><i class="bi bi-key me-1"></i>New Password</label>
                        
                        <div class="progress mt-2 bg-secondary bg-opacity-25" style="height: 4px;">
                            <div id="strengthMeter" class="progress-bar bg-danger" style="width: 0%;"></div>
                        </div>
                        <small id="strengthText" class="text-muted mt-1 d-block" style="font-size: 0.70rem;">Requires 8+ chars (Letters & Numbers)</small>
                        <div class="invalid-feedback text-danger opacity-75">Minimum 8 chars, 1 letter, 1 number required.</div>
                    </div>

                    <div class="form-floating mb-4">
                        <input type="password" name="confirm_password" id="confirm_password" class="form-control rounded-3" 
                               placeholder="Confirm Password" minlength="8" required>
                        <label for="confirm_password"><i class="bi bi-check-circle me-1"></i>Confirm New Password</label>
                        <div class="invalid-feedback text-danger opacity-75">Passwords must match completely.</div>
                    </div>

                    <button type="submit" id="resetSubmitBtn" class="btn btn-glass btn-lg w-100 rounded-3 mb-3 position-relative">
                        <span class="spinner-border spinner-border-sm position-absolute top-50 start-0 translate-middle-y ms-3 d-none" id="resetSpinner"></span>
                        <span id="resetText"><i class="bi bi-check2-circle me-1"></i> Update Password</span>
                    </button>
                </form>
            <?php endif; ?>

            <div class="text-center mt-4 border-top border-dark border-opacity-10 pt-4">
                <p class="text-muted small mb-0">
                    Back to login page? <br>
                    <a href="login.php" class="fw-bold mt-1 d-inline-block">Return to Login</a>
                </p>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const passInput = document.getElementById('password');
    if (!passInput) return;

    const meter = document.getElementById('strengthMeter');
    const textOut = document.getElementById('strengthText');

    passInput.addEventListener('input', function() {
        const val = passInput.value;
        let score = 0;
        
        if (val.length >= 8) score += 20;
        if (val.match(/[a-zA-Z]/)) score += 20;
        if (val.match(/[0-9]/)) score += 20;
        if (val.match(/[^a-zA-Z0-9]/)) score += 20;
        if (val.length >= 12) score += 20;

        meter.style.width = score + '%';

        if (val.length === 0) {
            meter.style.width = '0%';
            textOut.innerHTML = 'Requires 8+ chars (Letters & Numbers)';
            passInput.setCustomValidity('Required.');
        } else if (score <= 40 || !val.match(/[a-zA-Z]/) || !val.match(/[0-9]/) || val.length < 8) {
            meter.className = 'progress-bar bg-danger';
            textOut.innerHTML = 'Weak (Needs 8+ chars inside letters & numbers)';
            passInput.setCustomValidity('Invalid pattern');
        } else if (score === 60) {
            meter.className = 'progress-bar bg-warning';
            textOut.innerHTML = 'Medium';
            passInput.setCustomValidity(''); 
        } else {
            meter.className = 'progress-bar bg-success';
            textOut.innerHTML = 'Strong';
            passInput.setCustomValidity('');
        }
    });

    const form = document.getElementById('resetForm');
    const submitBtn = document.getElementById('resetSubmitBtn');
    
    if (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            } else {
                submitBtn.classList.add('disabled');
                document.getElementById('resetSpinner').classList.remove('d-none');
                document.getElementById('resetText').textContent = ' Updating Password...';
            }
            form.classList.add('was-validated');
        }, false);
    }
});
</script>

<?php include __DIR__ . '/../includes/auth_footer.php'; ?>
