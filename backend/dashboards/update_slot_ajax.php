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
$slotDate = $_POST['slot_date'] ?? null;
$slotStart = $_POST['slot_start'] ?? null;
$slotEnd = $_POST['slot_end'] ?? null;

if (!$slotId || !$slotDate || !$slotStart || !$slotEnd) {
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit();
}

// Check slot belongs to org and is not booked
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
    echo json_encode(['success' => false, 'message' => 'Cannot edit booked slot']);
    exit();
}

// Update slot
$stmt = $conn->prepare("UPDATE org_available_slots SET slot_date = ?, slot_start = ?, slot_end = ? WHERE id = ? AND organisation_id = ?");
$stmt->bind_param("sssii", $slotDate, $slotStart, $slotEnd, $slotId, $orgId);
if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
$stmt->close();
exit();
