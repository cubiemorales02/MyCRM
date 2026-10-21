<?php
include 'db.php';

$token = $_POST['token'] ?? '';
$new_password = $_POST['new_password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

if (empty($token) || empty($new_password) || empty($confirm_password)) {
    die("Invalid request!");
}

// Check if new password and confirm password match
if ($new_password !== $confirm_password) {
    die("New password and confirm password do not match!");
}

// Check token in database
$stmt = $conn->prepare("SELECT * FROM users WHERE reset_token = ? AND token_expiry > NOW()");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Invalid or expired token!");
}

// Hash new password
$hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);

// Update password
$update = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, token_expiry = NULL WHERE reset_token = ?");
$update->bind_param("ss", $hashedPassword, $token);
$update->execute();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Password Updated | MyCRM</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
<style>
* {
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Poppins', sans-serif;
}
body {
    min-height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
    background: linear-gradient(135deg, #3b82f6, #6366f1, #818cf8);
    padding:20px;
}
.message-box {
    background:#fff;
    padding:40px 30px;
    width:100%;
    max-width:400px;
    border-radius:20px;
    box-shadow:0 25px 50px rgba(0,0,0,0.2);
    text-align:center;
}
.message-box h2 {
    margin-bottom:20px;
    color:#3b82f6;
    font-size:26px;
    font-weight:600;
}
.success-msg {
    color:#28a745;
    font-size:16px;
    margin-bottom:25px;
}
.error-msg {
    color:#dc3545;
    font-size:16px;
    margin-bottom:25px;
}
.btn-login {
    display:inline-block;
    padding:12px 25px;
    border:none;
    border-radius:12px;
    background: linear-gradient(90deg, #3b82f6, #6366f1, #818cf8);
    color:#fff;
    font-size:15px;
    font-weight:600;
    cursor:pointer;
    text-decoration:none;
    transition:all 0.3s ease;
}
.btn-login:hover {
    opacity:0.9;
    transform:translateY(-2px);
    box-shadow:0 5px 15px rgba(0,0,0,0.1);
}
</style>
</head>
<body>

<div class="message-box">
    <h2>Password Updated</h2>
    <p class="success-msg">✅ Your password has been successfully updated!</p>
    <a href="login.php" class="btn-login">Back to Login</a>
</div>

</body>
</html>
