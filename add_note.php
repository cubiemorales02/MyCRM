<?php
session_start();
require_once 'db.php';

header('Content-Type: application/json');

if(!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contact_id = $_POST['contact_id'] ?? 0;
    $note_text = trim($_POST['note_text'] ?? '');
    
    if(empty($note_text)) {
        echo json_encode(['success' => false, 'message' => 'Note cannot be empty']);
        exit();
    }
    
    // Verify contact belongs to user
    $check = $conn->prepare("SELECT id FROM contacts WHERE id = ? AND user_id = ?");
    $check->bind_param("ii", $contact_id, $_SESSION['user_id']);
    $check->execute();
    if($check->get_result()->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Contact not found']);
        exit();
    }
    
    $stmt = $conn->prepare("INSERT INTO contact_notes (contact_id, user_id, note_text) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $contact_id, $_SESSION['user_id'], $note_text);
    
    if($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
}
?>