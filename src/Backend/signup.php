<?php
require_once("db.php");
header("Content-Type: application/json");

try {
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $data = json_decode(file_get_contents("php://input"), true);

        $Name = mysqli_real_escape_string($conn, $data['name']);
        $Email = mysqli_real_escape_string($conn, $data['email']);
        $Password = password_hash($data['password'], PASSWORD_DEFAULT);
        $Role = mysqli_real_escape_string($conn, $data['role']);

        // Check if email already exists
        $check_sql = "SELECT id FROM job_seekers WHERE email_address = '$Email'";
        $check_result = mysqli_query($conn, $check_sql);

        if (mysqli_num_rows($check_result) > 0) {
            echo json_encode(["success" => false, "message" => "Email already registered"]);
            exit;
        }

        // Insert user
        $sql = "INSERT INTO job_seekers (name, email_address, password, role) VALUES ('$Name', '$Email', '$Password', '$Role')";

        if ($conn->query($sql) === TRUE) {
            echo json_encode(["success" => true, "message" => "Signup successful", "role" => $Role]);
        } else {
            echo json_encode(["success" => false, "message" => "Error: " . $conn->error]);
        }
    }
} catch (Exception $ex) {
    echo json_encode(["success" => false, "message" => "No Connection"]);
}
?>
