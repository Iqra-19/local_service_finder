<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/google_config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

$idToken = $_POST['credential'] ?? '';
$requestedRole = $_POST['role'] ?? 'user';

if (!in_array($requestedRole, ['user', 'provider'])) {
    $requestedRole = 'user';
}

if (empty($idToken)) {
    setFlash('danger', 'Google authentication failed: Missing credential token.');
    header('Location: login.php');
    exit;
}

$googleUser = verifyGoogleIdToken($idToken);

if (!$googleUser) {
    setFlash('danger', 'Google authentication failed: Invalid or expired token.');
    header('Location: login.php');
    exit;
}

$googleId = $googleUser['sub'];
$email = $googleUser['email'];
$name = $googleUser['name'];

try {
    // 1. Check if user already exists with this google_id
    $stmt = $pdo->prepare('SELECT * FROM users WHERE google_id = ?');
    $stmt->execute([$googleId]);
    $user = $stmt->fetch();

    if ($user) {
        // Log in user
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['login_attempts'] = 0;
        $_SESSION['login_lockout'] = 0;

        setFlash('success', 'Welcome back, ' . htmlspecialchars($user['name']) . '!');
        redirectByRole();
    }

    // 2. Check if user exists with the same email address
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        // Link Google ID to existing account and log in
        $update = $pdo->prepare('UPDATE users SET google_id = ? WHERE id = ?');
        $update->execute([$googleId, $user['id']]);

        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['login_attempts'] = 0;
        $_SESSION['login_lockout'] = 0;

        setFlash('success', 'Google account successfully linked! Welcome back, ' . htmlspecialchars($user['name']) . '.');
        redirectByRole();
    }

    // 3. Create a new user account via Google
    $insert = $pdo->prepare('INSERT INTO users (name, email, google_id, role) VALUES (?, ?, ?, ?)');
    $insert->execute([$name, $email, $googleId, $requestedRole]);
    $newUserId = $pdo->lastInsertId();

    session_regenerate_id(true);
    $_SESSION['user_id'] = $newUserId;
    $_SESSION['user_name'] = $name;
    $_SESSION['user_email'] = $email;
    $_SESSION['user_role'] = $requestedRole;
    $_SESSION['login_attempts'] = 0;
    $_SESSION['login_lockout'] = 0;

    setFlash('success', 'Account created successfully with Google! Welcome to Local Service Finder.');
    redirectByRole();

} catch (PDOException $e) {
    error_log('Google Auth Error: ' . $e->getMessage());
    setFlash('danger', 'An error occurred during Google authentication. Please try again.');
    header('Location: login.php');
    exit;
}
