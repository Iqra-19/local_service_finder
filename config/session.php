<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

function getBaseUrl(): string {
    $docRoot = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
    $dir = str_replace('\\', '/', dirname(__DIR__));
    $baseUrl = str_replace($docRoot, '', $dir);
    return rtrim($baseUrl, '/');
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        header('Location: ' . getBaseUrl() . '/pages/login.php');
        exit;
    }
}

function requireRole(string $role): void {
    requireLogin();
    if (($_SESSION['user_role'] ?? '') !== $role) {
        header('Location: ' . getBaseUrl() . '/pages/login.php');
        exit;
    }
}

function redirectByRole(): void {
    if (!isLoggedIn()) return;
    $role = $_SESSION['user_role'] ?? '';
    if ($role === 'user') {
        header('Location: ' . getBaseUrl() . '/pages/user_dashboard.php');
    } elseif ($role === 'provider') {
        header('Location: ' . getBaseUrl() . '/pages/provider_dashboard.php');
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
