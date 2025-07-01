<?php
session_start();
require_once '../db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'organisation') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$orgId = $_SESSION['user_id'];
$slotId = $_POST['slot_id'] ?? null;

if (!$slotId) {
    echo json_encode(['success' => false, 'message' => 'Missing slot ID']);
    exit();
}

// Check if slot belongs to org and is not booked
$stmt = $conn->prepare("SELECT is_booked FROM org_available_slots WHERE id = ? AND organisation_id = ?");
$stmt->bind_param("ii", $slotId, $orgId);
$stmt->execute();
$stmt->bind_result($isBooked);
if (!$stmt->fetch()) {
    $stmt->close();
    echo json_encode(['success' => false, 'message' => 'Slot not found']);
    exit();
}
$stmt->close();

if ($isBooked) {
    echo json_encode(['success' => false, 'message' => 'Cannot delete booked slot']);
    exit();
}

// Delete slot
$stmt = $conn->prepare("DELETE FROM org_available_slots WHERE id = ? AND organisation_id = ?");
$stmt->bind_param("ii", $slotId, $orgId);
if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
$stmt->close();
exit();
