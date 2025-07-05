<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'users') {
    header("Location: ../login.php");
    exit();
}

$userId = $_SESSION['user_id'];

// Get user profile info
$query = $conn->prepare("SELECT name, email, headline, skills, experience, education, cv, passport, documents FROM users WHERE id = ?");
$query->bind_param("i", $userId);
$query->execute();
$query->bind_result($name, $email, $headline, $skills, $experience, $education, $cv, $passport, $documents);
$query->fetch();
$query->close();

// Profile completion
$fieldsFilled = [
    !empty($name),
    !empty($headline),
    !empty($skills),
    !empty($experience),
    !empty($education),
    !empty($passport),
];
$allFilled = array_reduce($fieldsFilled, fn($carry, $field) => $carry && $field, true);
$completion = $allFilled && !empty($cv) ? 100 : ($allFilled ? 60 : 40);

// Notification banner - organizations with pending interests
$bannerStmt = $conn->prepare("SELECT DISTINCT o.name AS org_name FROM interests i JOIN organizations o ON i.organisation_id = o.id WHERE i.jobseeker_id = ? AND i.status = 'pending'");
$bannerStmt->bind_param("i", $userId);
$bannerStmt->execute();
$bannerResult = $bannerStmt->get_result();
$orgsPending = $bannerResult->fetch_all(MYSQLI_ASSOC);
$bannerStmt->close();

// Interest statistics
$statsStmt = $conn->prepare("SELECT status, COUNT(*) AS total FROM interests WHERE jobseeker_id = ? GROUP BY status");
$statsStmt->bind_param("i", $userId);
$statsStmt->execute();
$statsResult = $statsStmt->get_result();

$pending = $accepted = $rejected = 0;
while ($row = $statsResult->fetch_assoc()) {
    if ($row['status'] === 'pending') $pending = $row['total'];
    elseif ($row['status'] === 'accepted') $accepted = $row['total'];
    elseif ($row['status'] === 'rejected') $rejected = $row['total'];
}
$statsStmt->close();
$totalOffers = $pending + $accepted + $rejected;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Job Seeker Dashboard</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f6fa;
            color: #2c3e50;
            display: flex;
        }
        .content {
            margin-left: 220px;
            padding: 40px 30px;
            flex-grow: 1;
            overflow-y: auto;
            max-width: 1000px;
        }
        .section, .profile-info {
            background: #ffffff;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        h2, h3 {
            margin-top: 0;
        }
        label {
            font-weight: 600;
            margin-top: 15px;
            display: block;
        }
        .profile-info img {
            max-height: 100px;
            border-radius: 8px;
            margin-top: 10px;
        }
        .progress {
            background: #dcdde1;
            border-radius: 10px;
            height: 20px;
            overflow: hidden;
        }
        .progress-bar {
            height: 100%;
            background: #0047AB;
            color: #fff;
            text-align: center;
            font-size: 12px;
            line-height: 20px;
        }
        .notif-banner {
            background: #e1e7f5;
            border-left: 5px solid #2980b9;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
        }
        .summary-box {
            background: #f9f9fc;
            padding: 20px;
            border: 1px solid #dce1f0;
            border-radius: 10px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .summary-box div {
            font-size: 16px;
        }
        .btn-view {
            margin-top: 15px;
            background: #0b1d3a;
            color: white;
            padding: 10px 18px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            display: inline-block;
        }
        .btn-view:hover {
            background: #1d2f53;
        }
        .success, .error {
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        .success {
            background: #d4edda;
            color: #155724;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
        }
        @media (max-width: 768px) {
            .content {
                margin-left: 0;
                padding: 20px;
            }
        }
    </style>
</head>
<body>
<?php include 'sidebar.php'; ?>
<main class="content">
    <h2>Welcome, <?= htmlspecialchars($name) ?></h2>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="success"><?= htmlspecialchars($_SESSION['success']) ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="error"><?= htmlspecialchars($_SESSION['error']) ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php if (!empty($orgsPending)): ?>
        <div class="notif-banner">
            <strong>You have new interest(s) from the following organisations:</strong>
            <ul>
                <?php foreach ($orgsPending as $org): ?>
                    <li><?= htmlspecialchars($org['org_name']) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="section profile-info">
        <h3>Profile Overview</h3>
        <?php if (!empty($passport)): ?>
            <img src="../../<?= htmlspecialchars(ltrim($passport)) ?>" alt="Passport Photo">
        <?php endif; ?>
        <p><strong>Email:</strong> <?= htmlspecialchars($email) ?></p>
        <p><strong>Headline:</strong> <?= htmlspecialchars($headline ?? 'N/A') ?></p>
        <p><strong>Skills:</strong> <?= htmlspecialchars($skills ?? 'N/A') ?></p>
        <p><strong>Experience:</strong><br><?= nl2br(htmlspecialchars($experience ?? 'N/A')) ?></p>
        <p><strong>Education:</strong><br><?= nl2br(htmlspecialchars($education ?? 'N/A')) ?></p>

        <?php if (!empty($cv)): ?>
            <p><strong>CV:</strong>
                <a href="../<?= htmlspecialchars($cv) ?>" target="_blank">View CV</a> |
                <a href="delete_cv.php" onclick="return confirm('Are you sure you want to delete your CV?');" style="color: red;">Delete CV</a>
            </p>
        <?php endif; ?>

        <?php if (!empty($documents)): ?>
            <p><strong>Other Documents:</strong></p>
            <ul>
                <?php foreach (explode(',', $documents) as $doc): ?>
                    <li><a href="../uploads/<?= htmlspecialchars(trim($doc)) ?>" target="_blank"><?= htmlspecialchars(trim($doc)) ?></a></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <p><strong>Profile Completion:</strong> <?= $completion ?>%</p>
        <div class="progress">
            <div class="progress-bar" style="width: <?= $completion ?>%;"><?= $completion ?>%</div>
        </div>
    </div>

    <div class="section">
        <h3>Upload CV</h3>
        <form action="upload_cv.php" method="POST" enctype="multipart/form-data">
            <label>Upload CV</label>
            <input type="file" name="cv" accept=".pdf,.doc,.docx" required>
            <button type="submit" name="upload_cv">Upload CV</button>
        </form>
    </div>

    <div class="section">
        <h3>Summary of Job Offers</h3>
        <div class="summary-box">
            <div>Total Offers Received: <strong><?= $totalOffers ?></strong></div>
            <div>✅ Accepted Offers: <strong><?= $accepted ?></strong></div>
            <div>❌ Rejected Offers: <strong><?= $rejected ?></strong></div>
            <div>⏳ Pending Offers: <strong><?= $pending ?></strong></div>
            <a href="job_offers.php" class="btn-view">View Full Offers</a>
        </div>
    </div>

    <div class="section">
        <h3>Upcoming Interviews</h3>
        <p><a class="btn-view" href="view_slots.php">View Available Interview Slots</a></p>
    </div>

    <div class="section">
        <h3>Help & Support</h3>
        <p>Need assistance? <a href="support.php">Visit the Help Center</a> or <a href="contact.php">Contact Support</a>.</p>
    </div>
</main>
</body>
</html>
