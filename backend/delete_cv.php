<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$userId = $_SESSION['user_id'];

// Fetch current CV filename
$stmt = $conn->prepare("SELECT cv FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($cv);
$stmt->fetch();
$stmt->close();

if (!empty($cv)) {
    $filePath = __DIR__ . '/../../' . $cv;

    // Delete the file from the server if it exists
    if (file_exists($filePath)) {
        unlink($filePath);
    }

    // Remove the reference from the DB
    $update = $conn->prepare("UPDATE users SET cv = NULL WHERE id = ?");
    $update->bind_param("i", $userId);
    $update->execute();
    $update->close();
}

// Redirect back to dashboard
header("Location: users_dashboard.php");
exit();
?>