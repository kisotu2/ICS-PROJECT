<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../db.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid profile ID.");
}

$id = intval($_GET['id']);
$message = '';

// Process Show Interest form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['show_interest'])) {
    if (!isset($_SESSION['org_id'])) {
        header("Location: ../login.php");
        exit();
    }

    $org_id = $_SESSION['org_id'];
    $jobseeker_id = $id;  // The profile being viewed
    $job_title = trim($_POST['job_title']);
    $job_description = trim($_POST['job_description']);

    if ($job_title !== '' && $job_description !== '') {
        $stmt = $conn->prepare("INSERT INTO interests (organisation_id, jobseeker_id, job_title, job_description) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiss", $org_id, $jobseeker_id, $job_title, $job_description);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            $interest_id = $conn->insert_id;
            $stmt->close();

            $notifStmt = $conn->prepare("INSERT INTO notifications (user_id, organisation_id, interest_id, message) VALUES (?, ?, ?, ?)");
            $notifMessage = "An organisation has shown interest in your profile for the role: $job_title";
            $notifStmt->bind_param("iiis", $jobseeker_id, $org_id, $interest_id, $notifMessage);
            $notifStmt->execute();
            $notifStmt->close();

            $message = "Interest sent successfully!";
        } else {
            $message = "Failed to send interest.";
        }
    } else {
        $message = "Please fill in all fields.";
    }
}

// Fetch the user profile data
$stmt = $conn->prepare("SELECT id, name, email, cv, passport, headline, skills, experience, education FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    die("Profile not found.");
}

$baseUrl = '/jobseekers/ICS-PROJECT/';
function getFullFilePath($relativePath) {
    return __DIR__ . '/../../' . $relativePath;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title><?= htmlspecialchars($user['name']) ?> - Profile</title>
    <style>
        /* Your CSS here (you can copy from the earlier CSS I sent for both profile and form) */

        body {
            font-family: Arial, sans-serif;
            background: #f8f9fa;
            padding: 40px;
            margin: 0;
        }
        .profile-container {
            background: white;
            max-width: 700px;
            margin: auto;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            margin-bottom: 40px;
        }
        .profile-header {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        .profile-header img {
            width: 150px;
            height: 150px;
            border-radius: 10px;
            object-fit: cover;
            border: 1px solid #ccc;
        }
        h1, h2, h3 {
            margin: 0;
        }
        p {
            margin: 10px 0;
        }
        .section {
            margin-top: 20px;
        }
        a.button, button {
            display: inline-block;
            margin-top: 15px;
            padding: 10px 15px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            border: none;
            cursor: pointer;
        }
        a.button:hover, button:hover {
            background: #0056b3;
        }
        form {
            background: white;
            padding: 25px 30px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            max-width: 700px;
            margin: auto;
        }
        label {
            font-weight: 600;
            color: #555;
            display: block;
            margin-bottom: 8px;
            margin-top: 15px;
        }
        input[type="text"],
        textarea {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid #ccc;
            border-radius: 7px;
            font-size: 15px;
            resize: vertical;
            box-sizing: border-box;
        }
        textarea {
            min-height: 100px;
        }
        .message {
            text-align: center;
            font-weight: 600;
            color: green;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<div class="profile-container">
    <div class="profile-header">
        <?php
        $passportPath = $user['passport'];
        $passportFullPath = getFullFilePath($passportPath);
        if (!empty($passportPath) && file_exists($passportFullPath)) {
            echo '<img src="' . htmlspecialchars($baseUrl . $passportPath) . '" alt="Passport">';
        } else {
            echo '<div class="passport-placeholder" style="width:150px; height:150px; background:#ccc; border-radius:10px; display:flex; align-items:center; justify-content:center; color:#666;">No Photo</div>';
        }
        ?>
        <div>
            <h1><?= htmlspecialchars($user['name']) ?></h1>
            <h3><?= htmlspecialchars($user['headline']) ?></h3>
            <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
        </div>
    </div>

    <div class="section">
        <h3>Skills</h3>
        <p><?= htmlspecialchars($user['skills']) ?></p>
    </div>

    <div class="section">
        <h3>Experience</h3>
        <p><?= nl2br(htmlspecialchars($user['experience'])) ?></p>
    </div>

    <div class="section">
        <h3>Education</h3>
        <p><?= nl2br(htmlspecialchars($user['education'])) ?></p>
    </div>

    <div class="section">
        <h3>CV</h3>
        <?php
        $cvPath = $user['cv'];
        $cvFullPath = getFullFilePath($cvPath);
        if (!empty($cvPath) && file_exists($cvFullPath)) {
            echo '<a class="button" href="' . htmlspecialchars($baseUrl . $cvPath) . '" target="_blank">📄 View CV</a>';
        } else {
            echo '<p><em>CV not available</em></p>';
        }
        ?>
    </div>
</div>

<?php if (isset($_SESSION['org_id'])): ?>
    <form method="POST" action="">
        <h2>Show Interest</h2>

        <?php if ($message): ?>
            <div class="message"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <input type="hidden" name="show_interest" value="1">
        <label for="job_title">Job Title</label>
        <input type="text" id="job_title" name="job_title" required>

        <label for="job_description">Job Description</label>
        <textarea id="job_description" name="job_description" required></textarea>

        <button type="submit">Send Interest</button>
    </form>
<?php else: ?>
    <p style="text-align:center;">Please <a href="../login.php">login</a> as an organisation to show interest.</p>
<?php endif; ?>

</body>
</html>
