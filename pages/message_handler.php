<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/session.php';

if (!isLoggedIn()) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../config/db.php';
$myId = $_SESSION['user_id'];
$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'fetch_contacts':
            // Fetch contacts list with last message, time, and unread counts
            $sql = "SELECT 
                        u.id as contact_id, 
                        u.name as contact_name, 
                        u.role as contact_role,
                        m.message as last_message, 
                        m.created_at as last_message_time,
                        m.sender_id as last_message_sender,
                        (SELECT COUNT(*) FROM messages WHERE sender_id = u.id AND receiver_id = :my_id AND is_read = 0) as unread_count
                    FROM (
                        SELECT 
                            CASE 
                                WHEN sender_id = :my_id1 THEN receiver_id 
                                ELSE sender_id 
                            END as contact_id,
                            MAX(id) as last_msg_id
                        FROM messages
                        WHERE sender_id = :my_id2 OR receiver_id = :my_id3
                        GROUP BY contact_id
                    ) chat_summary
                    JOIN users u ON chat_summary.contact_id = u.id
                    JOIN messages m ON chat_summary.last_msg_id = m.id
                    ORDER BY last_message_time DESC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'my_id' => $myId,
                'my_id1' => $myId,
                'my_id2' => $myId,
                'my_id3' => $myId
            ]);
            $contacts = $stmt->fetchAll();

            echo json_encode(['status' => 'success', 'contacts' => $contacts]);
            break;

        case 'fetch':
            $contactId = filter_input(INPUT_GET, 'contact_id', FILTER_VALIDATE_INT);
            if (!$contactId) {
                echo json_encode(['status' => 'error', 'message' => 'Invalid contact ID']);
                exit;
            }

            // Mark incoming messages as read
            $updateStmt = $pdo->prepare("UPDATE messages SET is_read = 1 WHERE sender_id = ? AND receiver_id = ? AND is_read = 0");
            $updateStmt->execute([$contactId, $myId]);

            // Fetch chat history
            $stmt = $pdo->prepare("SELECT * FROM messages 
                                   WHERE (sender_id = ? AND receiver_id = ?) 
                                      OR (sender_id = ? AND receiver_id = ?) 
                                   ORDER BY created_at ASC");
            $stmt->execute([$myId, $contactId, $contactId, $myId]);
            $messages = $stmt->fetchAll();

            echo json_encode(['status' => 'success', 'messages' => $messages]);
            break;

        case 'send':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                echo json_encode(['status' => 'error', 'message' => 'POST method required']);
                exit;
            }

            $receiverId = filter_input(INPUT_POST, 'receiver_id', FILTER_VALIDATE_INT);
            $message = trim($_POST['message'] ?? '');

            if (!$receiverId) {
                echo json_encode(['status' => 'error', 'message' => 'Invalid receiver ID']);
                exit;
            }
            if ($receiverId === $myId) {
                echo json_encode(['status' => 'error', 'message' => 'Cannot message yourself']);
                exit;
            }
            if ($message === '') {
                echo json_encode(['status' => 'error', 'message' => 'Message content cannot be empty']);
                exit;
            }

            // Verify receiver exists
            $userCheck = $pdo->prepare("SELECT id FROM users WHERE id = ?");
            $userCheck->execute([$receiverId]);
            if (!$userCheck->fetch()) {
                echo json_encode(['status' => 'error', 'message' => 'Receiver does not exist']);
                exit;
            }

            // Insert message
            $stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
            $stmt->execute([$myId, $receiverId, $message]);

            echo json_encode(['status' => 'success', 'message' => 'Message sent']);
            break;

        case 'unread_total':
            // Fetch total unread count for sidebar indicator
            $stmt = $pdo->prepare("SELECT COUNT(*) as unread_count FROM messages WHERE receiver_id = ? AND is_read = 0");
            $stmt->execute([$myId]);
            $row = $stmt->fetch();
            echo json_encode(['status' => 'success', 'unread_count' => intval($row['unread_count'])]);
            break;

        case 'get_contact_info':
            // Fetch user basic info when loading a direct chat from an external page
            $contactId = filter_input(INPUT_GET, 'contact_id', FILTER_VALIDATE_INT);
            if (!$contactId) {
                echo json_encode(['status' => 'error', 'message' => 'Invalid contact ID']);
                exit;
            }

            $stmt = $pdo->prepare("SELECT id as contact_id, name as contact_name, role as contact_role FROM users WHERE id = ?");
            $stmt->execute([$contactId]);
            $contactInfo = $stmt->fetch();

            if (!$contactInfo) {
                echo json_encode(['status' => 'error', 'message' => 'Contact not found']);
            } else {
                echo json_encode(['status' => 'success', 'contact' => $contactInfo]);
            }
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
