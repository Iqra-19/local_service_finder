<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        header('Location: /local_service_finder/pages/login.php');
        exit;
    }
}

function requireRole(string $role): void {
    requireLogin();
    if (($_SESSION['user_role'] ?? '') !== $role) {
        header('Location: /local_service_finder/pages/login.php');
        exit;
    }
}

function redirectByRole(): void {
    if (!isLoggedIn()) return;
    $role = $_SESSION['user_role'] ?? '';
    if ($role === 'user') {
        header('Location: /local_service_finder/pages/user_dashboard.php');
    } elseif ($role === 'provider') {
        header('Location: /local_service_finder/pages/provider_dashboard.php');
    }
    exit;
}

function setFlash(string $type, string $message): void {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

function getFlash(): ?array {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}
