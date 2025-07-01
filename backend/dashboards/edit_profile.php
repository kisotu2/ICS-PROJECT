<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once '../db.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$errors = [];

// Allowed file types for uploads
$allowedCvTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
$allowedPassportTypes = ['image/jpeg', 'image/png', 'image/gif'];

// Max file sizes (in bytes)
$maxCvSize = 5 * 1024 * 1024; // 5 MB
$maxPassportSize = 2 * 1024 * 1024; // 2 MB

// Fetch existing profile data
$stmt = $conn->prepare("
    SELECT name, headline, skills, experience, education, cv, passport, documents, industry, experience_years, experience_level
    FROM users WHERE id = ?
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($name, $headline, $skills, $experience, $education, $cv, $passport, $documents, $industry, $experience_years, $experience_level);
$stmt->fetch();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $headline = trim($_POST['headline'] ?? '');
    $skills = trim($_POST['skills'] ?? '');
    $experience = trim($_POST['experience'] ?? '');
    $education = trim($_POST['education'] ?? '');
    $industry = trim($_POST['industry'] ?? '');
    $experience_years = (int)($_POST['experience_years'] ?? 0);
    $experience_level = $_POST['experience_level'] ?? '';

    // Validation
    if (empty($name)) {
        $errors[] = "Name is required.";
    }
    if (empty($headline)) {
        $errors[] = "Headline is required.";
    }
    if ($experience_years < 0) {
        $errors[] = "Experience years cannot be negative.";
    }
    if (!in_array($experience_level, ['junior', 'mid', 'senior', 'expert'])) {
        $errors[] = "Please select a valid experience level.";
    }
    // Optional: validate industry is in allowed list (can be added)

    // Handle CV upload if any
    if (isset($_FILES['cv']) && $_FILES['cv']['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES['cv']['error'] !== UPLOAD_ERR_OK) {
            $errors[] = "Error uploading CV file.";
        } else {
            if (!in_array($_FILES['cv']['type'], $allowedCvTypes)) {
                $errors[] = "Invalid CV file type. Allowed: PDF, DOC, DOCX.";
            }
            if ($_FILES['cv']['size'] > $maxCvSize) {
                $errors[] = "CV file size exceeds 5MB limit.";
            }
        }
    }

    // Handle Passport upload if any
    if (isset($_FILES['passport']) && $_FILES['passport']['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES['passport']['error'] !== UPLOAD_ERR_OK) {
            $errors[] = "Error uploading passport photo.";
        } else {
            if (!in_array($_FILES['passport']['type'], $allowedPassportTypes)) {
                $errors[] = "Invalid passport photo type. Allowed: JPG, PNG, GIF.";
            }
            if ($_FILES['passport']['size'] > $maxPassportSize) {
                $errors[] = "Passport photo size exceeds 2MB limit.";
            }
        }
    }

    if (empty($errors)) {
        // Handle file moves
        if (isset($_FILES['cv']) && $_FILES['cv']['error'] === UPLOAD_ERR_OK) {
            $cvFilename = uniqid('cv_') . '_' . basename($_FILES['cv']['name']);
            $cvPath = '../uploads/' . $cvFilename;
            if (!move_uploaded_file($_FILES['cv']['tmp_name'], $cvPath)) {
                $errors[] = "Failed to save CV file.";
            } else {
                $cv = 'uploads/' . $cvFilename;
            }
        }

        if (isset($_FILES['passport']) && $_FILES['passport']['error'] === UPLOAD_ERR_OK) {
            $passportFilename = uniqid('passport_') . '_' . basename($_FILES['passport']['name']);
            $passportPath = '../uploads/' . $passportFilename;
            if (!move_uploaded_file($_FILES['passport']['tmp_name'], $passportPath)) {
                $errors[] = "Failed to save passport photo.";
            } else {
                $passport = 'uploads/' . $passportFilename;
            }
        }
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("
            UPDATE users
            SET name = ?, headline = ?, skills = ?, experience = ?, education = ?, industry = ?, experience_years = ?, experience_level = ?, cv = ?, passport = ?
            WHERE id = ?
        ");
        $stmt->bind_param("ssssssisisi",
            $name,
            $headline,
            $skills,
            $experience,
            $education,
            $industry,
            $experience_years,
            $experience_level,
            $cv,
            $passport,
            $userId
        );

        if ($stmt->execute()) {
            header("Location: users_dashboard.php");
            exit();
        } else {
            $errors[] = "Error saving profile: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Industry options for dropdown
$industries = [
    'Accounting & Finance',
    'Administrative & Office Support',
    'Advertising, Arts & Media',
    'Banking',
    'Construction',
    'Consulting',
    'Customer Service',
    'Education & Training',
    'Engineering',
    'Healthcare & Medical',
    'Hospitality & Tourism',
    'Human Resources',
    'Information Technology',
    'Insurance',
    'Legal',
    'Manufacturing',
    'Marketing',
    'Non-Profit & Volunteer',
    'Pharmaceutical',
    'Real Estate',
    'Retail',
    'Sales',
    'Science & Technology',
    'Transportation & Logistics',
    'Others'
];

function isChecked($current, $value) {
    return $current === $value ? 'checked' : '';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Profile</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f9f9f9;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        form {
            width: 100%;
            max-width: 650px;
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            overflow-y: auto;
            max-height: 90vh;
        }
        input[type="text"], textarea, select, input[type="number"], input[type="file"] {
            width: 100%;
            margin-top: 8px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }
        textarea {
            resize: vertical;
            min-height: 80px;
        }
        button {
            margin-top: 15px;
            padding: 12px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background: #0056b3;
        }
        .errors li {
            color: red;
            margin-bottom: 5px;
        }
        .nav-back {
            display: block;
            margin-bottom: 20px;
            color: #007bff;
            text-decoration: none;
        }
        .radio-group {
            margin-top: 10px;
        }
        .radio-group label {
            margin-right: 15px;
            font-weight: normal;
        }
        label {
            font-weight: bold;
            margin-top: 15px;
            display: block;
        }
    </style>
</head>
<body>

<form method="POST" action="" enctype="multipart/form-data">
    <a class="nav-back" href="users_dashboard.php">&larr; Back to Dashboard</a>
    <h2>Edit Your Profile</h2>

    <?php if ($errors): ?>
        <ul class="errors">
            <?php foreach ($errors as $err): ?>
                <li><?= htmlspecialchars($err) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <label>Name</label>
    <input type="text" name="name" value="<?= htmlspecialchars($name ?? '') ?>" required>

    <label>Professional Headline</label>
    <input type="text" name="headline" value="<?= htmlspecialchars($headline ?? '') ?>" placeholder="e.g., Senior PHP Developer" required>

    <label>Skills (comma-separated)</label>
    <input type="text" name="skills" value="<?= htmlspecialchars($skills ?? '') ?>" placeholder="e.g., PHP, JavaScript, SQL">

    <label>Experience</label>
    <textarea name="experience" placeholder="Describe your work history"><?= htmlspecialchars($experience ?? '') ?></textarea>

    <label>Education</label>
    <textarea name="education" placeholder="Your educational background"><?= htmlspecialchars($education ?? '') ?></textarea>

    <label>Industry</label>
    <select name="industry" required>
        <option value="">-- Select Industry --</option>
        <?php foreach ($industries as $ind): ?>
            <option value="<?= htmlspecialchars($ind) ?>" <?= $ind === $industry ? 'selected' : '' ?>><?= htmlspecialchars($ind) ?></option>
        <?php endforeach; ?>
    </select>

    <label>Experience (years)</label>
    <input type="number" name="experience_years" value="<?= htmlspecialchars($experience_years ?? '0') ?>" min="0" step="1" required>

    <label>Experience Level</label>
    <div class="radio-group">
        <label><input type="radio" name="experience_level" value="junior" <?= isChecked($experience_level, 'junior') ?>> Junior (0-2 years)</label>
        <label><input type="radio" name="experience_level" value="mid" <?= isChecked($experience_level, 'mid') ?>> Mid-level (3-5 years)</label>
        <label><input type="radio" name="experience_level" value="senior" <?= isChecked($experience_level, 'senior') ?>> Senior (6-10 years)</label>
        <label><input type="radio" name="experience_level" value="expert" <?= isChecked($experience_level, 'expert') ?>> Expert (10+ years)</label>
    </div>

    <label>Upload CV (PDF, DOC, DOCX, max 5MB)</label>
    <input type="file" name="cv" accept=".pdf,.doc,.docx">

    <label>Upload Passport Photo (JPG, PNG, GIF, max 2MB)</label>
    <input type="file" name="passport" accept=".jpg,.jpeg,.png,.gif">

    <button type="submit">Save Profile</button>
</form>

</body>
</html>
