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

$userId = $_SESSION['user_id'];
$errors = [];

$allowedCvTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
$allowedPassportTypes = ['image/jpeg', 'image/png', 'image/gif'];

$maxCvSize = 5 * 1024 * 1024; // 5 MB
$maxPassportSize = 2 * 1024 * 1024; // 2 MB

$stmt = $conn->prepare("SELECT name, headline, skills, experience, education, cv, passport, documents, industry, experience_years, experience_level FROM users WHERE id = ?");
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

    if (empty($name)) $errors[] = "Name is required.";
    if (empty($headline)) $errors[] = "Headline is required.";
    if ($experience_years < 0) $errors[] = "Experience years cannot be negative.";
    if (!in_array($experience_level, ['junior', 'mid', 'senior', 'expert'])) $errors[] = "Please select a valid experience level.";

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
        if (isset($_FILES['cv']) && $_FILES['cv']['error'] === UPLOAD_ERR_OK) {
            $cvFilename = uniqid('cv_') . '_' . basename($_FILES['cv']['name']);
            $cvPath = '../uploads/' . $cvFilename;
            if (move_uploaded_file($_FILES['cv']['tmp_name'], $cvPath)) {
                $cv = 'uploads/' . $cvFilename;
            } else {
                $errors[] = "Failed to save CV file.";
            }
        }

        if (isset($_FILES['passport']) && $_FILES['passport']['error'] === UPLOAD_ERR_OK) {
            $passportFilename = uniqid('passport_') . '_' . basename($_FILES['passport']['name']);
            $passportPath = '../uploads/' . $passportFilename;
            if (move_uploaded_file($_FILES['passport']['tmp_name'], $passportPath)) {
                $passport = 'uploads/' . $passportFilename;
            } else {
                $errors[] = "Failed to save passport photo.";
            }
        }
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("UPDATE users SET name = ?, headline = ?, skills = ?, experience = ?, education = ?, industry = ?, experience_years = ?, experience_level = ?, cv = ?, passport = ? WHERE id = ?");
        $stmt->bind_param("ssssssisisi", $name, $headline, $skills, $experience, $education, $industry, $experience_years, $experience_level, $cv, $passport, $userId);

        if ($stmt->execute()) {
            header("Location: users_dashboard.php");
            exit();
        } else {
            $errors[] = "Error saving profile: " . $stmt->error;
        }
        $stmt->close();
    }
}

$industries = [ 'Accounting & Finance','Administrative & Office Support','Advertising, Arts & Media','Banking','Construction','Consulting','Customer Service','Education & Training','Engineering','Healthcare & Medical','Hospitality & Tourism','Human Resources','Information Technology','Insurance','Legal','Manufacturing','Marketing','Non-Profit & Volunteer','Pharmaceutical','Real Estate','Retail','Sales','Science & Technology','Transportation & Logistics','Others' ];

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
    * {
      box-sizing: border-box;
    }

    body {
      margin: 0;
      font-family: 'Segoe UI', sans-serif;
      background: #f0f2f5;
      color: #333;
      display: flex;
    }

    /* Sidebar */
    .sidebar {
      width: 220px;
      background: #003366;
      height: 100vh;
      position: fixed;
      top: 0;
      left: 0;
      padding: 20px 15px;
      display: flex;
      flex-direction: column;
    }

    .sidebar h2 {
      color: #fff;
      margin-bottom: 30px;
      font-size: 18px;
    }

    .sidebar a {
      color: #fff;
      text-decoration: none;
      margin-bottom: 15px;
      padding: 10px 12px;
      border-radius: 6px;
      transition: background 0.2s ease;
      font-size: 14px;
    }

    .sidebar a:hover {
      background: #004080;
    }

    /* Main content */
    .main {
      margin-left: 220px;
      padding: 40px 30px;
      width: 100%;
      max-width: 900px;
    }

    .form-container {
      background: #fff;
      border-radius: 10px;
      padding: 30px;
    }

    h1 {
      font-size: 22px;
      margin-bottom: 25px;
      color: #003366;
    }

    label {
      display: block;
      margin-top: 15px;
      font-weight: 600;
      color: #333;
    }

    input[type="text"],
    input[type="number"],
    input[type="file"],
    select,
    textarea {
      width: 100%;
      margin-top: 8px;
      padding: 10px 12px;
      border: 1px solid #ccc;
      border-radius: 6px;
      background: #fafafa;
      font-size: 14px;
    }

    textarea {
      resize: vertical;
    }

    .radio-group {
      margin-top: 10px;
    }

    .radio-group label {
      margin-right: 15px;
      font-weight: normal;
    }

    button {
      margin-top: 25px;
      padding: 12px 20px;
      background-color: #0047AB;
      color: white;
      border: none;
      border-radius: 6px;
      font-weight: bold;
      font-size: 14px;
      cursor: pointer;
      transition: background-color 0.2s ease;
    }

    button:hover {
      background-color: #003366;
    }

    .errors {
      margin-bottom: 20px;
    }

    .errors li {
      color: red;
      font-size: 13px;
    }

    .back-link {
      display: inline-block;
      margin-bottom: 20px;
      color: #0047AB;
      text-decoration: none;
      font-weight: 600;
    }

    .back-link:hover {
      text-decoration: underline;
    }

    @media (max-width: 768px) {
      .sidebar {
        display: none;
      }

      .main {
        margin-left: 0;
        padding: 20px;
      }
    }
  </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
  <h2>Job Portal</h2>
  <a href="users_dashboard.php">🏠 Dashboard</a>
  <a href="edit_profile.php">✏️ Edit Profile</a>
  <a href="job_offers.php">📩 Job Offers</a>
  <a href="interviews.php">📅 Interviews</a>
  <a href="messages.php">💬 Messages</a>
  <a href="notifications.php">🔔 Notifications</a>
  <a href="settings.php">⚙️ Settings</a>
  <a href="../logout.php">🚪 Logout</a>
