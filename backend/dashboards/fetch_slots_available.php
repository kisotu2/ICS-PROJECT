<?php
require_once '../db.php';

header('Content-Type: application/json');

// Enable error reporting (optional, for debugging)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Correct table and column names
$sql = "SELECT id, slot_date, slot_start, slot_end FROM org_available_slots WHERE is_booked = 0";
$result = $conn->query($sql);

$slots = [];

while ($row = $result->fetch_assoc()) {
    $slots[] = [
        'id' => $row['id'],
        'title' => 'Available Slot',
        'start' => $row['slot_date'] . 'T' . $row['slot_start'],
        'end' => $row['slot_date'] . 'T' . $row['slot_end']
    ];
}

echo json_encode($slots);
