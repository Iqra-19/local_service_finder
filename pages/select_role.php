<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['pending_google_user'])) {
    header('Location: login.php');
    exit;
}

$pendingUser = $_SESSION['pending_google_user'];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken()) {
        $error = 'Invalid security token. Please try again.';
    } else {
        $role = $_POST['role'] ?? 'user';
        if (!in_array($role, ['user', 'provider'])) {
            $role = 'user';
        }

        try {
            $insert = $pdo->prepare('INSERT INTO users (name, email, google_id, role) VALUES (?, ?, ?, ?)');
            $insert->execute([$pendingUser['name'], $pendingUser['email'], $pendingUser['google_id'], $role]);
            $newUserId = $pdo->lastInsertId();

            session_regenerate_id(true);
            $_SESSION['user_id'] = $newUserId;
            $_SESSION['user_name'] = $pendingUser['name'];
            $_SESSION['user_email'] = $pendingUser['email'];
            $_SESSION['user_role'] = $role;
            $_SESSION['login_attempts'] = 0;
            $_SESSION['login_lockout'] = 0;

            unset($_SESSION['pending_google_user']);

            setFlash('success', 'Account created successfully! Welcome to Local Service Provider.');
            redirectByRole();
            exit;
        } catch (PDOException $e) {
            $error = 'Database error creating account. Please try logging in again.';
            unset($_SESSION['pending_google_user']);
        }
    }
}

$pageTitle = 'Select Account Type';
include __DIR__ . '/../includes/auth_header.php';
?>

<div class="auth-wrapper py-5">
    <div class="glass-card max-w-700 mx-auto" style="max-width: 650px;">
        <div class="card-body p-4 p-md-5">
            <div class="text-center mb-4">
                <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px;">
                    <i class="bi bi-person-gear fs-1"></i>
                </div>
                <h2 class="fw-bold text-dark mb-2">Select Account Type</h2>
                <p class="text-muted">Welcome, <strong><?= htmlspecialchars($pendingUser['name']) ?></strong>! Please select how you will be using our platform to complete your Google setup.</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-glass-danger d-flex align-items-center mb-4 rounded-3 alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2 flex-shrink-0 fs-5"></i>
                    <div><?= htmlspecialchars($error) ?></div>
                    <button type="button" class="btn-close ms-auto opacity-50" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form method="POST" action="" id="roleForm">
                <?= csrfInput() ?>
                <input type="hidden" name="role" id="selectedRole" value="user">

                <div class="row g-3 mb-4">
                    <!-- Option 1: Customer -->
                    <div class="col-md-6">
                        <div class="card h-100 border-2 role-card active p-4 text-center cursor-pointer rounded-4 shadow-sm" id="cardCustomer" onclick="selectAccountType('user')">
                            <div class="icon-wrapper mb-3 text-primary">
                                <i class="bi bi-person-check-fill display-4"></i>
                            </div>
                            <h5 class="fw-bold text-dark mb-2">Customer</h5>
                            <p class="text-muted small mb-0">I want to discover, book, and review local service professionals near me.</p>
                            <div class="badge bg-primary rounded-pill mt-3 px-3 py-2 check-badge"><i class="bi bi-check-lg me-1"></i> Selected</div>
                        </div>
                    </div>

                    <!-- Option 2: Provider -->
                    <div class="col-md-6">
                        <div class="card h-100 border-2 role-card p-4 text-center cursor-pointer rounded-4 shadow-sm" id="cardProvider" onclick="selectAccountType('provider')">
                            <div class="icon-wrapper mb-3 text-success">
                                <i class="bi bi-briefcase-fill display-4"></i>
                            </div>
                            <h5 class="fw-bold text-dark mb-2">Service Provider</h5>
                            <p class="text-muted small mb-0">I want to offer my skilled services, manage bookings, and earn money.</p>
                            <div class="badge bg-success rounded-pill mt-3 px-3 py-2 check-badge d-none"><i class="bi bi-check-lg me-1"></i> Selected</div>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-glass btn-lg w-100 rounded-3 fw-bold py-3 shadow">
                    Complete Google Registration <i class="bi bi-arrow-right ms-2"></i>
                </button>
            </form>
        </div>
    </div>
</div>

<style>
.cursor-pointer { cursor: pointer; transition: all 0.3s ease; }
.role-card { border-color: #e2e8f0; }
.role-card:hover { transform: translateY(-4px); border-color: #3b82f6; }
.role-card.active#cardCustomer { border-color: #2563eb; background-color: rgba(37, 99, 235, 0.03); }
.role-card.active#cardProvider { border-color: #10b981; background-color: rgba(16, 185, 129, 0.03); }
</style>

<script>
function selectAccountType(role) {
    document.getElementById('selectedRole').value = role;
    const cardCust = document.getElementById('cardCustomer');
    const cardProv = document.getElementById('cardProvider');
    const badgeCust = cardCust.querySelector('.check-badge');
    const badgeProv = cardProv.querySelector('.check-badge');

    if (role === 'user') {
        cardCust.classList.add('active');
        cardProv.classList.remove('active');
        badgeCust.classList.remove('d-none');
        badgeProv.classList.add('d-none');
    } else {
        cardProv.classList.add('active');
        cardCust.classList.remove('active');
        badgeProv.classList.remove('d-none');
        badgeCust.classList.add('d-none');
    }
}
</script>

<?php include __DIR__ . '/../includes/auth_footer.php'; ?>
