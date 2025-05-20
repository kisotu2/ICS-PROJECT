<?php
header("Content-Type: application/json");
$data = json_decode(file_get_contents("php://input"));

$mysqli = new mysqli("localhost", "root", "", "Job");

if ($mysqli->connect_error) {
    echo json_encode(["message" => "Database connection failed"]);
    exit();
}

$name = $data->name ?? '';
$email = $data->email ?? '';
$password = password_hash($data->password ?? '', PASSWORD_DEFAULT);
$role = $data->role ?? '';

$table = $role === 'admin' ? 'admin' : ($role === 'organization' ? 'organisation' : 'users');

$stmt = $mysqli->prepare("INSERT INTO $table (name, email, password) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $name, $email, $password);

if ($stmt->execute()) {
    echo json_encode(["message" => "Registration successful for $role"]);
} else {
    echo json_encode(["message" => "Registration failed"]);
}

$stmt->close();
$mysqli->close();
?>
