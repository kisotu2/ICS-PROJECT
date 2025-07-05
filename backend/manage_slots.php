<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once '../db.php';

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

// Fetch available interview slots
$stmt = $conn->prepare("SELECT * FROM interview_slots WHERE organisation_id = ? ORDER BY interview_date ASC");
$stmt->bind_param("i", $org_id);
$stmt->execute();
$result = $stmt->get_result();
$slots = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Form submission handling
$message = '';
if (isset($_POST['assign'])) {
    $slot_id = (int)$_POST['slot_id'];
    $interest_id = (int)$_POST['interest_id'];
    $jobseeker_id = (int)$_POST['jobseeker_id'];

    // Mark slot as booked
    $conn->query("UPDATE interview_slots SET is_booked = 1 WHERE slot_id = $slot_id");

    // Insert interview
    $stmt = $conn->prepare("INSERT INTO interviews (interest_id, organisation_id, jobseeker_id, status, date, created_at) VALUES (?, ?, ?, 'scheduled', (SELECT interview_date FROM interview_slots WHERE slot_id = ?), NOW())");
    $stmt->bind_param("iiii", $interest_id, $org_id, $jobseeker_id, $slot_id);
    $stmt->execute();

    $message = "Interview slot assigned successfully!";
}

$jobseeker_id = $_GET['jobseeker_id'] ?? null;
$interest_id = $_GET['interest_id'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Interview Slots</title>
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f4f6fa;
            color: #2c3e50;
            display: flex;
        }
        .main-content {
            margin-left: 240px;
            padding: 40px;
            flex-grow: 1;
        }
        h2 {
            color: #003366;
        }
        .message {
            padding: 10px;
            background-color: #d4edda;
            color: #155724;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        form {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            max-width: 500px;
        }
        select, button {
            padding: 10px;
            width: 100%;
            margin-top: 10px;
            font-size: 16px;
            border-radius: 6px;
        }
        button {
            background: #0047AB;
            color: white;
            border: none;
        }
        button:hover {
            background: #002f6c;
        }
    </style>
</head>
<body>
<?php include 'org_sidebar.php'; ?>
<div class="main-content">
    <h2>Manage Interview Slots</h2>

    <?php if (!empty($message)): ?>
        <div class="message"> <?= htmlspecialchars($message) ?> </div>
    <?php endif; ?>

    <?php if ($jobseeker_id && $interest_id): ?>
        <h3>Assign Slot to Job Seeker</h3>
        <form method="POST">
            <input type="hidden" name="jobseeker_id" value="<?= (int)$jobseeker_id ?>">
            <input type="hidden" name="interest_id" value="<?= (int)$interest_id ?>">
            <label>Select an available slot:</label>
            <select name="slot_id" required>
                <?php foreach ($slots as $slot): ?>
                    <?php if (!$slot['is_booked']): ?>
                        <option value="<?= $slot['slot_id'] ?>">
                            <?= date('F j, Y \a\t H:i', strtotime($slot['interview_date'])) ?>
                        </option>
                    <?php endif; ?>
                <?php endforeach; ?>
            </select>
            <button type="submit" name="assign">Assign Slot</button>
        </form>
    <?php else: ?>
        <p>Please select a job seeker from the interview scheduling page.</p>
    <?php endif; ?>
</div>
</body>
</html>
