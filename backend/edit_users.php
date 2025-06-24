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

if (!isset($_GET['id'])) {
    echo "No user ID provided.";
    exit();
}

$userId = $_GET['id'];

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $status = $_POST['status'];
    $orgId = $_POST['applied_org_id'] !== '' ? intval($_POST['applied_org_id']) : null;

    $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, application_status = ?, applied_org_id = ? WHERE id = ?");
    $stmt->bind_param("sssii", $name, $email, $status, $orgId, $userId);
    if ($stmt->execute()) {
        header("Location: admin_dashboard.php");
        exit();
    } else {
        echo "Error updating user: " . $stmt->error;
    }
}

// Fetch current user info
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Fetch orgs
$orgs = $conn->query("SELECT id, name FROM organisation");
?>

<h2>Edit Job Seeker</h2>
<form method="POST">
    <label>Name:</label><br>
    <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>"><br><br>

    <label>Email:</label><br>
    <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>"><br><br>

    <label>Application Status:</label><br>
    <select name="status">
        <option value="pending" <?= $user['application_status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
        <option value="accepted" <?= $user['application_status'] === 'accepted' ? 'selected' : '' ?>>Accepted</option>
        <option value="rejected" <?= $user['application_status'] === 'rejected' ? 'selected' : '' ?>>Rejected</option>
    </select><br><br>

    <label>Applied Organisation:</label><br>
    <select name="applied_org_id">
        <option value="">None</option>
        <?php while ($org = $orgs->fetch_assoc()): ?>
            <option value="<?= $org['id'] ?>" <?= $user['applied_org_id'] == $org['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($org['name']) ?>
            </option>
        <?php endwhile; ?>
    </select><br><br>

    <button type="submit">Update Job Seeker</button>
</form>

<a href="admin_dashboard.php">Back to Dashboard</a>
