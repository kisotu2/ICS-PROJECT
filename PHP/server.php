<?php
header('Access-Control-Allow-Origin: http://localhost:3000');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Methods: POST');
header('Content-Type: application/json');

// Get the raw POST data
$data = json_decode(file_get_contents("php://input"), true);

if (isset($data['email']) && isset($data['password'])) {
    $name = $data['name'] ?? '';
    $email = $data['email'];
    $password = $data['password'];

    // You can add DB connection and store or check login here
    // For now, just send response back
    echo json_encode([
        "status" => "success",
        "message" => "Received data for $email"
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid input"
    ]);
}
?>
