<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['org_id'])) {
    header("Location: ../login.php");
    exit();
}

$org_id = $_SESSION['org_id'];
$jobseeker_id = $_GET['jobseeker_id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = $_POST['scheduled_date'];

    $stmt = $conn->prepare("INSERT INTO interviews (jobseeker_id, organisation_id, scheduled_date) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $jobseeker_id, $org_id, $date);
    if ($stmt->execute()) {
        echo "Interview scheduled.";
    } else {
        echo "Failed to schedule.";
    }
    exit();
}
?>

<form method="POST">
    <label>Schedule Date:</label>
    <input type="datetime-local" name="scheduled_date" required>
    <button type="submit">Schedule Interview</button>
</form>
