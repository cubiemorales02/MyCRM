<?php
session_start();
require_once 'db.php'; // your database connection

if(isset($_POST['email']) && isset($_POST['password'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Example query (adjust to your DB schema)
    $stmt = $conn->prepare("SELECT id, fullname, email, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows === 1){
        $user = $result->fetch_assoc();

        // Verify password (assuming hashed)
        if(password_verify($password, $user['password'])){
            // --- SESSION SETUP FOR PROFILE DISPLAY ---
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['fullname'] = $user['fullname']; // User full name
            $_SESSION['email'] = $user['email'];       // User email
            // ------------------------------------------

            header("Location: dashboard.php");
            exit();
        } else {
            header("Location: login.php?error=Invalid credentials");
            exit();
        }
    } else {
        header("Location: login.php?error=User not found");
        exit();
    }
} else {
    header("Location: login.php?error=Please fill in all fields");
    exit();
}
