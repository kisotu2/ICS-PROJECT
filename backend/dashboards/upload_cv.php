<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require_once '../db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['cv'])) {
    $userId = $_SESSION['user_id'];
    $cv = $_FILES['cv'];

    // Check for upload errors
    if ($cv['error'] !== UPLOAD_ERR_OK) {
        die("Upload failed with error code: " . $cv['error']);
    }

    // Use absolute path instead of relative for upload directory
    $uploadDir = __DIR__ . '/../../uploads/'; // absolute path to uploads/
    $webPath = 'uploads/'; // relative for DB/web

    // Ensure uploads directory exists
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            die("Failed to create upload directory. Check folder permissions.");
        }
    }

    // Sanitize and construct file name
    $cvName = time() . "_" . basename($cv['name']);
    $targetFile = $uploadDir . $cvName;        // full path
    $dbFile = $webPath . $cvName;              // what goes to DB

    // Move the uploaded file
    if (move_uploaded_file($cv['tmp_name'], $targetFile)) {
        // Update the DB
        $stmt = $conn->prepare("UPDATE users SET cv = ? WHERE id = ?");
        $stmt->bind_param("si", $dbFile, $userId);
        if ($stmt->execute()) {
            header("Location: users_dashboard.php");
            exit();
        } else {
            die("Database update failed: " . $stmt->error);
        }
    } else {
        die("Failed to move uploaded file. Check permissions on the uploads directory.");
    }
}
?>
