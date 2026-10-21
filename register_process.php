<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullName']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];

    // Check if email already exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<script>alert('Email already exists!'); window.location='register.php';</script>";
        exit();
    }

    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert into database (reset_token & token_expiry are NULL by default)
    $stmt = $conn->prepare("INSERT INTO users (fullname, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $fullname, $email, $hashedPassword);

    if ($stmt->execute()) {
        echo "<script>alert('Registration successful! You can now login.'); window.location='login.php';</script>";
    } else {
        echo "<script>alert('Registration failed!'); window.location='register.php';</script>";
    }

    $stmt->close();
    $conn->close();
} else {
    header("Location: register.php");
    exit();
}
?>
