<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once '../db.php';

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
        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #eef2f5;
            padding: 0;
            margin: 0;
        }

        header {
            background: #1e88e5;
            color: white;
            padding: 20px 40px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        h2, h3 {
            color: #333;
            margin-bottom: 10px;
        }

        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }

        section {
            margin-bottom: 40px;
            background: #ffffff;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 12px 15px;
            text-align: left;
        }

        th {
            background-color: #f5f5f5;
            color: #444;
            border-bottom: 2px solid #ccc;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tr:hover {
            background-color: #eef6ff;
        }

        a {
            color: #1e88e5;
            text-decoration: none;
            font-weight: bold;
        }

        a:hover {
            text-decoration: underline;
        }

        .logout {
            display: inline-block;
            margin-top: 10px;
            color: #e53935;
            text-decoration: none;
            font-weight: bold;
        }

        .logout:hover {
            text-decoration: underline;
        }

        .actions a {
            margin-right: 10px;
        }

        @media screen and (max-width: 768px) {
            table, thead, tbody, th, td, tr {
                display: block;
            }

            th {
                display: none;
            }

            td {
                position: relative;
                padding-left: 50%;
            }

            td::before {
                position: absolute;
                top: 12px;
                left: 15px;
                width: 45%;
                white-space: nowrap;
                font-weight: bold;
                color: #555;
            }

            tr td:nth-of-type(1)::before { content: "ID"; }
            tr td:nth-of-type(2)::before { content: "Name"; }
            tr td:nth-of-type(3)::before { content: "Email"; }
            tr td:nth-of-type(4)::before { content: "CV"; }
            tr td:nth-of-type(5)::before { content: "Status"; }
            tr td:nth-of-type(6)::before { content: "Org ID"; }
            tr td:nth-of-type(7)::before { content: "Actions"; }
        }
    </style>
</head>
<body>

<header>
    <h2>Welcome, <?= htmlspecialchars($admin['name']) ?> (Admin)</h2>
    <p><?= htmlspecialchars($admin['email']) ?> | <a class="logout" href="../logout.php">Logout</a></p>
</header>

<div class="container">

    <!-- Job Seekers -->
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
                    <td><?= $row['application_status'] ?></td>
                    <td><?= $row['applied_org_id'] ?? 'N/A' ?></td>
                    <td class="actions">
                        <a href="edit_user.php?id=<?= $row['id'] ?>">Edit</a> |
                        <a href="delete_user.php?id=<?= $row['id'] ?>" onclick="return confirm('Delete this user?')">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
        <?php else: echo "No job seekers found."; endif; ?>
    </section>

    <!-- Organizations -->
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
                    <td class="actions">
                        <a href="edit_org.php?id=<?= $org['id'] ?>">Edit</a> |
                        <a href="delete_org.php?id=<?= $org['id'] ?>" onclick="return confirm('Delete this organisation?')">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
        <?php else: echo "No organisations found."; endif; ?>
    </section>

    <!-- Job Seeker Status by Org -->
    <section>
        <h3>Job Seeker Status by Organisation</h3>
        <?php
        $query = "
            SELECT users.name as user_name, users.email as user_email, users.application_status, organisation.name as org_name 
            FROM users 
            LEFT JOIN organisation ON users.applied_org_id = organisation.id
            WHERE users.applied_org_id IS NOT NULL
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

</div>

</body>
</html>
