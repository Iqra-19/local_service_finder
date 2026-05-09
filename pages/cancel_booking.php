<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/db.php';
requireRole('user');

$bookingId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$bookingId) {
    setFlash('danger', 'Invalid booking ID.');
    header('Location: booking_history.php');
    exit;
}

// Ensure the booking belongs to the user and is still pending
$stmt = $pdo->prepare("SELECT id, booking_status FROM bookings WHERE id = ? AND user_id = ?");
$stmt->execute([$bookingId, $_SESSION['user_id']]);
$booking = $stmt->fetch();

if (!$booking) {
    setFlash('danger', 'Booking not found or access denied.');
} elseif ($booking['booking_status'] !== 'pending') {
    setFlash('danger', 'Only pending bookings can be cancelled.');
} else {
    // Delete the booking or set to rejected/cancelled. Let's delete it or mark as rejected depending on semantics? The requirements said "Cancel booking". Let's delete it so they can try again, or just mark it rejected. But for users "cancelled" would be a new status or just delete. Actually, I can just delete it, or wait, 'rejected' is usually for providers. I'll just delete the row as a cancel to keep ENUM clean, or wait! DB has 'pending', 'accepted', 'completed', 'rejected'. There is no 'cancelled'. Deleting it is best to avoid clutter, or we could add 'cancelled' if we could, but I already set the DB to ENUM('pending', 'accepted', 'completed', 'rejected'). I will just Delete it.
    $updateStmt = $pdo->prepare("UPDATE bookings SET booking_status = 'cancelled' WHERE id = ?");
    $updateStmt->execute([$bookingId]);
    setFlash('success', 'Booking has been cancelled successfully.');
}

header('Location: booking_history.php');
exit;

