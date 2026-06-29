<?php
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
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

// CSRF Security Protection Helpers
function generateCsrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrfInput(): string {
    $token = generateCsrfToken();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
}

function verifyCsrfToken(): bool {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf_token'] ?? '';
        if (empty($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            return false;
        }
    }
    return true;
}

