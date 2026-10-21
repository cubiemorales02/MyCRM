<?php
session_start();
include 'db.php';

// Redirect logged-in users
if(isset($_SESSION['user_id'])){
    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Forgot Password | MyCRM</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<!-- Google Fonts + FontAwesome -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

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

.forgot-box {
    background:#fff;
    padding:40px 30px;
    width:100%;
    max-width:400px;
    border-radius:20px;
    box-shadow:0 25px 50px rgba(0,0,0,0.2);
    text-align:center;
}

.forgot-box h2 {
    margin-bottom:25px;
    color:#3b82f6;
    font-size:28px;
    font-weight:600;
}

.forgot-box input {
    width:100%;
    padding:12px 15px;
    border-radius:12px;
    border:1px solid #ddd;
    outline:none;
    font-size:15px;
    margin-bottom:18px;
    background:#f9f9f9;
    transition: all 0.3s ease;
}

.forgot-box input:focus {
    border-color:#3b82f6;
    background:#fff;
    box-shadow:0 0 0 2px rgba(59,130,246,0.15);
}

.forgot-box button {
    width:100%;
    padding:14px;
    border:none;
    border-radius:25px;
    background: linear-gradient(90deg, #3b82f6, #6366f1);
    color:#fff;
    font-size:16px;
    font-weight:600;
    cursor:pointer;
    transition: all 0.3s ease;
}

.forgot-box button:hover {
    opacity:0.9;
    transform:translateY(-2px);
    box-shadow:0 5px 15px rgba(0,0,0,0.1);
}

.forgot-box p {
    margin-top:20px;
    font-size:14px;
    color:#555;
}

.forgot-box p a {
    color:#3b82f6;
    font-weight:600;
    text-decoration:none;
}

.forgot-box p a:hover {
    color:#6366f1;
    text-decoration:underline;
}

.message {
    color:#28a745;
    margin-bottom:15px;
    font-size:14px;
}

.error {
    color:#dc3545;
    margin-bottom:15px;
    font-size:14px;
}
</style>
</head>
<body>

<div class="forgot-box">
<h2>Forgot Password</h2>

<?php
if(isset($_GET['error'])){
    echo "<p class='error'>".$_GET['error']."</p>";
} elseif(isset($_GET['success'])){
    echo "<p class='message'>".$_GET['success']."</p>";
}
?>

<form action="send_reset.php" method="POST">
    <input type="email" name="email" placeholder="Enter your email" required>
    <button type="submit">Send Reset Link</button>
</form>

<p><a href="login.php">Back to Login</a></p>
</div>

</body>
</html>
