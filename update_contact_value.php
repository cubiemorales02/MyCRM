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
    $contact_value = floatval($_POST['contact_value'] ?? 0);
    
    $stmt = $conn->prepare("UPDATE contacts SET contact_value = ? WHERE id = ? AND user_id = ?");
    $stmt->bind_param("dii", $contact_value, $contact_id, $_SESSION['user_id']);
    
    if($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
}
?>