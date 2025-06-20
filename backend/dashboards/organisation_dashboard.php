<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
$org_id = $_SESSION['org_id'] ?? 1; // Replace with real session
$conn_seekers = new mysqli('localhost', 'root', '', 'jobseekers');
$conn_org = new mysqli('localhost', 'root', '', 'jobseekers');

// Handle interest submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['jobseeker_id'])) {
    $jobseeker_id = $_POST['jobseeker_id'];
    $stmt = $conn_org->prepare("INSERT INTO interests (organisation_id, jobseeker_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $org_id, $jobseeker_id);
    $stmt->execute();
    $stmt->close();
}

// Search logic
$search = $_GET['search'] ?? '';
$sql = "SELECT * FROM users WHERE name LIKE ?";
$stmt = $conn_seekers->prepare($sql);
$like = "%$search%";
$stmt->bind_param("s", $like);
$stmt->execute();
$result = $stmt->get_result();
?>

<h1>Welcome Organisation!</h1>
<a href="../login.php">Logout</a>

<form method="GET">
    <input type="text" name="search" placeholder="Search Job Seekers..." value="<?= htmlspecialchars($search) ?>">
    <button type="submit">Search</button>
</form>

<h3>Available Job Seekers:</h3>
<?php while($row = $result->fetch_assoc()): ?>
    <div style="border: 1px solid #ccc; padding: 10px; margin:10px;">
        <strong>Name:</strong> <?= htmlspecialchars($row['name']) ?><br>
        <strong>Email:</strong> <?= htmlspecialchars($row['email']) ?><br>
        <a href="<?= htmlspecialchars($row['cv']) ?>" target="_blank">View CV</a><br>

        <form method="POST" style="margin-top:10px;">
            <input type="hidden" name="jobseeker_id" value="<?= $row['id'] ?>">
            <button type="submit">Show Interest</button>
        </form>
    </div>
<?php endwhile; ?>
