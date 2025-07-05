<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

$org_id = $_SESSION['org_id'] ?? 1;
$conn = new mysqli('localhost', 'root', '', 'jobseekers');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$search = $_GET['search'] ?? '';
$experienceLevel = $_GET['experience_level'] ?? '';

$sql = "
    SELECT id, name, email, cv, passport, headline, skills, experience, education, industry
    FROM users
    WHERE (
        skills LIKE ? OR
        headline LIKE ? OR
        experience LIKE ?
    )
    AND headline IS NOT NULL 
    AND skills IS NOT NULL 
    AND experience IS NOT NULL 
    AND education IS NOT NULL 
    AND cv IS NOT NULL 
    AND passport IS NOT NULL
";

if (!empty($experienceLevel)) {
    $sql .= " AND experience_level = ?";
}

$stmt = $conn->prepare($sql);
$like = "%$search%";

if (!empty($experienceLevel)) {
    $stmt->bind_param("ssss", $like, $like, $like, $experienceLevel);
} else {
    $stmt->bind_param("sss", $like, $like, $like);
}

$stmt->execute();
$result = $stmt->get_result();

$profilesByIndustry = [];
while ($row = $result->fetch_assoc()) {
    $industry = $row['industry'] ?? 'Unknown Industry';
    $profilesByIndustry[$industry][] = $row;
}
ksort($profilesByIndustry);

$baseUrl = '/jobseekers/ICS-PROJECT/';
function getFullFilePath($relativePath) {
    return __DIR__ . '/../../' . $relativePath;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Organisation Dashboard</title>
  <link rel="stylesheet" href="org_sidebar.css">
  <style>
    body {
      margin: 0;
      font-family: 'Segoe UI', sans-serif;
      background: #f4f6f9;
    }

    .main-content {
      margin-left: 240px;
      padding: 30px;
    }

    h1, h3 {
      color: #003366;
    }

    .search-bar {
      display: flex;
      gap: 10px;
      margin-bottom: 30px;
    }

    .search-bar input, .search-bar select {
      padding: 10px;
      font-size: 14px;
      border: 1px solid #ccc;
      border-radius: 6px;
    }

    .search-bar button {
      padding: 10px 18px;
      background: #0047AB;
      color: white;
      border: none;
      border-radius: 6px;
      font-weight: bold;
      cursor: pointer;
      transition: background 0.3s ease;
    }

    .search-bar button:hover {
      background: #003366;
    }

    .grid {
      display: flex;
      flex-wrap: wrap;
      gap: 25px;
      margin-top: 10px;
    }

    .profile-card {
      background: white;
      border-radius: 10px;
      width: 320px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.05);
      overflow: hidden;
    }

    .profile-card img {
      width: 100%;
      height: 200px;
      object-fit: cover;
    }

    .passport-placeholder {
      background: #ddd;
      height: 200px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #777;
    }

    .profile-info {
      padding: 15px;
    }

    .profile-info h2 {
      margin: 0 0 10px;
      font-size: 18px;
      color: #003366;
    }

    .profile-info p {
      margin: 4px 0;
      font-size: 14px;
    }

    .profile-actions {
      display: flex;
      gap: 10px;
      padding: 15px;
    }

    .profile-actions a {
      flex: 1;
      background: #0047AB;
      color: white;
      text-align: center;
      padding: 10px;
      border-radius: 6px;
      text-decoration: none;
      font-weight: 600;
      font-size: 14px;
    }

    .profile-actions a:hover {
      background: #003366;
    }

    .industry-title {
      margin-top: 40px;
      border-bottom: 1px solid #ccc;
      padding-bottom: 5px;
      font-size: 20px;
    }
  </style>
</head>
<body>

<?php include 'org_sidebar.php'; ?>

<div class="main-content">
  <h1>Organisation Dashboard</h1>

  <form method="GET" class="search-bar">
    <input type="text" name="search" placeholder="Search by skills, headline, or experience" value="<?= htmlspecialchars($search) ?>">
    <select name="experience_level">
      <option value="">-- Any Experience Level --</option>
      <option value="junior" <?= $experienceLevel === 'junior' ? 'selected' : '' ?>>Junior</option>
      <option value="mid" <?= $experienceLevel === 'mid' ? 'selected' : '' ?>>Mid</option>
      <option value="senior" <?= $experienceLevel === 'senior' ? 'selected' : '' ?>>Senior</option>
      <option value="expert" <?= $experienceLevel === 'expert' ? 'selected' : '' ?>>Expert</option>
    </select>
    <button type="submit">Search</button>
  </form>

  <?php foreach ($profilesByIndustry as $industry => $profiles): ?>
    <h3 class="industry-title"><?= htmlspecialchars($industry) ?></h3>
    <div class="grid">
      <?php foreach ($profiles as $row): ?>
        <div class="profile-card">
          <?php
            $passportPath = $row['passport'];
            $passportFullPath = getFullFilePath($passportPath);
            if (!empty($passportPath) && file_exists($passportFullPath)) {
              echo '<img src="' . htmlspecialchars($baseUrl . $passportPath) . '" alt="Passport Photo">';
            } else {
              echo '<div class="passport-placeholder">No Photo Available</div>';
            }
          ?>
          <div class="profile-info">
            <h2><?= htmlspecialchars($row['headline']) ?></h2>
            <p><strong><?= htmlspecialchars($row['name']) ?></strong></p>
            <p><strong>Skills:</strong> <?= htmlspecialchars($row['skills']) ?></p>
          </div>
          <div class="profile-actions">
            <a href="show_interest.php?jobseeker_id=<?= (int)$row['id'] ?>">🤝 Interest</a>
            <a href="view_profile.php?id=<?= (int)$row['id'] ?>" target="_blank">🔍 View</a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endforeach; ?>
</div>

</body>
</html>
