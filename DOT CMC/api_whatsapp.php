<?php
include 'db_connect.php';

// A secret key to ensure only your Node.js bot can access this API
$secret_key = "dotcmc_whatsapp_secret_2026";

if (!isset($_GET['key']) || $_GET['key'] !== $secret_key) {
    die(json_encode(['status' => 'error', 'message' => 'Unauthorized access']));
}

$action = isset($_GET['action']) ? $_GET['action'] : '';

header('Content-Type: application/json');

if ($action === 'get_pending') {
    // Fetch up to 10 pending messages
    $query = $conn->query("SELECT * FROM whatsapp_queue WHERE status = 'pending' ORDER BY created_at ASC LIMIT 10");
    $messages = [];
    while ($row = $query->fetch_assoc()) {
        $messages[] = $row;
    }
    echo json_encode(['status' => 'success', 'data' => $messages]);
} 
elseif ($action === 'mark_sent' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // The Node.js bot will send the IDs of the messages it successfully sent
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (isset($input['ids']) && is_array($input['ids']) && count($input['ids']) > 0) {
        $ids = implode(',', array_map('intval', $input['ids']));
        $update = $conn->query("UPDATE whatsapp_queue SET status = 'sent' WHERE id IN ($ids)");
        if ($update) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update database']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No IDs provided']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
}
?>
