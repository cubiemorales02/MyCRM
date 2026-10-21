<?php
session_start();
require_once 'db.php';

header('Content-Type: application/json');

if(!isset($_SESSION['user_id'])) {
    echo json_encode([]);
    exit();
}

$contact_id = $_GET['contact_id'] ?? 0;

$stmt = $conn->prepare("
    SELECT id, note_text, created_at 
    FROM contact_notes 
    WHERE contact_id = ? AND user_id = ? 
    ORDER BY created_at DESC
");
$stmt->bind_param("ii", $contact_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

$notes = [];
while($row = $result->fetch_assoc()) {
    $notes[] = $row;
}

echo json_encode($notes);
?>