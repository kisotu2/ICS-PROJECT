<?php
require_once '../db.php';

$jobseeker_id = $_GET['jobseeker_id'];
$org_id = $_GET['org_id'];

$stmt = $conn->prepare("UPDATE interests SET status = 'hired' WHERE jobseeker_id = ? AND organisation_id = ?");
$stmt->bind_param("ii", $jobseeker_id, $org_id);
if ($stmt->execute()) {
    echo "Job seeker marked as hired!";
} else {
    echo "Error updating status.";
}
?>
