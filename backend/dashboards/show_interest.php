<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['org_id'])) {
    header("Location: ../login.php");
    exit();
}

$org_id = $_SESSION['org_id'];
$jobseeker_id = isset($_GET['jobseeker_id']) ? intval($_GET['jobseeker_id']) : 0;
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jobseeker_id = intval($_POST['jobseeker_id']);
    $job_title = trim($_POST['job_title']);
    $job_description = trim($_POST['job_description']);

    if ($jobseeker_id && $job_title !== '' && $job_description !== '') {
        $stmt = $conn->prepare("INSERT INTO interests (organisation_id, jobseeker_id, job_title, job_description) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiss", $org_id, $jobseeker_id, $job_title, $job_description);
        $stmt->execute();
        $interest_id = $conn->insert_id;
        $stmt->close();

        if ($interest_id) {
            $message = "Interest sent successfully!";

            $notifStmt = $conn->prepare("INSERT INTO notifications (user_id, organisation_id, interest_id, message) VALUES (?, ?, ?, ?)");
            $notifMessage = "An organisation has shown interest in your profile for the role: $job_title";
            $notifStmt->bind_param("iiis", $jobseeker_id, $org_id, $interest_id, $notifMessage);
            $notifStmt->execute();
            $notifStmt->close();
        } else {
            $message = "Failed to send interest.";
        }
    } else {
        $message = "Please fill all fields.";
    }
}
?>

<h2>Send Interest to Job Seeker</h2>

<?php if ($message): ?>
    <p><?= htmlspecialchars($message) ?></p>
<?php endif; ?>

<form method="POST">
    <input type="hidden" name="jobseeker_id" value="<?= htmlspecialchars($jobseeker_id) ?>">

    <p><strong>Job Seeker ID:</strong> <?= htmlspecialchars($jobseeker_id) ?></p>

    <label>Job Title:</label><br>
    <input type="text" name="job_title" required><br><br>

    <label>Job Description:</label><br>
    <textarea name="job_description" rows="5" required></textarea><br><br>

    <button type="submit">Send Interest</button>
    <a href="jobseeker_list.php">Cancel</a>
</form>
