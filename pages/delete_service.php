<?php
require_once __DIR__ . '/../config/session.php';
requireRole('provider');
require_once __DIR__ . '/../config/db.php';

$serviceId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($serviceId) {
    try {
        $stmt = $pdo->prepare("DELETE FROM services WHERE id = ? AND provider_id = ?");
        $stmt->execute([$serviceId, $_SESSION['user_id']]);
        if ($stmt->rowCount() > 0) {
            setFlash('success', 'Service deleted successfully.');
        } else {
            setFlash('danger', 'Service not found or already deleted.');
        }
    } catch (PDOException $e) {
        setFlash('danger', 'Cannot delete service because it is linked to bookings.');
    }
}

header('Location: manage_services.php');
exit;
