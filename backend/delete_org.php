<?php
// delete_org.php

session_start();
require_once '../backend/db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

if (!isset($_GET['id'])) {
    echo "No organisation selected.";
    exit();
}

$id = (int)$_GET['id'];
$stmt = $conn->prepare("DELETE FROM organisation WHERE id = ?");
$stmt->bind_param("i", $id);
if ($stmt->execute()) {
    header("Location: admin_dashboard.php?org_deleted=1");
} else {
    echo "Failed to delete organisation.";
}
$stmt->close();
?>
