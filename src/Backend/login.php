<?php
header("Content-Type: application/json");
$data = json_decode(file_get_contents("php://input"));

$mysqli = new mysqli("localhost", "root", "", "Job");

if ($mysqli->connect_error) {
    echo json_encode(["message" => "Database connection failed"]);
    exit();
}

$email = $data->email ?? '';
$password = $data->password ?? '';
$role = $data->role ?? '';

$table = $role === 'admin' ? 'admin' : ($role === 'organization' ? 'organisation' : 'users');

$stmt = $mysqli->prepare("SELECT * FROM $table WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($user = $result->fetch_assoc()) {
    if (password_verify($password, $user['password'])) {
        echo json_encode(["message" => "Login successful"]);
    } else {
        echo json_encode(["message" => "Incorrect password"]);
    }
} else {
    echo json_encode(["message" => "User not found"]);
}

$stmt->close();
$mysqli->close();
?>
