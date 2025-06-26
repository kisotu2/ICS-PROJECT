<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

if (!isset($_SESSION['org_id'])) {
    header("Location: ../login.php");
    exit();
}

$org_id = $_SESSION['org_id'];

// Connect to database
$conn = new mysqli('localhost', 'root', '', 'jobseekers');
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Get job seekers who are in the talent pool (i.e., organization showed interest)
$query = "
    SELECT u.id, u.name, u.email, u.cv, u.application_status
    FROM interests i
    JOIN users u ON i.jobseeker_id = u.id
    WHERE i.organisation_id = ?
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $org_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Talent Pool</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f7f7f7;
            padding: 20px;
        }

        h2 {
            color: #333;
        }

        .jobseeker {
            background: #fff;
            border: 1px solid #ccc;
            margin-bottom: 15px;
            padding: 15px;
            border-radius: 8px;
        }

        .jobseeker a {
            color: blue;
            text-decoration: none;
        }

        .jobseeker a:hover {
            text-decoration: underline;
        }

        .status {
            font-style: italic;
            color: #555;
        }
    </style>
</head>
<body>

<h2>Your Talent Pool</h2>

<?php if ($result->num_rows > 0): ?>
    <?php while($row = $result->fetch_assoc()): ?>
        <div class="jobseeker">
            <strong>Name:</strong> <?= htmlspecialchars($row['name']) ?><br>
            <strong>Email:</strong> <?= htmlspecialchars($row['email']) ?><br>
            <strong>Status:</strong> <span class="status"><?= htmlspecialchars($row['application_status'] ?? 'Pending') ?></span><br>
            <?php if (!empty($row['cv'])): ?>
                <a href="../<?= htmlspecialchars($row['cv']) ?>" target="_blank">View CV</a><br>
            <?php else: ?>
                No CV uploaded<br>
            <?php endif; ?>
        </div>
    <?php endwhile; ?>
<?php else: ?>
    <p>No job seekers in your talent pool yet.</p>
<?php endif; ?>

</body>
</html>
