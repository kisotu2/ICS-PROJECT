<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'organisation') {
    header("Location: ../login.php");
    exit();
}

$orgId = $_SESSION['user_id'];

// Fetch notifications for this organisation where the interest status is accepted or rejected
$stmt = $conn->prepare("
    SELECT n.notification_id, n.message, n.status, n.created_at, n.interest_id, u.name AS jobseeker_name, i.status AS interest_status
    FROM notifications n
    JOIN interests i ON n.interest_id = i.id
    LEFT JOIN users u ON n.user_id = u.id
    WHERE n.organisation_id = ?
      AND i.status IN ('accepted', 'rejected')
    ORDER BY n.created_at DESC
");
$stmt->bind_param("i", $orgId);
$stmt->execute();
$result = $stmt->get_result();
$notifications = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Organisation Notifications</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .notification { border-bottom: 1px solid #ccc; padding: 10px 0; }
        .unread { background-color: #eef6ff; }
        .status { font-weight: bold; }
        .date { color: #666; font-size: 0.9em; }
        .btn-schedule {
            display: inline-block;
            margin-top: 8px;
            padding: 6px 12px;
            background: #007bff;
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
        }
        .btn-schedule:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>

<h2>Your Notifications</h2>

<?php if (empty($notifications)): ?>
    <p>No notifications about accepted or rejected interests yet.</p>
<?php else: ?>
    <?php foreach ($notifications as $note): ?>
        <div class="notification <?= $note['status'] === 'unread' ? 'unread' : '' ?>">
            <p><strong>From:</strong> <?= htmlspecialchars($note['jobseeker_name'] ?? 'System') ?></p>
            <p><?= htmlspecialchars($note['message']) ?></p>
            <p class="status">Interest Status: <?= htmlspecialchars(ucfirst($note['interest_status'])) ?></p>
            <p class="date"><?= htmlspecialchars($note['created_at']) ?></p>

            <?php if ($note['interest_status'] === 'accepted'): ?>
    <a href="manage_slots.php?organisation_id=<?= urlencode($orgId) ?>" class="btn-schedule">
        Manage Interview Slots
    </a>
<?php endif; ?>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

</body>
</html>
