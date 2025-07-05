<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once '../db.php';

// Access control
if (empty($_SESSION['org_id']) || ($_SESSION['role'] ?? '') !== 'organisation') {
    die("Access denied. Please login as an organization user.");
}

$user_id = $_SESSION['org_id'];
$org_id = $_SESSION['organization_id'] ?? null;

function loadOrganization($conn, $org_id) {
    if (!$org_id) return null;
    $stmt = $conn->prepare("SELECT * FROM organizations WHERE id = ?");
    $stmt->bind_param("i", $org_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

$organization = loadOrganization($conn, $org_id);

if (!$organization && $org_id) {
    unset($_SESSION['organization_id']);
    $org_id = null;
    $organization = null;
}

$message = '';
$message_class = '';

function clean_input($data) {
    return htmlspecialchars(trim($data));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = clean_input($_POST['name'] ?? '');
    $tagline  = clean_input($_POST['tagline'] ?? '');
    $website  = filter_var(trim($_POST['website'] ?? ''), FILTER_SANITIZE_URL);
    $industry = clean_input($_POST['industry'] ?? '');
    $location = clean_input($_POST['location'] ?? '');
    $email    = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $phone    = clean_input($_POST['phone'] ?? '');
    $address  = clean_input($_POST['address'] ?? '');

    $errors = [];
    if (!$name) $errors[] = "Organization name is required.";
    if ($email === false) $errors[] = "Please enter a valid email address.";
    if ($website && filter_var($website, FILTER_VALIDATE_URL) === false) $errors[] = "Please enter a valid website URL.";

    $upload_dir = __DIR__ . '/uploads/logos/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    $target_file = $organization['logo'] ?? '';

    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $filename = basename($_FILES['logo']['name']);
        $safe_filename = time() . "_" . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $filename);
        $full_path = $upload_dir . $safe_filename;

        if (move_uploaded_file($_FILES['logo']['tmp_name'], $full_path)) {
            if (!empty($organization['logo']) && file_exists(__DIR__ . '/' . $organization['logo']) && $organization['logo'] !== 'uploads/logos/' . $safe_filename) {
                unlink(__DIR__ . '/' . $organization['logo']);
            }
            $target_file = 'uploads/logos/' . $safe_filename;
        } else {
            $errors[] = "Failed to upload new logo. Keeping old logo.";
        }
    }

    if (empty($errors)) {
        if ($org_id) {
            $sql = "UPDATE organizations SET name=?, tagline=?, website=?, industry=?, location=?, email=?, phone=?, address=?, logo=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssssssi", $name, $tagline, $website, $industry, $location, $email, $phone, $address, $target_file, $org_id);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                $message = "Organization profile updated successfully!";
                $message_class = "success";
            } else {
                $message = "No changes made or update failed.";
                $message_class = "warning";
            }
        } else {
            $sql = "INSERT INTO organizations (name, tagline, website, industry, location, email, phone, address, logo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssssss", $name, $tagline, $website, $industry, $location, $email, $phone, $address, $target_file);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                $new_org_id = $stmt->insert_id;

                $update_user_sql = "UPDATE organisation SET organization_id = ? WHERE id = ?";
                $update_stmt = $conn->prepare($update_user_sql);
                $update_stmt->bind_param("ii", $new_org_id, $user_id);
                $update_stmt->execute();

                $_SESSION['organization_id'] = $new_org_id;
                $org_id = $new_org_id;

                $message = "Organization profile created successfully!";
                $message_class = "success";
            } else {
                $message = "Failed to create organization profile.";
                $message_class = "error";
            }
        }

        $organization = loadOrganization($conn, $org_id);
    } else {
        $message = implode('<br>', $errors);
        $message_class = "error";
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
        color: #003366;
        border-bottom: 2px solid #003366;
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
        display: block;
    }
    button {
        margin-top: 20px;
        padding: 12px 20px;
        background: #003366;
        border: none;
        color: white;
        font-size: 16px;
        border-radius: 6px;
        cursor: pointer;
    }
    button:hover {
        background: #00264d;
    }
    .message {
        text-align: center;
        font-weight: bold;
        margin: 20px auto;
        padding: 10px;
        border-radius: 5px;
        max-width: 700px;
        word-wrap: break-word;
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

<?php include 'org_sidebar.php'; ?>

<?php if ($message): ?>
    <p class="message <?= htmlspecialchars($message_class) ?>"><?= $message ?></p>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data" novalidate>
    <h2>Basic Information</h2>

    <label for="name">Organization Name:</label>
    <input type="text" id="name" name="name" required value="<?= htmlspecialchars($organization['name'] ?? '') ?>">

    <label for="logo">Logo / Brand Image:</label>
    <?php if (!empty($organization['logo'])): ?>
        <img src="<?= htmlspecialchars($organization['logo']) ?>" alt="Logo" />
    <?php endif; ?>
    <input type="file" id="logo" name="logo" accept="image/*" />

    <label for="tagline">Tagline or Short Description:</label>
    <input type="text" id="tagline" name="tagline" value="<?= htmlspecialchars($organization['tagline'] ?? '') ?>">

    <label for="website">Website URL:</label>
    <input type="url" id="website" name="website" value="<?= htmlspecialchars($organization['website'] ?? '') ?>">

    <label for="industry">Industry / Sector:</label>
    <input type="text" id="industry" name="industry" value="<?= htmlspecialchars($organization['industry'] ?? '') ?>">

    <label for="location">Headquarters Location (City, Country):</label>
    <input type="text" id="location" name="location" value="<?= htmlspecialchars($organization['location'] ?? '') ?>">

    <h2>Contact Details</h2>

    <label for="email">Email Address:</label>
    <input type="email" id="email" name="email" value="<?= htmlspecialchars($organization['email'] ?? '') ?>">

    <label for="phone">Phone Number:</label>
    <input type="text" id="phone" name="phone" value="<?= htmlspecialchars($organization['phone'] ?? '') ?>">

    <label for="address">Physical Address:</label>
    <input type="text" id="address" name="address" value="<?= htmlspecialchars($organization['address'] ?? '') ?>">

    <button type="submit">Save Profile</button>
</form>

</body>
</html>
