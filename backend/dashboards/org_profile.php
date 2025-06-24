<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once '../db.php';

// Check if org is logged in
if (!isset($_SESSION['org_id'])) {
    header("Location: ../login.php");
    exit();
}

$org_id = $_SESSION['org_id'];

// Fetch organisation details
$stmt = $conn->prepare("SELECT id, name, email FROM organisation WHERE id = ?");
$stmt->bind_param("i", $org_id);
$stmt->execute();
$result = $stmt->get_result();
$org = $result->fetch_assoc();
$stmt->close();

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];

    $updateStmt = $conn->prepare("UPDATE organisation SET name = ?, email = ? WHERE id = ?");
    $updateStmt->bind_param("ssi", $name, $email, $org_id);
    if ($updateStmt->execute()) {
        header("Location: profile.php?updated=1");
        exit();
    } else {
        $error = "Failed to update profile.";
    }
    $updateStmt->close();
}

// Handle delete
if (isset($_POST['delete'])) {
    $deleteStmt = $conn->prepare("DELETE FROM organisation WHERE id = ?");
    $deleteStmt->bind_param("i", $org_id);
    if ($deleteStmt->execute()) {
        session_destroy();
        header("Location: ../login.php?deleted=1");
        exit();
    } else {
        $error = "Failed to delete profile.";
    }
    $deleteStmt->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Organisation Profile</title>
    <style>
        body {
            font-family: Arial;
            background: #f4f4f4;
            padding: 20px;
        }

        h2 {
            color: #333;
        }

        form {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            width: 400px;
        }

        input[type="text"], input[type="email"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0 20px 0;
            border: 1px solid #ccc;
            border-radius: 6px;
        }

        .actions {
            display: flex;
            justify-content: space-between;
        }

        button {
            padding: 10px 15px;
            border: none;
            color: #fff;
            border-radius: 6px;
            cursor: pointer;
        }

        .update {
            background-color: #28a745;
        }

        .delete {
            background-color: #dc3545;
        }

        .message {
            margin: 10px 0;
            color: green;
        }

        .error {
            color: red;
        }
    </style>
</head>
<body>

<h2>Your Organisation Profile</h2>

<?php if (isset($_GET['updated'])): ?>
    <p class="message">Profile updated successfully.</p>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <p class="error"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<form method="POST">
    <label>Name:</label>
    <input type="text" name="name" value="<?= htmlspecialchars($org['name']) ?>" required>

    <label>Email:</label>
    <input type="email" name="email" value="<?= htmlspecialchars($org['email']) ?>" required>

    <div class="actions">
        <button class="update" type="submit" name="update">Update Profile</button>
        <button class="delete" type="submit" name="delete" onclick="return confirm('Are you sure you want to delete your account?')">Delete Profile</button>
    </div>
</form>

</body>
</html>
