<?php
session_start();
require 'db_connection.php'; // Include the database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']); // Trim whitespace
    $password = $_POST['password'];

    // Check for admin credentials
    $adminQuery = "SELECT * FROM admins WHERE username = ?";
    $stmt = $conn->prepare($adminQuery);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $admin = $result->fetch_assoc();
        // Verify the hashed password
        if (password_verify($password, $admin['password'])) {
            // Set session variables for admin
            $_SESSION['user_role'] = 'admin';
            $_SESSION['user_logged_in'] = true; // Renamed for consistency
            header('Location: admin_dashboard.php');
            exit();
        }
    }

    // Check for regular user credentials
    $userQuery = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($userQuery);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        // Verify the hashed password
        if (password_verify($password, $user['password'])) {
            // Set session variables for user
            $_SESSION['user_role'] = 'user';
            $_SESSION['user_logged_in'] = true; // Renamed for consistency
            $_SESSION['user_id'] = $user['id']; // Store user ID in session for further use
            header('Location: index.php'); // Redirect to the normal user page
            exit();
        }
    }

    // Handle invalid login
    $error_message = "Invalid username or password.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="login">
    <div class="box">
        <span class="borderLine"></span>
        <form method="POST" action="">
            <h2>Login</h2>
            <?php if (isset($error_message)): ?>
                <p style="color: red;"><?= htmlspecialchars($error_message) ?></p>
            <?php endif; ?>
            <div class="inputbox">
                <input type="text" name="username" required="required" placeholder="Username">
                <span>Username</span>
                <i></i>
            </div>
            <div class="inputbox">
                <input type="password" name="password" required="required" placeholder="Password">
                <span>Password</span>
                <i></i>
            </div>
            <div class="links">
                <a href="#">Forgot password?</a>
                <a href="signup.php">Signup</a>
            </div>
            <input type="submit" value="Login">
        </form>
    </div>
</body>
</html>
