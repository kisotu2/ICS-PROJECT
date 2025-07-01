<?php
session_start();
require_once '../db.php';

header('Content-Type: application/json');

$slotId = $_POST['slot_id'] ?? null;
$jobseekerId = $_POST['jobseeker_id'] ?? null;

if (!$slotId || !$jobseekerId) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit();
}

// Check if already booked
$stmt = $conn->prepare("SELECT is_booked FROM org_available_slots WHERE id = ?");
$stmt->bind_param("i", $slotId);
$stmt->execute();
$stmt->bind_result($isBooked);
$stmt->fetch();
$stmt->close();

if ($isBooked) {
    echo json_encode(['success' => false, 'message' => 'Slot already booked']);
    exit();
}

// Mark slot as booked and link to jobseeker
$stmt = $conn->prepare("UPDATE org_available_slots SET is_booked = 1, jobseeker_id = ? WHERE id = ?");
$stmt->bind_param("ii", $jobseekerId, $slotId);
$success = $stmt->execute();
$stmt->close();

if ($success) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to book slot']);
}
