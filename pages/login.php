<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/google_config.php';

if (isLoggedIn()) redirectByRole();


if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['login_lockout'] = 0;
}

$error = '';
$lockoutDuration = 15; // minutes
$email = $_SERVER['REQUEST_METHOD'] !== 'POST' ? 'volt.repair@services.com' : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($_SESSION['login_lockout'] > 0 && $_SESSION['login_lockout'] < time()) {
        $_SESSION['login_attempts'] = 0;
        $_SESSION['login_lockout'] = 0;
    }

    if ($_SESSION['login_lockout'] > time()) {
        $remaining = ceil(($_SESSION['login_lockout'] - time()) / 60);
        $error = "Too many failed attempts. Try again in $remaining minute(s).";
    } else {
        if (!$email || !$password) {
            $error = 'Both email and password are required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } else {
            $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                session_regenerate_id(true);

                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];

                $_SESSION['login_attempts'] = 0;
                $_SESSION['login_lockout'] = 0;

                redirectByRole();
            } else {
                $_SESSION['login_attempts']++;
                if ($_SESSION['login_attempts'] >= 5) {
                    $_SESSION['login_lockout'] = time() + ($lockoutDuration * 60);
                    $error = "Account locked locally for $lockoutDuration minutes.";
                } else {
                    $attemptsLeft = 5 - $_SESSION['login_attempts'];
                    $error = "Invalid credentials. ($attemptsLeft attempts remaining)";
                }
            }
        }
    }
}

$pageTitle = 'Secure Login';
include __DIR__ . '/../includes/auth_header.php';
?>

<div class="auth-wrapper">
    <div class="glass-card">
        <div class="card-body p-4 p-md-5">
            <div class="text-center mb-4">
                <i class="bi bi-shield-lock text-primary opacity-75 display-4 mb-2 d-block"></i>
                <h2 class="mb-1 text-dark">Authentication</h2>
                <p class="text-muted small">Securely access your dashboard</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-glass-danger d-flex align-items-center mb-4 rounded-3 alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2 flex-shrink-0 fs-5"></i>
                    <div><?= htmlspecialchars($error) ?></div>
                    <button type="button" class="btn-close ms-auto opacity-50" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php $flash = getFlash(); if ($flash): ?>
                <div class="alert alert-glass-<?= htmlspecialchars($flash['type']) === 'success' ? 'success' : 'danger' ?> d-flex align-items-center mb-4 rounded-3 alert-dismissible fade show" role="alert">
                    <i class="bi bi-info-circle-fill me-2 flex-shrink-0 fs-5"></i>
                    <div><?= htmlspecialchars($flash['message']) ?></div>
                    <button type="button" class="btn-close ms-auto opacity-50" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form method="POST" action="" id="loginForm" class="needs-validation" novalidate>
                <div class="form-floating mb-3">
                    <input type="email" name="email" id="email" class="form-control rounded-3" 
                           value="<?= htmlspecialchars($email ?? '') ?>" placeholder="name@example.com" autofocus required>
                    <label for="email"><i class="bi bi-envelope me-1"></i>Email address</label>
                    <div class="invalid-feedback text-danger opacity-75">Please provide a valid registered email.</div>
                </div>
                
                <div class="form-floating pe-5 position-relative mb-2">
                    <input type="password" name="password" id="password" class="form-control rounded-3" 
                           value="<?= $_SERVER['REQUEST_METHOD'] !== 'POST' ? 'Password123' : '' ?>" placeholder="Password" required>
                    <label for="password"><i class="bi bi-key me-1"></i>Password</label>
                    <button type="button" class="btn position-absolute top-50 end-0 translate-middle-y text-muted border-0 shadow-none px-3" id="togglePassword">
                        <i class="bi bi-eye-fill fs-5" id="toggleIcon"></i>
                    </button>
                    <div class="invalid-feedback text-danger opacity-75">Password is required.</div>
                </div>

                <div class="d-flex justify-content-end mb-4">
                    <a href="forgot_password.php" class="small">Forgot Password?</a>
                </div>

                <button type="submit" id="submitBtn" class="btn btn-glass btn-lg w-100 rounded-3 mb-3 position-relative">
                    <span class="spinner-border spinner-border-sm position-absolute top-50 start-0 translate-middle-y ms-3 d-none" id="submitSpinner"></span>
                    <span id="submitText"><i class="bi bi-box-arrow-in-right me-1"></i> Secure Login</span>
                </button>
            </form>

            <div class="auth-divider">OR</div>

            <div class="google-btn-container mb-3">
                <div id="g_id_onload"
                     data-client_id="<?= htmlspecialchars(GOOGLE_CLIENT_ID) ?>"
                     data-login_uri="<?= getBaseUrl() ?>/pages/google_auth.php"
                     data-auto_prompt="false">
                </div>
                <div class="g_id_signin"
                     data-type="standard"
                     data-size="large"
                     data-theme="outline"
                     data-text="sign_in_with"
                     data-shape="rectangular"
                     data-logo_alignment="left"
                     data-width="320">
                </div>
            </div>

            <div class="text-center mt-4 border-top border-dark border-opacity-10 pt-4">
                <p class="text-muted small mb-0">
                    Don't have an account? <br>
                    <a href="register.php" class="fw-bold mt-1 d-inline-block">Create an account</a>
                </p>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Show Password toggle
    document.getElementById('togglePassword').addEventListener('click', function() {
        const pass = document.getElementById('password');
        const icon = document.getElementById('toggleIcon');
        if(pass.type === 'password') {
            pass.type = 'text';
            icon.classList.replace('bi-eye-fill', 'bi-eye-slash-fill');
        } else {
            pass.type = 'password';
            icon.classList.replace('bi-eye-slash-fill', 'bi-eye-fill');
        }
    });

    // Form spin loading
    const form = document.getElementById('loginForm');
    const submitBtn = document.getElementById('submitBtn');
    
    form.addEventListener('submit', function (event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        } else {
            submitBtn.classList.add('disabled');
            document.getElementById('submitSpinner').classList.remove('d-none');
            document.getElementById('submitText').textContent = ' Authenticating...';
        }
        form.classList.add('was-validated');
    }, false);
});
</script>

<?php include __DIR__ . '/../includes/auth_footer.php'; ?>
