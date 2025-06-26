<?php
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
<?php endwhile; ?>