</div>

<!-- Main Content -->
<div class="main">
  <div class="form-container">
    <a href="users_dashboard.php" class="back-link">&larr; Back to Dashboard</a>
    <h1>Edit Your Profile</h1>

    <?php if ($errors): ?>
      <ul class="errors">
        <?php foreach ($errors as $err): ?>
          <li><?= htmlspecialchars($err) ?></li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>

    <form method="POST" action="" enctype="multipart/form-data">
      <label>Name</label>
      <input type="text" name="name" value="<?= htmlspecialchars($name ?? '') ?>" required>

      <label>Professional Headline</label>
      <input type="text" name="headline" value="<?= htmlspecialchars($headline ?? '') ?>" placeholder="e.g., Senior PHP Developer" required>

      <label>Skills (comma-separated)</label>
      <input type="text" name="skills" value="<?= htmlspecialchars($skills ?? '') ?>">

      <label>Experience</label>
      <textarea name="experience"><?= htmlspecialchars($experience ?? '') ?></textarea>

      <label>Education</label>
      <textarea name="education"><?= htmlspecialchars($education ?? '') ?></textarea>

      <label>Industry</label>
      <select name="industry" required>
        <option value="">-- Select Industry --</option>
        <?php foreach ($industries as $ind): ?>
          <option value="<?= htmlspecialchars($ind) ?>" <?= $ind === $industry ? 'selected' : '' ?>>
            <?= htmlspecialchars($ind) ?>
          </option>
        <?php endforeach; ?>
      </select>

      <label>Experience (years)</label>
      <input type="number" name="experience_years" value="<?= htmlspecialchars($experience_years ?? 0) ?>" min="0" required>

      <label>Experience Level</label>
      <div class="radio-group">
        <label><input type="radio" name="experience_level" value="junior" <?= isChecked($experience_level, 'junior') ?>> Junior</label>
        <label><input type="radio" name="experience_level" value="mid" <?= isChecked($experience_level, 'mid') ?>> Mid-level</label>
        <label><input type="radio" name="experience_level" value="senior" <?= isChecked($experience_level, 'senior') ?>> Senior</label>
        <label><input type="radio" name="experience_level" value="expert" <?= isChecked($experience_level, 'expert') ?>> Expert</label>
      </div>

      <label>Upload CV</label>
      <input type="file" name="cv" accept=".pdf,.doc,.docx">

      <label>Upload Passport Photo</label>
      <input type="file" name="passport" accept=".jpg,.jpeg,.png,.gif">

      <button type="submit">Save Profile</button>
    </form>
  </div>
</div>

</body>
</html>