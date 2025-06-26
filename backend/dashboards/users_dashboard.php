<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once '../db.php';

// Check if job seeker is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$userId = $_SESSION['user_id'];

// Fetch user details
$query = $conn->prepare("SELECT name, email, cv, passport, documents FROM users WHERE id = ?");
$query->bind_param("i", $userId);
$query->execute();
$query->bind_result($name, $email, $cv, $passport, $documents);
$query->fetch();
$query->close();

// Fetch organisations that showed interest
$interestResult = null;
try {
    $interestQuery = $conn->prepare("
        SELECT i.id AS interest_id, o.name AS org_name, o.email AS org_email, i.status
        FROM interests i
        JOIN organisation o ON i.organisation_id = o.id
        WHERE i.jobseeker_id = ?
    ");
    $interestQuery->bind_param("i", $userId);
    $interestQuery->execute();
    $interestResult = $interestQuery->get_result();
} catch (Exception $e) {
    echo "Error fetching interests: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html>
    <div class="container">
<head>
    <title>Job Seeker Dashboard</title>
    <style>
    html, body {
        height: 100%;
        margin: 0;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: #f2f6fc;
    }

    body {
        display: flex;
        justify-content: center;
        align-items: flex-start; /* Change to center if you want vertical centering too */
        padding: 40px 0;
    }

    .container {
        width: 90%;
        max-width: 950px;
        padding: 20px;
    }

    header {
        background: #1e88e5;
        color: #fff;
        padding: 20px 40px;
        text-align: center;
        border-radius: 10px 10px 0 0;
        box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }

    .card {
        background: #fff;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 30px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }

    h2, h3 {
        color: #333;
    }

    .profile-info img {
        max-height: 120px;
        border-radius: 8px;
        margin-top: 10px;
    }

    label {
        font-weight: bold;
    }

    .doc-list {
        padding-left: 20px;
        margin-top: 10px;
    }

    .doc-list li {
        margin-bottom: 6px;
    }

    form {
        margin-top: 10px;
    }

    input[type="file"] {
        display: block;
        margin-top: 10px;
    }

    button {
        background: #1e88e5;
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 6px;
        cursor: pointer;
        font-weight: bold;
        margin-top: 10px;
    }

    button:hover {
        background: #1669bb;
    }

    .interest-box {
        border: 1px solid #d0d0d0;
        border-left: 5px solid #1e88e5;
        padding: 15px;
        border-radius: 10px;
        margin-top: 15px;
        background-color: #ffffff;
    }

    .interest-box form {
        display: inline-block;
        margin-right: 10px;
    }

    .logout {
        color: #1e88e5;
        text-decoration: none;
        font-weight: bold;
        display: inline-block;
        margin-top: 20px;
    }

    .logout:hover {
        text-decoration: underline;
    }

    .status {
        font-style: italic;
        color: #555;
    }

    .button-red {
        background-color: #e53935;
    }

    .button-red:hover {
        background-color: #c62828;
    }
</style>

</head>
<body>

<h2>Welcome, <?= htmlspecialchars($name) ?></h2>

<div class="profile-info">
    <p><strong>Email:</strong> <?= htmlspecialchars($email) ?></p>

    <?php if (!empty($passport)): ?>
        <p><strong>Passport Photo:</strong></p>
        <img src="../uploads/<?= htmlspecialchars($passport) ?>" alt="Passport Photo">
    <?php endif; ?>

    <?php if (!empty($cv)): ?>
        <p><strong>Uploaded CV:</strong> <a href="../uploads/<?= htmlspecialchars($cv) ?>" target="_blank">View CV</a></p>
    <?php endif; ?>

    <?php if (!empty($documents)): ?>
        <p><strong>Other Documents:</strong></p>
        <ul class="doc-list">
            <?php foreach (explode(',', $documents) as $doc): ?>
                <?php if (trim($doc)): ?>
                    <li><a href="../uploads/<?= htmlspecialchars(trim($doc)) ?>" target="_blank"><?= htmlspecialchars(trim($doc)) ?></a></li>
                <?php endif; ?>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>

<form method="POST" enctype="multipart/form-data" action="upload_cv.php">
    <label>Upload CV:</label>
    <input type="file" name="cv" accept=".pdf,.doc,.docx" required>
    <button type="submit">Upload</button>
</form>

<!-- Interests Section -->
<?php if ($interestResult && $interestResult->num_rows > 0): ?>
    <h3>Organisations Interested in You</h3>
    <?php while ($row = $interestResult->fetch_assoc()): ?>
        <div class="interest-box">
            <strong>Organisation:</strong> <?= htmlspecialchars($row['org_name']) ?><br>
            <strong>Email:</strong> <?= htmlspecialchars($row['org_email']) ?><br>
            <strong>Status:</strong> <span class="status"><?= htmlspecialchars($row['status']) ?></span><br>

            <?php if ($row['status'] === 'pending'): ?>
                <form method="GET" action="respond_interest.php">
                    <input type="hidden" name="interest_id" value="<?= $row['interest_id'] ?>">
                    <input type="hidden" name="action" value="approve">
                    <button type="submit">Approve</button>
                </form>
                <form method="GET" action="respond_interest.php">
                    <input type="hidden" name="interest_id" value="<?= $row['interest_id'] ?>">
                    <input type="hidden" name="action" value="reject">
                    <button type="submit">Decline</button>
                </form>
            <?php endif; ?>
        </div>
    <?php endwhile; ?>
<?php endif; ?>

<a class="logout" href="../logout.php">Logout</a>

</body>
</div>
</html>
