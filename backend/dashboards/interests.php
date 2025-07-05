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

$sql = "
    SELECT i.id, i.job_title, i.job_description, i.status, i.created_at, 
           u.name AS jobseeker_name, u.email AS jobseeker_email
    FROM interests i
    LEFT JOIN users u ON i.jobseeker_id = u.id
    WHERE i.organisation_id = ?
    ORDER BY i.created_at DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $org_id);
$stmt->execute();
$result = $stmt->get_result();
$interests = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Sent Interests</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, sans-serif;
            background: #f4f6fa;
            margin: 0;
            padding: 0;
            display: flex;
        }

        .main {
            margin-left: 240px; /* same as sidebar width */
            padding: 40px;
            flex: 1;
        }

        h2 {
            color: #0a1f44;
            margin-bottom: 30px;
        }

        .interest-box {
            background: white;
            border-left: 6px solid #0a1f44;
            padding: 20px;
            margin-bottom: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.04);
            transition: transform 0.2s ease;
        }

        .interest-box:hover {
            transform: translateY(-2px);
        }

        .interest-box.accepted { border-color: #2e8b57; }
        .interest-box.declined { border-color: #cc0000; }
        .interest-box.pending  { border-color: #f39c12; }

        .interest-box h3 {
            margin-top: 0;
            font-size: 20px;
            color: #0a1f44;
        }

        .status-label {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 5px;
            font-size: 13px;
            font-weight: bold;
            color: white;
            background: gray;
        }

        .status-label.pending  { background: #f39c12; }
        .status-label.accepted { background: #2e8b57; }
        .status-label.declined { background: #cc0000; }

        .created-at {
            font-size: 12px;
            color: #888;
            margin-top: 8px;
        }

        .jobseeker {
            font-size: 14px;
            color: #555;
            margin-top: 10px;
        }
    </style>
</head>
<body>

<?php include 'org_sidebar.php'; ?>

<div class="main">
    <h2>Job Interests You've Sent</h2>

    <?php if (count($interests) > 0): ?>
        <?php foreach ($interests as $interest): ?>
            <div class="interest-box <?= htmlspecialchars($interest['status']) ?>">
                <h3><?= htmlspecialchars($interest['job_title']) ?></h3>
                <p><?= nl2br(htmlspecialchars($interest['job_description'])) ?></p>

                <div class="jobseeker">
                    <strong>Job Seeker:</strong> <?= htmlspecialchars($interest['jobseeker_name'] ?? 'Unknown') ?>
                    (<?= htmlspecialchars($interest['jobseeker_email'] ?? '-') ?>)
                </div>

                <span class="status-label <?= htmlspecialchars($interest['status']) ?>">
                    <?= ucfirst($interest['status']) ?>
                </span>

                <div class="created-at">Sent on: <?= date("F j, Y, g:i a", strtotime($interest['created_at'])) ?></div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>You have not sent any job interests yet.</p>
    <?php endif; ?>
</div>

</body>
</html>
