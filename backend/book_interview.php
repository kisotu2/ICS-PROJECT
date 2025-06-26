<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$conn = new mysqli('localhost', 'root', '', 'jobseekers');
$org_id = $_GET['org_id'] ?? 0;

// Fetch available dates
$stmt = $conn->prepare("SELECT * FROM interview_slots WHERE organisation_id = ? AND is_booked = 0");
$stmt->bind_param("i", $org_id);
$stmt->execute();
$slots = $stmt->get_result();
?>

<h2>Book Interview</h2>
<form method="POST">
    <label>Select a Date:</label>
    <select name="slot_id">
        <?php while ($slot = $slots->fetch_assoc()): ?>
            <option value="<?= $slot['id'] ?>"><?= $slot['interview_date'] ?></option>
        <?php endwhile; ?>
    </select>
    <button type="submit" name="book">Book Interview</button>
</form>

<?php
if (isset($_POST['book'])) {
    $slot_id = $_POST['slot_id'];
    $update = $conn->prepare("UPDATE interview_slots SET is_booked = 1 WHERE slot_id = ?");
    $update->bind_param("i", $slot_id);
    if ($update->execute()) {
        echo "<p style='color:green;'>Interview booked successfully!</p>";
    }
}
?>
