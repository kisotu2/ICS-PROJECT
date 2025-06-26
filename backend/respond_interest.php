<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$interest_id = $_GET['interest_id'] ?? null;
$action = $_GET['action'] ?? null;

if (!$interest_id || !in_array($action, ['approve', 'reject'])) {
    die("Invalid action.");
}

$status = $action === 'approve' ? 'approved' : 'rejected';

$stmt = $conn->prepare("UPDATE interests SET status = ?, notified = 1 WHERE id = ?");
$stmt->bind_param("si", $status, $interest_id);
if ($stmt->execute()) {
    echo "You have successfully {$status}d the interest.";
} else {
    echo "Failed to update.";
}
$stmt->close();
?>
<a href="jobseeker_dashboard.php">← Back to Dashboard</a>
