<?php
session_start();
$org_id = $_SESSION['org_id'] ?? 1;

$conn = new mysqli('localhost', 'root', '', 'jobseekers');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle approval
if (isset($_POST['approve'])) {
    $interview_id = (int)$_POST['interview_id'];
    $conn->query("UPDATE interviews SET status = 'approved' WHERE id = $interview_id");
}

$pending = $conn->query("SELECT * FROM interviews WHERE organisation_id = $org_id AND status = 'pending'");
$approved = $conn->query("SELECT * FROM interviews WHERE organisation_id = $org_id AND status = 'approved'");
$passed = $conn->query("SELECT * FROM interviews WHERE organisation_id = $org_id AND status = 'passed'");
?>

<h2>Pending Interviews</h2>
<?php while ($row = $pending->fetch_assoc()): ?>
    <div style="border: 1px solid orange; padding: 10px; margin:10px;">
        <strong>Job Seeker ID:</strong> <?= $row['jobseeker_id'] ?><br>
        <strong>Date:</strong> <?= $row['date'] ?><br>
        <form method="POST">
            <input type="hidden" name="interview_id" value="<?= $row['id'] ?>">
            <button type="submit" name="approve">Approve</button>
        </form>
    </div>
<?php endwhile; ?>

<h2>Approved Interviews</h2>
<?php while ($row = $approved->fetch_assoc()): ?>
    <div style="border: 1px solid green; padding: 10px; margin:10px;">
        <strong>Job Seeker ID:</strong> <?= $row['jobseeker_id'] ?><br>
        <strong>Date:</strong> <?= $row['date'] ?><br>
    </div>
<?php endwhile; ?>

<h2>Passed Interviews</h2>
<?php while ($row = $passed->fetch_assoc()): ?>
    <div style="border: 1px solid blue; padding: 10px; margin:10px;">
        <strong>Job Seeker ID:</strong> <?= $row['jobseeker_id'] ?><br>
        <strong>Date:</strong> <?= $row['date'] ?><br>
    </div>
<?php endwhile; ?>
