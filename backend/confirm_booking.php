<?php
$conn = new mysqli('localhost', 'root', '', 'jobseekers');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$interest_id = $_POST['interest_id'] ?? null;
$date = $_POST['interview_date'] ?? null;

if ($interest_id && $date) {
    $stmt = $conn->prepare("INSERT INTO interviews (interest_id, interview_date) VALUES (?, ?)");
    $stmt->bind_param("is", $interest_id, $date);
    $stmt->execute();
    $stmt->close();

    echo "Interview booked for $date. <a href='jobseeker_dashboard.php'>Back</a>";
} else {
    echo "Missing data.";
}
?>
