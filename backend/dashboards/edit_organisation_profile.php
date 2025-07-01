<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once '../db.php';

// Make sure user is logged in and is an organization user
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    die("Access denied. Please login.");
}

$org_id = $_SESSION['organization_id'] ?? null;
$organization = null;

// Fetch organization info if it exists
if ($org_id) {
    $stmt = $conn->prepare("SELECT * FROM organizations WHERE id = ?");
    $stmt->bind_param("i", $org_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $organization = $result->fetch_assoc();

    // If organization record doesn't exist, clear session org_id
    if (!$organization) {
        unset($_SESSION['organization_id']);
        $org_id = null;
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name'] ?? '');
    $tagline  = trim($_POST['tagline'] ?? '');
    $website  = trim($_POST['website'] ?? '');
    $industry = trim($_POST['industry'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');
    $address  = trim($_POST['address'] ?? '');

    // Logo upload setup
    $upload_dir = 'uploads/logos/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    $target_file = $organization['logo'] ?? '';

    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $filename = basename($_FILES['logo']['name']);
        $safe_filename = time() . "_" . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $filename);
        $full_path = $upload_dir . $safe_filename;

        if (move_uploaded_file($_FILES['logo']['tmp_name'], $full_path)) {
            // Delete old logo file if different
            if (!empty($organization['logo']) && file_exists($organization['logo']) && $organization['logo'] !== $full_path) {
                unlink($organization['logo']);
            }
            $target_file = $full_path;
        } else {
            echo "<p class='message warning'>Failed to upload new logo. Keeping old logo.</p>";
        }
    }

    if ($org_id) {
        // Update existing organization record
        $sql = "UPDATE organizations SET name=?, tagline=?, website=?, industry=?, location=?, email=?, phone=?, address=?, logo=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssssssi", $name, $tagline, $website, $industry, $location, $email, $phone, $address, $target_file, $org_id);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            echo "<p class='message success'>Organization profile updated successfully!</p>";
        } else {
            echo "<p class='message warning'>No changes made or update failed.</p>";
        }
    } else {
        // Insert new organization record
        $sql = "INSERT INTO organizations (name, tagline, website, industry, location, email, phone, address, logo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssssss", $name, $tagline, $website, $industry, $location, $email, $phone, $address, $target_file);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            $new_org_id = $stmt->insert_id;

            // Link new organization ID to logged-in user in organisation table
            $update_user_sql = "UPDATE organisation SET organization_id = ? WHERE id = ?";
            $update_stmt = $conn->prepare($update_user_sql);
            $update_stmt->bind_param("ii", $new_org_id, $user_id);
            $update_stmt->execute();

            // Store new org ID in session
            $_SESSION['organization_id'] = $new_org_id;
            $org_id = $new_org_id;

            echo "<p class='message success'>Organization profile created successfully!</p>";
        } else {
            echo "<p class='message error'>Failed to create organization profile.</p>";
        }
    }

    // Refresh organization data after update/insert
    if ($org_id) {
        $stmt = $conn->prepare("SELECT * FROM organizations WHERE id = ?");
        $stmt->bind_param("i", $org_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $organization = $result->fetch_assoc();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Edit Organization Profile</title>
<style>
    body {
        font-family: 'Segoe UI', sans-serif;
        background: #f4f4f4;
        padding: 30px;
    }
    form {
        max-width: 700px;
        margin: auto;
        padding: 30px;
        background: white;
        border-radius: 10px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
    h2 {
        color: #333;
        border-bottom: 2px solid #4CAF50;
        padding-bottom: 10px;
    }
    label {
        font-weight: bold;
        margin-top: 15px;
        display: block;
    }
    input[type="text"],
    input[type="url"],
    input[type="email"] {
        width: 100%;
        padding: 10px;
        font-size: 16px;
        border: 1px solid #ccc;
        border-radius: 5px;
        box-sizing: border-box;
        margin-top: 5px;
    }
    input[type="file"] {
        margin-top: 10px;
    }
    img {
        max-width: 100px;
        margin: 10px 0;
    }
    button {
        margin-top: 20px;
        padding: 12px 20px;
        background: #4CAF50;
        border: none;
        color: white;
        font-size: 16px;
        border-radius: 6px;
        cursor: pointer;
    }
    button:hover {
        background: #45a049;
    }
    .message {
        text-align: center;
        font-weight: bold;
        margin: 20px auto;
        padding: 10px;
        border-radius: 5px;
        max-width: 700px;
    }
    .message.success {
        background: #e0f5e9;
        color: #2e7d32;
    }
    .message.error {
        background: #fdecea;
        color: #c62828;
    }
    .message.warning {
        background: #fff8e1;
        color: #f57c00;
    }
</style>
</head>
<body>
<form method="POST" enctype="multipart/form-data" novalidate>
    <h2>Basic Information</h2>

    <label>Organization Name:</label>
    <input type="text" name="name" value="<?= htmlspecialchars($organization['name'] ?? '') ?>" required>

    <label>Logo / Brand Image:</label>
    <?php if (!empty($organization['logo'])): ?>
        <img src="<?= htmlspecialchars($organization['logo']) ?>" alt="Logo" />
    <?php endif; ?>
    <input type="file" name="logo" accept="image/*" />

    <label>Tagline or Short Description:</label>
    <input type="text" name="tagline" value="<?= htmlspecialchars($organization['tagline'] ?? '') ?>">

    <label>Website URL:</label>
    <input type="url" name="website" value="<?= htmlspecialchars($organization['website'] ?? '') ?>">

    <label>Industry / Sector:</label>
    <input type="text" name="industry" value="<?= htmlspecialchars($organization['industry'] ?? '') ?>">

    <label>Headquarters Location (City, Country):</label>
    <input type="text" name="location" value="<?= htmlspecialchars($organization['location'] ?? '') ?>">

    <h2>Contact Details</h2>

    <label>Email Address:</label>
    <input type="email" name="email" value="<?= htmlspecialchars($organization['email'] ?? '') ?>">

    <label>Phone Number:</label>
    <input type="text" name="phone" value="<?= htmlspecialchars($organization['phone'] ?? '') ?>">

    <label>Physical Address:</label>
    <input type="text" name="address" value="<?= htmlspecialchars($organization['address'] ?? '') ?>">

    <button type="submit">Save Profile</button>
</form>
</body>
</html>