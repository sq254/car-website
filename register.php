<?php
// register.php
include 'config/database.php';
session_start();

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and collect form inputs
    $username = $conn->real_escape_string($_POST['username']);
    $email    = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Check if passwords match
    if ($password !== $confirm_password) {
        $message = "Passwords do not match!";
    } else {
        // Check if the username or email already exists
        $check_sql = "SELECT * FROM users WHERE username='$username' OR email='$email'";
        $check_result = $conn->query($check_sql);
        if ($check_result->num_rows > 0) {
            $message = "Username or Email already exists.";
        } else {
            // Hash the password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (username, email, password) VALUES ('$username', '$email', '$hashed_password')";
            if ($conn->query($sql) === TRUE) {
                $message = "Registration successful! You can now <a href='login.php' style='color: #fff;'>login</a>.";
            } else {
                $message = "Error: " . $conn->error;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Auto Cars</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include "header.php"; ?>
    <div class="form-container">
        <h1>Register</h1>
        <?php if ($message != '') { echo "<p class='error'>$message</p>"; } ?>
        <form method="POST" action="register.php">
            <input type="text" name="username" placeholder="Username" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            <button type="submit">Register</button>
        </form>
        <p>Already have an account? <a href="login.php" style="color: #fff;">Login here</a>.</p>
    </div>
    <?php include "footer.php"; ?>
</body>
</html>
