<?php
session_start();
require_once '../db.php';

header('Content-Type: application/json');

// ✅ Access check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'organisation') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$orgId      = $_POST['organisation_id'] ?? null;
$slotDate   = $_POST['slot_date'] ?? null;
$slotStart  = $_POST['slot_start'] ?? null;
$slotEnd    = $_POST['slot_end'] ?? null;
$title      = trim($_POST['title'] ?? '');

if (!$orgId || !$slotDate || !$slotStart || !$slotEnd || empty($title)) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit();
}

$stmt = $conn->prepare("
    INSERT INTO org_available_slots 
    (organisation_id, slot_date, slot_start, slot_end, title, is_booked)
    VALUES (?, ?, ?, ?, ?, 0)
");

$stmt->bind_param("issss", $orgId, $slotDate, $slotStart, $slotEnd, $title);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database insert failed']);
}

$stmt->close();
exit();
