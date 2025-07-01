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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['passport'])) {
    $userId = $_SESSION['user_id'];
    $passport = $_FILES['passport'];

    // Check for upload errors
    if ($passport['error'] !== UPLOAD_ERR_OK) {
        die("Upload failed with error code: " . $passport['error']);
    }

    // Use absolute path instead of relative for upload directory
    $uploadDir = __DIR__ . '/../../uploads/'; // absolute path to uploads/
    $webPath = 'uploads/'; // relative path for DB/web usage

    // Ensure uploads directory exists
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            die("Failed to create upload directory. Check folder permissions.");
        }
    }

    // Sanitize and construct file name
    $fileExtension = strtolower(pathinfo($passport['name'], PATHINFO_EXTENSION));
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];

    if (!in_array($fileExtension, $allowedExtensions)) {
        die("Invalid file type. Allowed types: " . implode(", ", $allowedExtensions));
    }

    $passportName = time() . "_passport_" . basename($passport['name']);
    $targetFile = $uploadDir . $passportName; // full path
    $dbFile = $webPath . $passportName;       // path saved to DB

    // Move the uploaded file
    if (move_uploaded_file($passport['tmp_name'], $targetFile)) {
        // Update the DB
        $stmt = $conn->prepare("UPDATE users SET passport = ? WHERE id = ?");
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