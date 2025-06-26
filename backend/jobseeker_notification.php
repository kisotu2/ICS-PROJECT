<?php
<<<<<<< HEAD
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
$jobseeker_id = $_SESSION['jobseeker_id'] ?? 1; // Replace with real session
$conn = new mysqli('localhost', 'root', '', 'organisations_db');

// Handle accept/decline
if (isset($_POST['interest_id'], $_POST['action'])) {
    $stmt = $conn->prepare("UPDATE interests SET status=? WHERE id=? AND jobseeker_id=?");
    $stmt->bind_param("sii", $_POST['action'], $_POST['interest_id'], $jobseeker_id);
    $stmt->execute();
    if ($_POST['action'] === 'accepted') {
        header("Location: book_interview.php?org_id=" . $_POST['org_id']);
        exit;
    }
}

// Fetch interests
$query = "
    SELECT interests.*, o.name AS org_name
    FROM interests
    JOIN organisations_db.organisations o ON o.id = interests.organisation_id
    WHERE jobseeker_id = ?
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $jobseeker_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<h2>Job Offers</h2>
<?php while($row = $result->fetch_assoc()): ?>
    <div style="border:1px solid #ccc; margin:10px; padding:10px;">
        <p><strong><?= htmlspecialchars($row['org_name']) ?></strong> is interested in hiring you.</p>
        <form method="POST">
            <input type="hidden" name="interest_id" value="<?= $row['id'] ?>">
            <input type="hidden" name="org_id" value="<?= $row['organisation_id'] ?>">
            <button name="action" value="accepted">Accept</button>
            <button name="action" value="declined">Decline</button>
        </form>
    </div>
=======
session_start();
require_once '../db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$query = $conn->prepare("SELECT i.id, o.name AS org_name, i.status FROM interests i JOIN organisation o ON i.organisation_id = o.id WHERE i.jobseeker_id = ?");
$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();

while ($row = $result->fetch_assoc()):
?>
    <p><?= htmlspecialchars($row['org_name']) ?> is interested in you.</p>
    <?php if ($row['status'] === 'pending'): ?>
        <a href="respond_interest.php?interest_id=<?= $row['id'] ?>&action=approve">Approve</a> |
        <a href="respond_interest.php?interest_id=<?= $row['id'] ?>&action=reject">Reject</a>
    <?php else: ?>
        <em>Status: <?= htmlspecialchars($row['status']) ?></em>
    <?php endif; ?>
>>>>>>> 4919fcf (first commit)
<?php endwhile; ?>
