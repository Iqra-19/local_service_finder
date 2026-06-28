<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$isGuest = !isset($_SESSION['user_id']);
$userRole = $_SESSION['user_role'] ?? '';
$dashboardUrl = ($userRole === 'provider') ? '/local_service_finder/pages/provider_dashboard.php' : '/local_service_finder/pages/user_dashboard.php';
?>
<nav class="navbar navbar-expand-xl sticky-top landing-navbar">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="/local_service_finder/">
            <div class="brand-icon-box">
                <i class="fa-solid fa-wrench"></i>
            </div>
            <span class="brand-text">Service<span class="text-primary">Finder</span></span>
        </a>
        <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#landingNav" aria-controls="landingNav" aria-expanded="false" aria-label="Toggle navigation">
            <i class="fa-solid fa-bars-staggered text-dark fs-4"></i>
        </button>
        <div class="collapse navbar-collapse" id="landingNav">
            <ul class="navbar-nav mx-auto align-items-xl-center gap-xl-3 py-3 py-xl-0">
                <li class="nav-item">
                    <a class="nav-link fw-medium" href="/local_service_finder/">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-medium" href="/local_service_finder/pages/browse_services.php">Browse Services</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-medium" href="/local_service_finder/#categories">Categories</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-medium" href="/local_service_finder/#how-it-works">How It Works</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-medium" href="/local_service_finder/#why-us">Why Us</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-medium" href="/local_service_finder/#faq">FAQ</a>
                </li>
            </ul>
            <div class="d-flex align-items-center gap-2 pt-2 pt-xl-0 border-top border-xl-0 mt-2 mt-xl-0">
                <?php if (!$isGuest): ?>
                    <a href="<?= $dashboardUrl ?>" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold shadow-sm">
                        <i class="fa-solid fa-chart-line me-2"></i>Dashboard
                    </a>
                    <a href="/local_service_finder/pages/logout.php" class="btn btn-outline-danger rounded-pill px-3 py-2 fw-semibold">
                        <i class="fa-solid fa-right-from-bracket me-1"></i>Logout
                    </a>
                <?php else: ?>
                    <a href="/local_service_finder/pages/login.php" class="btn btn-link text-dark text-decoration-none fw-semibold px-3">
                        Sign In
                    </a>
                    <a href="/local_service_finder/pages/register.php" class="btn btn-outline-primary rounded-pill px-3 py-2 fw-semibold">
                        Register
                    </a>
                    <a href="/local_service_finder/pages/register.php?role=provider" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold shadow-sm text-nowrap">
                        <i class="fa-solid fa-briefcase me-2"></i>Join as Provider
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

