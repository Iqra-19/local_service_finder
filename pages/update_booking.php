<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/db.php';
requireRole('provider');

$bookingId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_SPECIAL_CHARS);

if (!$bookingId || !$action) {
    setFlash('danger', 'Invalid parameters.');
    header('Location: booking_requests.php');
    exit;
}

// Map the specific action string to a target status and required current status
$allowedTransitions = [
    'accept' => ['target' => 'accepted', 'require' => 'pending'],
    'reject' => ['target' => 'rejected', 'require' => 'pending'],
    'complete' => ['target' => 'completed', 'require' => 'accepted']
];

if (!isset($allowedTransitions[$action])) {
    setFlash('danger', 'Invalid action.');
    header('Location: booking_requests.php');
    exit;
}

$transition = $allowedTransitions[$action];

// Identify booking and ensure the logged in user is the relevant provider
$stmt = $pdo->prepare("SELECT id, status FROM bookings WHERE id = ? AND provider_id = ?");
$stmt->execute([$bookingId, $_SESSION['user_id']]);
$booking = $stmt->fetch();

if (!$booking) {
    setFlash('danger', 'Booking not found or access denied.');
} elseif ($booking['status'] !== $transition['require']) {
    setFlash('danger', "Invalid state transition. Booking must be {$transition['require']} to proceed.");
} else {
    // Execute state change
    $updateStmt = $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?");
    $updateStmt->execute([$transition['target'], $bookingId]);
    setFlash('success', "Booking has been successfully {$transition['target']}.");
}

header('Location: booking_requests.php');
exit;
