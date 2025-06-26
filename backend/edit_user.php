<?php
// edit_user.php

session_start();
require_once '../backend/db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

if (!isset($_GET['id'])) {
    echo "No user selected.";
    exit();
}

$id = (int)$_GET['id'];

// Fetch user
$stmt = $conn->prepare("SELECT name, email FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];

    $update = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
    $update->bind_param("ssi", $name, $email, $id);
    if ($update->execute()) {
        header("Location: admin_dashboard.php?user_updated=1");
        exit();
    } else {
        echo "Update failed.";
    }
    $update->close();
}
?>

<!-- HTML Form -->
<form method="POST">
    <label>Name:</label>
    <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required><br>
    <label>Email:</label>
    <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required><br>
    <button type="submit">Update User</button>
</form>