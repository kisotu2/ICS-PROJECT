<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: login_form.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Job Seeker Page</title>
</head>
<body>
    <h1>Welcome, <?php echo $_SESSION['name']; ?> (Job Seeker)</h1>
    <a href="logout.php">Logout</a>
</body>
</html>