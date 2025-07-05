<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once 'db.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email)) $errors[] = "Email is required.";
    if (empty($password)) $errors[] = "Password is required.";

    if (empty($errors)) {
        $roles = [
            'admin' => 'admin_dashboard.php',
            'users' => 'dashboards/users_dashboard.php',
            'organisation' => '' // we'll handle org redirect dynamically
        ];

        foreach ($roles as $table => $redirect) {
            $stmt = $conn->prepare("SELECT id, name, password FROM `$table` WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows === 1) {
                $stmt->bind_result($id, $name, $hashed);
                $stmt->fetch();

                if (password_verify($password, $hashed)) {
                    $_SESSION['name'] = $name;
                    $_SESSION['role'] = $table;

                    switch ($table) {
                        case 'admin':
                            $_SESSION['admin_id'] = $id;
                            header("Location: $redirect");
                            exit;

                        case 'users':
                            $_SESSION['user_id'] = $id;
                            header("Location: $redirect");
                            exit;

                        case 'organisation':
                            $_SESSION['org_id'] = $id;

                            // Check if organization_id exists for this user
                            $check = $conn->prepare("SELECT organization_id FROM organisation WHERE id = ?");
                            $check->bind_param("i", $id);
                            $check->execute();
                            $result = $check->get_result();
                            $orgData = $result->fetch_assoc();
                            $check->close();

                            $_SESSION['organization_id'] = $orgData['organization_id'] ?? null;

                            if (empty($_SESSION['organization_id'])) {
                                header("Location: dashboards/edit_organisation_profile.php");
                            } else {
                                header("Location: dashboards/organisation_dashboard.php");
                            }
                            exit;
                    }

                } else {
                    $errors[] = "Incorrect password.";
                    break;
                }
            }

            $stmt->close();
        }

        if (empty($errors)) {
            $errors[] = "Email not found.";
        }
    }

    if (isset($conn)) $conn->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <style>
        body {
            font-family: sans-serif;
            background: #f2f2f2;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        form {
            background: white;
            padding: 25px 30px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            width: 350px;
            box-sizing: border-box;
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 10px 12px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 7px;
            box-sizing: border-box;
        }

        button {
            width: 100%;
            padding: 10px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 7px;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background: #0056b3;
        }

        .error {
            color: red;
            font-size: 14px;
            list-style-type: none;
            padding-left: 0;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <form method="POST" novalidate>
        <h2>Login</h2>

        <?php if (!empty($errors)): ?>
            <ul class="error">
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <input type="email" name="email" placeholder="Email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>

        <a href="signup.php" style="display:block;text-align:center;margin-top:15px;color:#007bff;">Don't have an account? Sign Up</a>
    </form>
</body>
</html>
