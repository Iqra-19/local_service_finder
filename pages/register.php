<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/db.php';

if (isLoggedIn()) redirectByRole();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? 'user';

    if (!$name || !$email || !$password) {
        $error = 'All primary fields are required.';
    } elseif (!preg_match("/^[a-zA-Z\s]+$/", $name)) {
        $error = 'Name can only contain letters and spaces.';
    } elseif (mb_strlen($name) < 2 || mb_strlen($name) > 100) {
        $error = 'Name must be between 2 and 100 characters.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (mb_strlen($password) < 8 || !preg_match("/[a-zA-Z]/", $password) || !preg_match("/[0-9]/", $password)) {
        $error = 'Password must be at least 8 chars, 1 letter, and 1 number.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } elseif (!in_array($role, ['user', 'provider'])) {
        $error = 'Invalid role selected.';
    } else {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'Email is already registered. Please login.';
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
            $stmt = $pdo->prepare('INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)');
            $stmt->execute([$name, $email, $hash, $role]);
            
            setFlash('success', 'Registration successful! You can now authenticate securely.');
            header("Location: login.php");
            exit;
        }
    }
}

$pageTitle = 'Secure Registration';
include __DIR__ . '/../includes/auth_header.php';
?>

<div class="auth-wrapper auth-register">
    <div class="glass-card my-4">
        <div class="card-body p-4 p-md-5">
            <div class="text-center mb-4">
                <i class="bi bi-person-badge text-primary opacity-75 display-4 mb-2 d-block"></i>
                <h2 class="mb-1 text-dark">Create Account</h2>
                <p class="text-muted small">Verify your identity securely</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-glass-danger d-flex align-items-center mb-4 rounded-3 alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-octagon-fill me-2 flex-shrink-0 fs-5"></i>
                    <div><?= htmlspecialchars($error) ?></div>
                    <button type="button" class="btn-close ms-auto opacity-50" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form method="POST" action="" id="registerForm" class="needs-validation" novalidate oninput='confirm_password.setCustomValidity(confirm_password.value != password.value ? "Passwords do not match." : "")'>
                
                <div class="form-floating mb-3">
                    <input type="text" name="name" id="name" class="form-control rounded-3" 
                           value="<?= htmlspecialchars($name ?? '') ?>" placeholder="John Doe" minlength="2" maxlength="100" pattern="[a-zA-Z\s]+" required>
                    <label for="name"><i class="bi bi-person me-1"></i>Full Name</label>
                    <div class="invalid-feedback text-danger opacity-75">Letters and spaces only (min 2 chars).</div>
                </div>

                <div class="form-floating mb-3">
                    <input type="email" name="email" id="email" class="form-control rounded-3" 
                           value="<?= htmlspecialchars($email ?? '') ?>" placeholder="name@example.com" required>
                    <label for="email"><i class="bi bi-envelope me-1"></i>Email address</label>
                    <div class="invalid-feedback text-danger opacity-75">A valid email ensures we can securely reach you.</div>
                </div>

                <div class="form-floating mb-3">
                    <select name="role" id="role" class="form-select rounded-3" required>
                        <option value="user" <?= (isset($role) && $role === 'user') ? 'selected' : '' ?>>Customer — (Book & review services)</option>
                        <option value="provider" <?= (isset($role) && $role === 'provider') ? 'selected' : '' ?>>Service Provider — (Offer services)</option>
                    </select>
                    <label for="role"><i class="bi bi-briefcase me-1"></i>Account Type</label>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-sm-6">
                        <div class="form-floating position-relative">
                            <input type="password" name="password" id="password" class="form-control rounded-3" 
                                   placeholder="Password" minlength="8" required>
                            <label for="password"><i class="bi bi-key me-1"></i>Password</label>
                            
                            <div class="progress mt-2 bg-secondary bg-opacity-25" style="height: 4px;">
                                <div id="strengthMeter" class="progress-bar bg-danger" style="width: 0%;"></div>
                            </div>
                            <small id="strengthText" class="text-muted mt-1 d-block" style="font-size: 0.70rem;">Requires 8+ chars (Letters & Numbers)</small>
                            <div class="invalid-feedback text-danger opacity-75">Minimum 8 chars, 1 letter, 1 number required.</div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-floating">
                            <input type="password" name="confirm_password" id="confirm_password" class="form-control rounded-3" 
                                   placeholder="Confirm" minlength="8" required>
                            <label for="confirm_password"><i class="bi bi-check-circle me-1"></i>Confirm</label>
                            <div class="invalid-feedback text-danger opacity-75">Passwords must match completely.</div>
                        </div>
                    </div>
                </div>

                <button type="submit" id="regSubmitBtn" class="btn btn-glass btn-lg w-100 rounded-3 mb-3 position-relative">
                    <span class="spinner-border spinner-border-sm position-absolute top-50 start-0 translate-middle-y ms-3 d-none" id="regSubmitSpinner"></span>
                    <span id="regSubmitText"><i class="bi bi-person-plus-fill me-1"></i> Finalize Registration</span>
                </button>
            </form>

            <div class="text-center mt-3 border-top border-dark border-opacity-10 pt-4">
                <p class="text-muted small mb-0">
                    Already possess an account? <br>
                    <a href="login.php" class="fw-bold mt-1 d-inline-block">Login immediately</a>
                </p>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Password Strength
    const passInput = document.getElementById('password');
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

    // Submits
    const form = document.getElementById('registerForm');
    const submitBtn = document.getElementById('regSubmitBtn');
    
    form.addEventListener('submit', function (event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        } else {
            submitBtn.classList.add('disabled');
            document.getElementById('regSubmitSpinner').classList.remove('d-none');
            document.getElementById('regSubmitText').textContent = ' Creating Account...';
        }
        form.classList.add('was-validated');
    }, false);
});
</script>

<?php include __DIR__ . '/../includes/auth_footer.php'; ?>
