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
$sql = "
    SELECT id, name, email, cv, passport, headline, skills, experience, education
    FROM users
    WHERE name LIKE ? 
    AND name IS NOT NULL 
    AND headline IS NOT NULL 
    AND skills IS NOT NULL 
    AND experience IS NOT NULL 
    AND education IS NOT NULL 
    AND cv IS NOT NULL 
    AND passport IS NOT NULL
";
$stmt = $conn->prepare($sql);
$like = "%$search%";
$stmt->bind_param("s", $like);
$stmt->execute();
$result = $stmt->get_result();

$baseUrl = '/jobseekers/ICS-PROJECT/';
function getFullFilePath($relativePath) {
    return __DIR__ . '/../../' . $relativePath;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Organisation Dashboard</title>
    <style>
       body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: #f0f0f0; /* Light Gray */
    margin: 0;
    padding: 0;
    color: #333333; /* Dark Gray text */
}

.header {
    background: #003366; /* Navy Blue primary */
    color: white;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0;
    height: 60px; /* fixed height for navbar */
}

.header .logo {
    display: flex;
    align-items: center;
    height: 100%;
    width: 200px; /* adjust as needed */
    background: white; /* or transparent if logo has background */
    padding: 5px 15px;
    box-sizing: border-box;
}

.header .logo img {
    height: 100%;
    width: auto;
    object-fit: contain;
}

.header nav {
    display: flex;
    align-items: center;
    gap: 20px;
    padding-right: 30px;
}

.header nav a {
    color: white;
    text-decoration: none;
    font-weight: 600;
    transition: color 0.3s ease;
}

.header nav a:hover {
    color: #ff6600; /* Accent orange on hover */
}

.search-bar {
    text-align: center;
    padding: 30px 0 10px;
}

.search-bar input[type="text"] {
    padding: 10px;
    width: 300px;
    border-radius: 4px;
    border: 1px solid #ccc;
    font-size: 16px;
}

.search-bar button {
    padding: 10px 16px;
    background: #ff6600; /* Accent orange */
    color: white;
    border: none;
    border-radius: 4px;
    margin-left: 8px;
    cursor: pointer;
    font-weight: bold;
    transition: background 0.3s ease;
}

.search-bar button:hover {
    background: #d45400; /* Darker orange */
}

h3 {
    text-align: center;
    color: #333333;
}

.grid {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 25px;
    padding: 20px;
}

.profile-card {
    background: white;
    border-radius: 10px;
    width: 320px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    overflow: hidden;
    transition: transform 0.2s ease;
}

.profile-card:hover {
    transform: translateY(-5px);
}

.profile-card img {
    width: 100%;
    height: 200px;
    object-fit: cover;
    border-bottom: 1px solid #eee;
}

.profile-info {
    padding: 15px;
}

.profile-info h2 {
    font-size: 18px;
    color: #003366; /* Navy Blue */
    margin: 0 0 5px;
}

.profile-info .name {
    font-weight: bold;
    color: #333333;
}

.profile-info .skills {
    font-size: 14px;
    color: #666666;
    margin-top: 8px;
}

.profile-actions {
    padding: 0 15px 15px;
    display: flex;
    justify-content: space-between;
    gap: 10px;
}

.profile-actions form,
.profile-actions a {
    flex: 1;
}

.profile-actions button,
.profile-actions a {
    display: block;
    padding: 10px;
    background: #ff6600; /* Accent orange */
    color: white;
    text-align: center;
    border: none;
    border-radius: 5px;
    text-decoration: none;
    font-weight: 600;
    transition: background 0.3s;
}

.profile-actions button:hover,
.profile-actions a:hover {
    background: #d45400; /* Darker orange */
}

.passport-placeholder {
    background: #ddd;
    height: 200px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #777;
}
</style>
</head>
<body>

<div class="header">
    <div class="logo"><img src="../../assets/logo.png" alt="Logo"></div>
    <nav>
        <a href="interests.php">My Interests</a>
        <a href="interests_sent.php">🤝 Interests Sent</a>
        <a href="org_notifications.php">📅 Interview Requests</a>
        <a href="edit_organisation_profile.php">🏢 Organisation Profile</a>
        <a href="../logout.php">Logout</a>
    </nav>
</div>

<form method="GET" class="search-bar">
    <input type="text" name="search" placeholder="Search Job Seekers..." value="<?= htmlspecialchars($search) ?>">
    <button type="submit">Search</button>
</form>

<h3>Qualified Job Seekers (100% Profile Completion)</h3>

<div class="grid">
<?php while($row = $result->fetch_assoc()): ?>
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
            <p class="name"><?= htmlspecialchars($row['name']) ?></p>
            <p class="skills"><strong>Skills:</strong> <?= htmlspecialchars($row['skills']) ?></p>
        </div>
        <div class="profile-actions">
                <a href="show_interest.php?jobseeker_id=<?= (int)$row['id'] ?>">🤝 Show Interest</a>
            <a href="view_profile.php?id=<?= (int)$row['id'] ?>" target="_blank">🔍 View Full Profile</a>
        </div>
    </div>
<?php endwhile; ?>
</div>

</body>
</html>
