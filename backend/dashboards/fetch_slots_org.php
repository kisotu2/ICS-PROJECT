<?php
session_start();
require_once '../db.php';

header('Content-Type: application/json');

// ✅ Ensure session and role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'organisation') {
    http_response_code(403);
    echo json_encode([]);
    exit();
}

$orgId = $_POST['organisation_id'] ?? null;
if (!$orgId) {
    echo json_encode([]);
    exit();
}

// Fetch slots
$stmt = $conn->prepare("SELECT id, slot_date, slot_start, slot_end, is_booked, title FROM org_available_slots WHERE organisation_id = ?");
$stmt->bind_param("i", $orgId);
$stmt->execute();
$result = $stmt->get_result();

$events = [];
while ($row = $result->fetch_assoc()) {
    $start = $row['slot_date'] . 'T' . $row['slot_start'];
    $end = $row['slot_date'] . 'T' . $row['slot_end'];
    $events[] = [
        'id' => $row['id'],
        'title' => $row['is_booked'] ? 'Booked' : $row['title'],
        'start' => $start,
        'end' => $end,
        'color' => $row['is_booked'] ? '#d9534f' : '#378006',
        'editable' => !$row['is_booked'],
        'extendedProps' => ['isBooked' => (bool)$row['is_booked']]
    ];
}

$stmt->close();
echo json_encode($events);
exit();
