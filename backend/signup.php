<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'db.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_type = $_POST['user_type'] ?? '';
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // === Validation ===
    if (empty($name)) $errors[] = "Name is required.";
    if (empty($email)) {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }
    if (empty($password)) $errors[] = "Password is required.";
    if (empty($user_type)) $errors[] = "Please select a user type.";

    // === Determine table name ===
    $table = '';
    if ($user_type === 'jobseeker') {
        $table = 'users';
    } elseif ($user_type === 'organisation') {
        $table = 'organisation';
    } else {
        $errors[] = "Invalid user type selected.";
    }

    // === Check for duplicate email ===
    if (empty($errors)) {
        $check = $conn->prepare("SELECT id FROM `$table` WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $errors[] = "Email already registered. Try logging in.";
        }
        $check->close();
    }

    // === Insert user into the correct table ===
    if (empty($errors)) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO `$table` (name, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $hashed);

        if ($stmt->execute()) {
            // Store user info in session after signup!
            $_SESSION['user_id'] = $stmt->insert_id;
            $_SESSION['name'] = $name;
            $_SESSION['role'] = $table;

            if ($user_type === 'jobseeker') {
                header("Location: dashboards/users_dashboard.php");
            } elseif ($user_type === 'organisation') {
                header("Location: dashboards/organisation_dashboard.php");
            }
            exit();
        } else {
            $errors[] = "Error saving data: " . $stmt->error;
        }

        $stmt->close();
    }
}
?>

<style>
/* Your existing CSS styling unchanged */
* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: #f4f6f8;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
}

form {
    background: #ffffff;
    padding: 30px 40px;
    border-radius: 12px;
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
    width: 100%;
    max-width: 400px;
}

form h2 {
    margin-bottom: 20px;
    text-align: center;
    color: #333;
}

form input[type="text"],
form input[type="email"],
form input[type="password"],
form select {
    width: 100%;
    padding: 12px 15px;
    margin-bottom: 15px;
    border: 1px solid #ccc;
    border-radius: 8px;
    transition: border 0.2s ease;
}

form input:focus,
form select:focus {
    border-color: #007bff;
    outline: none;
}

form button {
    width: 100%;
    padding: 12px;
    background-color: #007bff;
    color: #fff;
    font-weight: bold;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: background 0.3s ease;
}

form button:hover {
    background-color: #0056b3;
}

form a {
    display: block;
    text-align: center;
    margin-top: 15px;
    color: #007bff;
    text-decoration: none;
    font-size: 14px;
}

form a:hover {
    text-decoration: underline;
}

form ul {
    margin-bottom: 15px;
    list-style: none;
    padding-left: 0;
}

form ul li {
    color: red;
    font-size: 14px;
}
</style>

<form method="POST">
    <h2>Sign Up</h2>

    <?php if (!empty($errors)): ?>
        <ul style="color:red;">
            <?php foreach ($errors as $error): ?>
                <li><?= htmlspecialchars($error) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <input type="text" name="name" placeholder="Name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
    <input type="email" name="email" placeholder="Email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
    <input type="password" name="password" placeholder="Password">

    <select name="user_type">
        <option value="">Select User Type</option>
        <option value="jobseeker" <?= ($_POST['user_type'] ?? '') == 'jobseeker' ? 'selected' : '' ?>>Job Seeker</option>
        <option value="organisation" <?= ($_POST['user_type'] ?? '') == 'organisation' ? 'selected' : '' ?>>Organisation</option>
    </select>

    <button type="submit">Sign Up</button>
    <a href="login.php">Already have an account? Login</a>
</form>