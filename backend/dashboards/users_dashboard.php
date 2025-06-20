<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once '../db.php';

// Redirect if not logged in
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
?>

<!DOCTYPE html>
<html>
<head>
    <title>Job Seeker Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background: #f9f9f9;
        }

        h2 {
            color: #333;
        }

        .profile-info {
            margin-bottom: 20px;
            padding: 15px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }

        .profile-info img {
            max-height: 100px;
            border-radius: 8px;
        }

        label {
            font-weight: bold;
            display: block;
            margin-top: 15px;
        }

        input[type="file"], button {
            margin-top: 10px;
        }

        .doc-list {
            margin-top: 10px;
        }

        a.logout {
            display: inline-block;
            margin-top: 20px;
            color: #007bff;
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
                <li><a href="../uploads/<?= htmlspecialchars(trim($doc)) ?>" target="_blank"><?= htmlspecialchars(trim($doc)) ?></a></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>

<form method="POST" enctype="multipart/form-data" action="upload_cv.php">
    <label>Upload CV:</label>
    <input type="file" name="cv" accept=".pdf,.doc,.docx" required>
    <button type="submit">Upload</button>
</form>

<a class="logout" href="../logout.php">Logout</a>

</body>
</html>
