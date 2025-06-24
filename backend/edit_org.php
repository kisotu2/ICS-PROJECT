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
    echo "No organisation ID provided.";
    exit();
}

$orgId = $_GET['id'];

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);

    $stmt = $conn->prepare("UPDATE organisation SET name = ?, email = ? WHERE id = ?");
    $stmt->bind_param("ssi", $name, $email, $orgId);
    if ($stmt->execute()) {
        header("Location: admin_dashboard.php");
        exit();
    } else {
        echo "Error updating organisation: " . $stmt->error;
    }
}

// Fetch current org info
$stmt = $conn->prepare("SELECT * FROM organisation WHERE id = ?");
$stmt->bind_param("i", $orgId);
$stmt->execute();
$result = $stmt->get_result();
$org = $result->fetch_assoc();
?>

<h2>Edit Organisation</h2>
<form method="POST">
    <label>Name:</label><br>
    <input type="text" name="name" value="<?= htmlspecialchars($org['name']) ?>"><br><br>

    <label>Email:</label><br>
    <input type="email" name="email" value="<?= htmlspecialchars($org['email']) ?>"><br><br>

    <button type="submit">Update Organisation</button>
</form>

<a href="admin_dashboard.php">Back to Dashboard</a>
