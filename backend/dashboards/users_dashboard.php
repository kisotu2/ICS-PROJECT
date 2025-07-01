<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once '../db.php';

// ✅ Access control
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'users') {
    header("Location: ../login.php");
    exit();
}

$userId = $_SESSION['user_id'];

// Fetch user details
$query = $conn->prepare("
    SELECT name, email, headline, skills, experience, education, cv, passport, documents
    FROM users WHERE id = ?
");
$query->bind_param("i", $userId);
$query->execute();
$query->bind_result($name, $email, $headline, $skills, $experience, $education, $cv, $passport, $documents);
$query->fetch();
$query->close();

// Profile completion logic
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

// 🔔 Fetch interests and org names
$notifStmt = $conn->prepare("
    SELECT 
        n.notification_id AS notif_id, 
        o.name AS org_name, 
        i.status, 
        i.created_at, 
        i.id AS interest_id,
        i.job_title,
        i.job_description
    FROM notifications n
    JOIN organizations o ON n.organisation_id = o.id
    JOIN interests i ON n.interest_id = i.id
    WHERE n.user_id = ? 
    ORDER BY n.created_at DESC
");
$notifStmt->bind_param("i", $userId);
$notifStmt->execute();
$result = $notifStmt->get_result();
$interests = $result->fetch_all(MYSQLI_ASSOC);
$notifStmt->close();

// ✅ Mark notifications as seen
if (!empty($interests)) {
    $updateNotif = $conn->prepare("UPDATE notifications SET status = 'seen' WHERE user_id = ? AND status = 'pending'");
    $updateNotif->bind_param("i", $userId);
    $updateNotif->execute();
    $updateNotif->close();
}
?>

<!DOCTYPE html>
<html>
    <div class="container">
<head>
    <title>Job Seeker Dashboard</title>
    <style>
        body {
    font-family: Arial, sans-serif;
    padding: 0;
    margin: 0;
    background: #f0f0f0; /* Light Gray (secondary) */
    color: #333333; /* Dark Gray (text) */
}

nav {
    background: #003366; /* Navy Blue */
    padding: 0 20px;
    height: 60px;          /* Fixed height */
    display: flex;
    align-items: stretch;  /* Stretch children to fill height */
}

nav img.logo {
    height: 100%;          /* Fill the nav bar height */
    width: auto;           /* Keep aspect ratio */
    display: block;        /* Remove inline spacing */
    margin-right: 15px;
}

nav a {
    color: #ffffff;j
    text-decoration: none;
    margin-right: 20px;
    font-weight: bold;
    display: flex;
    align-items: center;   /* Vertically center links text */
}yumvv
nav a:hover {
    text-decoration: underline;
    color: #ff6600; /* Orange (accent) */
}

.container {
    padding: 20px;
    max-width: 900px;
    margin: 0 auto;
}

h2, h3 {
    color: #333333; /* Dark Gray (text) */
}

.section, .profile-info {
    margin-bottom: 20px;
    padding: 15px;
    background: #ffffff; /* Soft White (secondary) */
    border-radius: 10px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
}

.profile-info img {
    max-height: 100px;
    border-radius: 8px;
    margin-top: 10px;
}

label {
    font-weight: bold;
    display: block;
    margin-top: 15px;
    color: #333333; /* Dark Gray (text) */
}

input[type="file"], button {
    margin-top: 10px;
}

.doc-list {
    margin-top: 10px;
}

.progress {
    background: #f0f0f0; /* Light Gray (secondary) */
    border-radius: 5px;
    overflow: hidden;
    height: 20px;
    margin-top: 5px;
}

.progress-bar {
    background: #ff6600; /* Orange (accent) */
    height: 100%;
    text-align: center;
    color: white;
    font-size: 12px;
    line-height: 20px;
}

.notif-banner {
    background: #f0f0f0; /* Light Gray (secondary) */
    border-left: 5px solid #0047AB; /* Royal Blue (primary) */
    padding: 15px;
    margin-bottom: 20px;
}

.notif-banner ul {
    margin: 10px 0 0 0;
    padding-left: 20px;
}

.notif-banner li {
    margin-bottom: 5px;
}

.job-offer {
    background: #ffffff; /* Soft White (secondary) */
    border: 1px solid #d4af37; /* Gold (accent) */
    padding: 15px;
    margin-top: 15px;
    border-radius: 8px;
    color: #333333; /* Dark Gray (text) */
}

.job-offer form {
    margin-top: 10px;
}

.job-offer button {
    margin-right: 10px;
    background-color: #0047AB; /* Royal Blue (primary) */
    color: #ffffff;
    border: none;
    padding: 8px 12px;
    border-radius: 5px;
    cursor: pointer;
    font-weight: bold;
    transition: background-color 0.3s ease;
}

.job-offer button:hover {
    background-color: #003366; /* Navy Blue (primary) */
}

.btn-slot {
    display: inline-block;
    padding: 10px 20px;
    background-color: #0047AB; /* Royal Blue (primary) */
    color: #ffffff;
    text-decoration: none;
    border-radius: 5px;
    font-weight: bold;
}

.btn-slot:hover {
    background-color: #003366; /* Navy Blue (primary) */
}

    </style>
</head>
<body>

<?php if (!empty($_SESSION['success'])): ?>
    <div style="background: #d4edda; color: #155724; padding: 10px; margin: 15px 0; border-radius: 5px;">
        <?= htmlspecialchars($_SESSION['success']) ?>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<?php if (!empty($_SESSION['error'])): ?>
    <div style="background: #f8d7da; color: #721c24; padding: 10px; margin: 15px 0; border-radius: 5px;">
        <?= htmlspecialchars($_SESSION['error']) ?>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<nav>
    <a href="users_dashboard.php">
        <img src="../../Asset/logo.png" alt="Logo" class="logo">
        🏠 Dashboard
    </a>
    <a href="edit_profile.php">✏️ Edit Profile</a>
    <a href="job_offers.php">📩 Job Offers</a>
    <a href="interviews.php">📅 Interviews</a>
    <a href="messages.php">💬 Messages</a>
    <a href="notifications.php">🔔 Notifications</a>
    <a href="settings.php">⚙️ Settings</a>
    <a href="../logout.php">🚪 Logout</a>
</nav>

<div class="container">
    <h2>Welcome, <?= htmlspecialchars($name) ?></h2>

    <?php if (!empty($interests)): ?>
        <div class="notif-banner">
            <strong>📢 You have new interest(s) from the following organisations:</strong>
            <ul>
                <?php foreach ($interests as $n): ?>
                    <li><?= htmlspecialchars($n['org_name']) ?></li>
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
            <ul class="doc-list">
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
        <h3>📩 Job Offers</h3>
        <?php foreach ($interests as $interest): ?>
            <div class='job-offer'>
                <p><strong><?= htmlspecialchars($interest['org_name']) ?></strong> has shown interest in you for the role:</p>
                <h4><?= htmlspecialchars($interest['job_title']) ?></h4>
                <p><?= nl2br(htmlspecialchars($interest['job_description'])) ?></p>
                <p>Status: <?= htmlspecialchars($interest['status']) ?></p>
                <?php if ($interest['status'] === 'pending'): ?>
                    <form method="POST" action="respond_interest.php">
                        <input type="hidden" name="interest_id" value="<?= $interest['interest_id'] ?>">
                        <button type="submit" name="response" value="accepted">Accept</button>
                        <button type="submit" name="response" value="rejected">Reject</button>
                    </form>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="section">
        <h3>Upcoming Interviews</h3>
        
        <p><a class="btn-slot" href="view_slots.php">View Available Interview Slots</a></p>
    </div>

    <div class="section">
        <h3>Help & Support</h3>
        <p>Need assistance? <a href="support.php">Visit the Help Center</a> or <a href="contact.php">Contact Support</a>.</p>
    </div>
</div>

</body>
</html>
