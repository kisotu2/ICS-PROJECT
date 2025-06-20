<style>
    /* Reset some default styles */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

/* Body Styling */
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: linear-gradient(to right, #e0eafc, #cfdef3);
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
}

/* Form Container */
form {
    background-color: #fff;
    padding: 30px 40px;
    border-radius: 10px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    width: 100%;
    max-width: 400px;
}

/* Heading */
form h2 {
    text-align: center;
    margin-bottom: 20px;
    color: #333;
}

/* Input Fields */
form input,
form select {
    width: 100%;
    padding: 12px 15px;
    margin: 10px 0;
    border: 1px solid #ccc;
    border-radius: 8px;
    font-size: 16px;
    transition: border 0.3s ease;
}

form input:focus,
form select:focus {
    border-color: #4a90e2;
    outline: none;
}

/* Submit Button */
form button {
    width: 100%;
    padding: 12px;
    background-color: #4a90e2;
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    cursor: pointer;
    transition: background-color 0.3s ease;
    margin-top: 10px;
}

form button:hover {
    background-color: #357ab7;
}

/* Confirmation Text */
p {
    text-align: center;
    margin-top: 20px;
    font-size: 15px;
    color: #2d2d2d;
}

</style>
<form method="POST" action="reset_password.php">
    <h2>Reset Password</h2>
    <input type="email" name="email" placeholder="Enter your email" required>
    <select name="user_type" required>
        <option value="">Select User Type</option>
        <option value="jobseeker">Job Seeker</option>
        <option value="organisation">Organisation</option>
        <option value="admin">Admin</option>
    </select>
    <button type="submit">Send Reset Link</button>
</form>

<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $user_type = $_POST['user_type'];

    echo "<p>If this email exists in the system, a reset link will be sent.</p>";

}
?>
