<?php
// login.php
include 'config/database.php';
session_start();

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username_or_email = $conn->real_escape_string($_POST['username_or_email']);
    $password = $_POST['password'];

    // Look up user by username or email
    $sql = "SELECT * FROM users WHERE username='$username_or_email' OR email='$username_or_email' LIMIT 1";
    $result = $conn->query($sql);
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        // Verify the password using password_verify()
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header("Location: index.php"); // Redirect to homepage or dashboard
            exit();
        } else {
            $message = "Invalid credentials!";
        }
    } else {
        $message = "Please register first!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Auto Cars</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include "header.php"; ?>
    <div class="form-container">
        <h1>Login</h1>
        <?php if ($message != '') { echo "<p class='error'>$message</p>"; } ?>
        <form method="POST" action="login.php">
            <input type="text" name="username_or_email" placeholder="Username or Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
        <p>Don't have an account? <a href="register.php" style="color: #fff;">Register here</a>.</p>
    </div>
    <?php include "footer.php"; ?>
</body>
</html>
