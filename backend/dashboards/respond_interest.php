<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'users') {
    header("Location: ../login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$interestId = $_POST['interest_id'] ?? null;
$response = $_POST['response'] ?? null;

if (!$interestId || !in_array($response, ['accepted', 'rejected'])) {
    $_SESSION['error'] = "Invalid request.";
    header("Location: users_dashboard.php");
    exit();
}

// Verify interest belongs to this user and is pending, get organisation_id
$stmt = $conn->prepare("SELECT status, organisation_id FROM interests WHERE id = ? AND jobseeker_id = ?");
$stmt->bind_param("ii", $interestId, $userId);
$stmt->execute();
$stmt->bind_result($status, $orgId);
if (!$stmt->fetch()) {
    $_SESSION['error'] = "Interest not found.";
    $stmt->close();
    header("Location: users_dashboard.php");
    exit();
}
$stmt->close();

if ($status !== 'pending') {
    $_SESSION['error'] = "This interest has already been responded to.";
    header("Location: users_dashboard.php");
    exit();
}

// Update interest status
$stmt = $conn->prepare("UPDATE interests SET status = ? WHERE id = ?");
$stmt->bind_param("si", $response, $interestId);

if ($stmt->execute()) {
    // Create notification message based on response
    $msg = $response === 'accepted' ? 
        "Job seeker has accepted your job offer (Interest ID: $interestId)." : 
        "Job seeker has rejected your job offer (Interest ID: $interestId).";

    // Insert notification for the organization
    $notifStmt = $conn->prepare("INSERT INTO notifications (user_id, organisation_id, interest_id, message, status, created_at) VALUES (?, ?, ?, ?, 'unread', NOW())");
    $notifStmt->bind_param("iiis", $userId, $orgId, $interestId, $msg);
    $notifStmt->execute();
    $notifStmt->close();

    $_SESSION['success'] = "You have successfully " . ($response === 'accepted' ? "accepted" : "rejected") . " the offer.";
} else {
    $_SESSION['error'] = "Failed to update interest status.";
}
$stmt->close();

header("Location: users_dashboard.php");
exit();
?>
