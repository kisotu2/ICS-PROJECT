<?php
require_once("db.php");
session_start();
header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $data = json_decode(file_get_contents("php://input"), true);

    $Email = mysqli_real_escape_string($conn, $data['email']);
    $Password = $data['password'];

    $sql = "SELECT id, name, email_address, password, role FROM users WHERE email_address = '$Email' LIMIT 1";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) === 1) {
        $user = mysqli_fetch_assoc($result);

        if (password_verify($Password, $user['password'])) {
            // Set session variables
            $_SESSION['id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['role'] = $user['role'];

            echo json_encode([
                "success" => true,
                "message" => "Login successful",
                "role" => $user['role']
            ]);
        } else {
            echo json_encode(["success" => false, "message" => "Incorrect password"]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "User not found"]);
    }
}
?>
