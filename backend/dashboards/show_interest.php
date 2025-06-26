<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once '../db.php';

if (!isset($_SESSION['org_id'])) {
    header("Location: ../login.php");
    exit();
}

$org_id = $_SESSION['org_id'];
$jobseeker_id = $_GET['id'] ?? null;

if (!$jobseeker_id) {
    die("Invalid job seeker.");
}

// Check if already interested
$check = $conn->prepare("SELECT * FROM interests WHERE organisation_id = ? AND jobseeker_id = ?");
$check->bind_param("ii", $org_id, $jobseeker_id);
$check->execute();
$result = $check->get_result();
$existing = $result->fetch_assoc();

if (!$existing) {
    // Insert new interest
    $insert = $conn->prepare("INSERT INTO interests (organisation_id, jobseeker_id, status, notified) VALUES (?, ?, 'pending', 0)");
    $insert->bind_param("ii", $org_id, $jobseeker_id);
    if ($insert->execute()) {
        echo "Interest shown successfully. Waiting for job seeker's approval.";
    } else {
        echo "Error showing interest.";
    }
    $insert->close();
} else {
    echo "You have already shown interest. Current status: <strong>" . htmlspecialchars($existing['status']) . "</strong>";
}

$check->close();
?>
<a href="organisation_dashboard.php">← Back to Dashboard</a>
