<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Login / Signup</title>
    <link rel="stylesheet" href="LoginSignup.css" />
</head>
<body>

<?php
    $action = isset($_POST['action']) ? $_POST['action'] : 'Sign Up';
?>

    <div class="container">
        <div class="header">
            <div class="text"><?php echo $action; ?></div>
            <div class="underline"></div>
        </div>

        <form method="POST" action="<?php echo $action === 'Sign Up' ? 'signup.php' : 'login.php'; ?>" class="Inputs">

            <?php if ($action !== "Login"): ?>
            <div class="input">
                <img src="Assets/user.png" alt="User Icon" />
                <input type="text" name="name" placeholder="Full Name" required />
            </div>
            <?php endif; ?>

            <div class="input">
                <img src="Assets/email.png" alt="Email Icon" />
                <input type="email" name="email" placeholder="Email Address" required />
            </div>

            <div class="input">
                <img src="Assets/password.png" alt="Password Icon" />
                <input type="password" name="password" placeholder="Password" required />
            </div>

            <select name="role" class="role-select">
                <option value="job_seeker">Job Seeker</option>
                <option value="organization">Organization</option>
                <option value="admin">Admin</option>
            </select>

            <?php if ($action === "Login"): ?>
            <div class="forgot-password">
                Forgot Password? <span>Click Here</span>
            </div>
            <?php endif; ?>

            <div class="submit-container">
                <button type="submit" name="action" value="Sign Up" class="submit <?php echo $action === 'Sign Up' ? 'gray' : ''; ?>">Sign Up</button>
                <button type="submit" name="action" value="Login" class="submit <?php echo $action === 'Login' ? 'gray' : ''; ?>">Login</button>
            </div>

        </form>
    </div>

</body>
</html>
