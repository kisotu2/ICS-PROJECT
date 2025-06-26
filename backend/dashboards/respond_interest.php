<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once '../db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$interest_id = $_GET['interest_id'] ?? null;
$action = $_GET['action'] ?? null;

// Validate input
if (!$interest_id || !in_array($action, ['approve', 'reject'])) {
    die("Invalid action.");
}

// Determine new status
$status = $action === 'approve' ? 'approved' : 'rejected';

// Update the status in the database
$stmt = $conn->prepare("UPDATE interests SET status = ?, notified = 1 WHERE id = ?");
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("si", $status, $interest_id);

if ($stmt->execute()) {
    echo "You have successfully {$status} the interest.";
} else {
    echo "Failed to update interest.";
}
$stmt->close();
?>

<br><a href="users_dashboard.php">← Back to Dashboard</a>
