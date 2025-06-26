<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once  '../backend/db.php';


if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

$adminId = $_SESSION['admin_id'];

// Fetch admin details
$adminQuery = $conn->prepare("SELECT name, email FROM admin WHERE id = ?");
$adminQuery->bind_param("i", $adminId);
$adminQuery->execute();
$adminResult = $adminQuery->get_result();
$admin = $adminResult->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f0f0f0; padding: 30px; }
        h2 { color: #333; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 40px; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: left; }
        th { background: #eee; }
        section { margin-bottom: 50px; background: #fff; padding: 20px; border-radius: 8px; }
    </style>
</head>
<body>

<h2>Welcome, <?= htmlspecialchars($admin['name']) ?> (Admin)</h2>
<p><strong>Email:</strong> <?= htmlspecialchars($admin['email']) ?></p>
<a href="../backend/logout.php">Logout</a>

<!-- View Job Seekers -->
<section>
    <h3>All Job Seekers</h3>
    <?php
    $result = $conn->query("SELECT * FROM users");
    if ($result->num_rows > 0): ?>
    <table>
        <tr><th>ID</th><th>Name</th><th>Email</th><th>CV</th><th>Status</th><th>Org ID</th><th>Actions</th></tr>
        <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td><?= htmlspecialchars($row['email']) ?></td>
                <td>
                    <?php if ($row['cv']): ?>
                        <a href="../<?= $row['cv'] ?>" target="_blank">View CV</a>
                    <?php else: ?>
                        No CV
                    <?php endif; ?>
                </td>
                <td><?= $row['status'] ?? 'N/A' ?></td>
                <td><?= $row['applied_org_id'] ?? 'N/A' ?></td>
                <td>
                    <a href="edit_user.php?id=<?= $row['id'] ?>">Edit</a> |
                    <a href="delete_user.php?id=<?= $row['id'] ?>" onclick="return confirm('Delete this user?')">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
    <?php else: echo "No job seekers found."; endif; ?>
</section>

<!-- View Organizations -->
<section>
    <h3>All Organisations</h3>
    <?php
    $result = $conn->query("SELECT * FROM organisation");
    if ($result->num_rows > 0): ?>
    <table>
        <tr><th>ID</th><th>Name</th><th>Email</th><th>Actions</th></tr>
        <?php while($org = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $org['id'] ?></td>
                <td><?= htmlspecialchars($org['name']) ?></td>
                <td><?= htmlspecialchars($org['email']) ?></td>
                <td>
                    <a href="edit_org.php?id=<?= $org['id'] ?>">Edit</a> |
                    <a href="delete_org.php?id=<?= $org['id'] ?>" onclick="return confirm('Delete this organisation?')">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
    <?php else: echo "No organisations found."; endif; ?>
</section>

<!-- View Job Seeker Status by Org -->
<section>
    <h3>Job Seeker Status by Organisation</h3>
    <?php
   $query = "
   SELECT 
       users.name AS user_name,
       users.email AS user_email,
       interests.status AS application_status,
       organisation.name AS org_name
   FROM interests
   JOIN users ON interests.jobseeker_id = users.id
   JOIN organisation ON interests.organisation_id = organisation.id
";
$res = $conn->query($query);

    if ($res->num_rows > 0): ?>
    <table>
        <tr><th>Job Seeker</th><th>Email</th><th>Organisation</th><th>Status</th></tr>
        <?php while($row = $res->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['user_name']) ?></td>
                <td><?= htmlspecialchars($row['user_email']) ?></td>
                <td><?= htmlspecialchars($row['org_name']) ?></td>
                <td><?= $row['application_status'] ?></td>
            </tr>
        <?php endwhile; ?>
    </table>
    <?php else: echo "No applications found."; endif; ?>
</section>

</body>
</html>
