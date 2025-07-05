<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once '../db.php';

// Access control
if (empty($_SESSION['org_id']) || ($_SESSION['role'] ?? '') !== 'organisation') {
    die("Access denied. Please login as an organization user.");
}

$user_id = $_SESSION['org_id'];
$org_id = $_SESSION['organization_id'] ?? null;

function loadOrganization($conn, $org_id) {
    if (!$org_id) return null;
    $stmt = $conn->prepare("SELECT * FROM organizations WHERE id = ?");
    $stmt->bind_param("i", $org_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

$organization = loadOrganization($conn, $org_id);

if (!$organization && $org_id) {
    unset($_SESSION['organization_id']);
    $org_id = null;
    $organization = null;
}

// Fetch job seekers who accepted offers
$stmt = $conn->prepare("SELECT i.id AS interest_id, u.id AS user_id, u.name, u.headline, u.skills, u.passport, i.job_title, i.job_description 
                        FROM interests i 
                        JOIN users u ON i.jobseeker_id = u.id 
                        WHERE i.organisation_id = ? AND i.status = 'accepted' 
                        ORDER BY i.created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$acceptedSeekers = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Accepted Job Seekers</title>
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f4f6fa;
            color: #2c3e50;
            display: flex;
        }

        .main-content {
            margin-left: 240px;
            padding: 40px;
            flex-grow: 1;
        }

        .card {
            background: #fff;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }

        h2 {
            margin-top: 0;
            color: #003366;
        }

        img.passport {
            max-height: 80px;
            border-radius: 8px;
            margin-bottom: 10px;
        }

        .btn {
            padding: 10px 16px;
            background: #0047AB;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-top: 12px;
            font-weight: bold;
        }

        .btn:hover {
            background: #002f6c;
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 20px;
            }
        }
    </style>
</head>
<body>

<?php include 'org_sidebar.php'; ?> <!-- ✅ Correct sidebar for org -->

<div class="main-content">
    <h2>Job Seekers Who Accepted Offers</h2>

    <?php if (empty($acceptedSeekers)): ?>
        <p>No job seekers have accepted your offers yet.</p>
    <?php else: ?>
        <?php foreach ($acceptedSeekers as $seeker): ?>
            <div class="card">
                <?php if (!empty($seeker['passport'])): ?>
                    <img src="../../<?= htmlspecialchars($seeker['passport']) ?>" alt="Passport Photo" class="passport">
                <?php endif; ?>
                <h3><?= htmlspecialchars($seeker['name']) ?></h3>
                <p><strong>Headline:</strong> <?= htmlspecialchars($seeker['headline']) ?></p>
                <p><strong>Skills:</strong> <?= htmlspecialchars($seeker['skills']) ?></p>
                <p><strong>Job Title:</strong> <?= htmlspecialchars($seeker['job_title']) ?></p>
                <p><strong>Description:</strong><br><?= nl2br(htmlspecialchars($seeker['job_description'])) ?></p>
                <a class="btn" href="manage_slots.php?jobseeker_id=<?= $seeker['user_id'] ?>&interest_id=<?= $seeker['interest_id'] ?>">Schedule Interview</a>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

</body>
</html>
