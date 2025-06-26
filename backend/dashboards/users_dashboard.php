<?php
// Fetch organisations that showed interest
$interestQuery = $conn->prepare("
    SELECT i.id AS interest_id, o.name AS org_name, o.email AS org_email, i.status
    FROM interests i
    JOIN organisation o ON i.organisation_id = o.id
    WHERE i.jobseeker_id = ?
");
$interestQuery->bind_param("i", $userId);
$interestQuery->execute();
$interestResult = $interestQuery->get_result();
?>

<h3>Organisations Interested in You</h3>

<?php if ($interestResult->num_rows > 0): ?>
    <table border="1" cellpadding="10" cellspacing="0">
        <tr>
            <th>Organisation Name</th>
            <th>Email</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
        <?php while ($interest = $interestResult->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($interest['org_name']) ?></td>
                <td><?= htmlspecialchars($interest['org_email']) ?></td>
                <td><?= htmlspecialchars(ucfirst($interest['status'])) ?></td>
                <td>
                    <?php if ($interest['status'] === 'pending'): ?>
                        <a href="respond_interest.php?id=<?= $interest['interest_id'] ?>&action=approve">Approve</a> |
                        <a href="respond_interest.php?id=<?= $interest['interest_id'] ?>&action=decline">Decline</a>
                    <?php else: ?>
                        <em>No actions available</em>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
<?php else: ?>
    <p>No organisations have shown interest yet.</p>
<?php endif; ?>


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
