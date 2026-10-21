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
    $status = $_POST['status'] ?? '';
    
    $valid_status = ['lead', 'customer', 'associate'];
    if(!in_array($status, $valid_status)) {
        echo json_encode(['success' => false, 'message' => 'Invalid status']);
        exit();
    }
    
    $stmt = $conn->prepare("UPDATE contacts SET status = ? WHERE id = ? AND user_id = ?");
    $stmt->bind_param("sii", $status, $contact_id, $_SESSION['user_id']);
    
    if($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
}
?>