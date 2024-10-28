<?php
session_start();
require 'db_connection.php'; // Include your database connection file

// Handle signup
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash the password
    $email = $_POST['email']; // Get the email address
    $phone = $_POST['phone']; // Get the phone number

    // Validate the email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('Invalid email format.');</script>";
    } else {
        // Check if username or email already exists in both tables
        $checkAdminQuery = "SELECT * FROM admins WHERE username = ? OR email = ?";
        $checkUserQuery = "SELECT * FROM users WHERE username = ? OR email = ?";

        // Check in admins table
        $stmt = $conn->prepare($checkAdminQuery);
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $adminResult = $stmt->get_result();

        // Check in users table
        $stmt = $conn->prepare($checkUserQuery);
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $userResult = $stmt->get_result();

        if ($adminResult->num_rows > 0 || $userResult->num_rows > 0) {
            echo "<script>alert('Username or email already exists.');</script>";
        } else {
            // Determine which table to insert into
            if ($username === 'admin') {
                // Insert into admins table
                $insertQuery = "INSERT INTO admins (username, password, email) VALUES (?, ?, ?)";
            } else {
                // Insert into users table
                $insertQuery = "INSERT INTO users (username, password, email, phone) VALUES (?, ?, ?, ?)";
            }

            // Prepare and execute the insert statement
            $stmt = $conn->prepare($insertQuery);
            if ($username === 'admin') {
                $stmt->bind_param("sss", $username, $password, $email);
            } else {
                $stmt->bind_param("ssss", $username, $password, $email, $phone);
            }

            if ($stmt->execute()) {
                echo "<script>alert('Signup successful! You can now log in.'); window.location.href='login.php';</script>";
            } else {
                echo "<script>alert('Error during signup.');</script>";
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
    <title>Signup</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="login">
    <div class="box">
        <span class="borderLine"></span>
        <form method="POST" action="">
            <h2>Signup</h2>
            <div class="inputbox">
                <input type="text" name="username" required="required">
                <span>Username</span>
                <i></i>
            </div>
            <div class="inputbox">
                <input type="password" name="password" required="required">
                <span>Password</span>
                <i></i>
            </div>
            <div class="inputbox">
                <input type="email" name="email" required="required">
                <span>Email</span>
                <i></i>
            </div>
            <div class="inputbox">
                <input type="phone" name="phone" required="required">
                <span>Phone Number</span>
                <i></i>
            </div>
            <div class="links">
                <a href="#">Forgot password?</a>
                <a href="login.php">Login</a>
            </div>
            <input type="submit" value="Signup">
        </form>
    </div>
</body>
</html>
