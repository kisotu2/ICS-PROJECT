<?php
session_start();
if (!isset($_SESSION['org_id'])) {
    header("Location: ../login.php");
    exit();
}
$org_id = $_SESSION['org_id'];

$conn = new mysqli('localhost', 'root', '', 'jobseekers');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle approval
if (isset($_POST['approve'])) {
    $interview_id = (int)$_POST['interview_id'];
    $conn->query("UPDATE interviews SET status = 'approved' WHERE id = $interview_id");
}

// Fetch interview categories
$pending   = $conn->query("SELECT * FROM interviews WHERE organisation_id = $org_id AND status = 'pending'");
$approved  = $conn->query("SELECT * FROM interviews WHERE organisation_id = $org_id AND status = 'approved'");
$passed    = $conn->query("SELECT * FROM interviews WHERE organisation_id = $org_id AND status = 'passed'");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Interview Status</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background: #f7f7f7;
        }

        h2 {
            color: #333;
            margin-top: 40px;
        }

        .interview-box {
            background: #fff;
            padding: 15px;
            margin-bottom: 15px;
            border-left: 5px solid;
            border-radius: 8px;
        }

        .pending { border-color: orange; }
        .approved { border-color: green; }
        .passed { border-color: blue; }
    </style>
</head>
<body>

<h2>Pending Interviews</h2>
<?php if ($pending && $pending->num_rows > 0): ?>
    <?php while ($row = $pending->fetch_assoc()): ?>
        <div class="interview-box pending">
            <strong>Job Seeker ID:</strong> <?= htmlspecialchars($row['jobseeker_id']) ?><br>
            <strong>Date:</strong> <?= htmlspecialchars($row['date']) ?><br>
            <form method="POST">
                <input type="hidden" name="interview_id" value="<?= $row['id'] ?>">
                <button type="submit" name="approve">Approve</button>
            </form>
        </div>
    <?php endwhile; ?>
<?php else: ?>
    <p>No pending interviews.</p>
<?php endif; ?>


<h2>Approved Interviews</h2>
<?php if ($approved && $approved->num_rows > 0): ?>
    <?php while ($row = $approved->fetch_assoc()): ?>
        <div class="interview-box approved">
            <strong>Job Seeker ID:</strong> <?= htmlspecialchars($row['jobseeker_id']) ?><br>
            <strong>Date:</strong> <?= htmlspecialchars($row['date']) ?><br>
        </div>
    <?php endwhile; ?>
<?php else: ?>
    <p>No approved interviews.</p>
<?php endif; ?>


<h2>Passed Interviews</h2>
<?php if ($passed && $passed->num_rows > 0): ?>
    <?php while ($row = $passed->fetch_assoc()): ?>
        <div class="interview-box passed">
            <strong>Job Seeker ID:</strong> <?= htmlspecialchars($row['jobseeker_id']) ?><br>
            <strong>Date:</strong> <?= htmlspecialchars($row['date']) ?><br>
        </div>
    <?php endwhile; ?>
<?php else: ?>
    <p>No passed interviews.</p>
<?php endif; ?>

</body>
</html>
