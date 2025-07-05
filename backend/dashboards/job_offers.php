<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'users') {
    header("Location: ../login.php");
    exit();
}

$userId = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT i.id AS interest_id, i.status, i.job_title, i.job_description, i.created_at, o.name AS org_name FROM interests i JOIN organizations o ON i.organisation_id = o.id WHERE i.jobseeker_id = ? ORDER BY i.created_at DESC");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$interests = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Job Offers</title>
  <style>
    * { box-sizing: border-box; }
    body {
      margin: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: #f5f6fa;
      color: #2c3e50;
      display: flex;
    }
    .content {
      margin-left: 220px;
      padding: 40px 30px;
      flex-grow: 1;
      overflow-y: auto;
      max-width: 1000px;
    }
    .section {
      background: #ffffff;
      border-radius: 12px;
      padding: 20px;
      margin-bottom: 20px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }
    .job-offer {
      border-left: 5px solid #0047AB;
      background: #ffffff;
      padding: 15px;
      border-radius: 8px;
      margin-top: 15px;
    }
    .job-offer button {
      background-color: #0b1d3a;
      color: white;
      padding: 8px 14px;
      margin-right: 10px;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-weight: bold;
      transition: 0.3s;
    }
    .job-offer button:hover {
      background-color: #13294b;
    }
    @media (max-width: 768px) {
      .content {
        margin-left: 0;
        padding: 20px;
      }
    }
  </style>
</head>
<body>
<?php include 'sidebar.php'; ?>
<main class="content">
  <h2>Job Offers</h2>

  <?php if (!empty($interests)): ?>
    <?php foreach ($interests as $interest): ?>
      <div class="job-offer">
        <p><strong><?= htmlspecialchars($interest['org_name']) ?></strong> has shown interest in you for the role:</p>
        <h4><?= htmlspecialchars($interest['job_title']) ?></h4>
        <p><?= nl2br(htmlspecialchars($interest['job_description'])) ?></p>
        <p>Status: <?= htmlspecialchars($interest['status']) ?></p>
        <?php if ($interest['status'] === 'pending'): ?>
          <form method="POST" action="respond_interest.php">
            <input type="hidden" name="interest_id" value="<?= $interest['interest_id'] ?>">
            <button type="submit" name="response" value="accepted">Accept</button>
            <button type="submit" name="response" value="rejected">Reject</button>
          </form>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  <?php else: ?>
    <p>No job interests at the moment.</p>
  <?php endif; ?>
</main>
</body>
</html>
