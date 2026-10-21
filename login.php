<?php
session_start();
if(isset($_SESSION['user_id'])){
    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Login | MyCRM</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<!-- FontAwesome for social icons -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Poppins', sans-serif;
}

body{
    height:100vh;
    background: linear-gradient(135deg, #3b82f6, #6366f1, #818cf8);
    display:flex;
    justify-content:center;
    align-items:center;
}

.login-box{
    background:#fff;
    padding:40px 30px;
    width:100%;
    max-width:400px;
    border-radius:20px;
    box-shadow:0 25px 50px rgba(0,0,0,0.2);
    text-align:center;
}

.login-box h2{
    margin-bottom:25px;
    color:#3b82f6;
    font-size:1.8rem;
}

.input-box{
    margin-bottom:18px;
    text-align:left;
}

.input-box label{
    font-size:14px;
    color:#374151;
    display:block;
    margin-bottom:5px;
}

.input-box input{
    width:100%;
    padding:12px;
    border-radius:12px;
    border:1px solid #d1d5db;
    font-size:14px;
}

.input-box input:focus{
    border-color:#3b82f6;
    outline:none;
}

.login-btn{
    width:100%;
    padding:14px;
    border:none;
    border-radius:25px;
    background: linear-gradient(to right, #3b82f6, #6366f1);
    color:#fff;
    font-size:15px;
    font-weight:500;
    cursor:pointer;
    transition:0.3s;
    margin-top:10px;
}

.login-btn:hover{
    opacity:0.9;
}

.extra-links{
    margin-top:20px;
    font-size:13px;
}

.extra-links a{
    text-decoration:none;
    color:#3b82f6;
    font-weight:600;
}

.error{
    color:red;
    text-align:center;
    margin-bottom:12px;
}

.or-separator{
    margin:20px 0;
    font-weight:bold;
    color:#3b82f6;
}

.btn-social{
    width:100%;
    padding:12px;
    border-radius:25px;
    font-weight:600;
    display:flex;
    align-items:center;
    justify-content:center;
    text-decoration:none;
    margin-bottom:12px;
    font-size:14px;
    transition:0.3s;
}

.btn-google{
    border:1px solid #DB4437;
    color:#DB4437;
    background:#fff;
}
.btn-google i{
    margin-right:8px;
}
.btn-google:hover{
    background:#DB4437;
    color:#fff;
}
.btn-google:hover i{
    color:#fff;
}

.btn-facebook{
    border:1px solid #1877F2;
    color:#1877F2;
    background:#fff;
}
.btn-facebook i{
    margin-right:8px;
}
.btn-facebook:hover{
    background:#1877F2;
    color:#fff;
}
.btn-facebook:hover i{
    color:#fff;
}

</style>
</head>
<body>

<div class="login-box">
  <h2>Welcome Back to MyCRM</h2>

  <?php 
  if(isset($_GET['error'])){
      echo "<p class='error'>".$_GET['error']."</p>";
  } 
  ?>

  <form action="login_process.php" method="POST">
    <div class="input-box">
      <label>Email</label>
      <input type="email" name="email" required>
    </div>
    <div class="input-box">
      <label>Password</label>
      <input type="password" name="password" required>
    </div>
    
    <button type="submit" class="login-btn">Login</button>

    <div class="or-separator">OR</div>

    <a href="google-auth.php" class="btn-social btn-google">
        <i class="fab fa-google"></i> Login with Google
    </a>

    <a href="#" class="btn-social btn-facebook">
        <i class="fab fa-facebook-f"></i> Login with Facebook
    </a>

    <div class="extra-links">
      <p>Don't have an account? <a href="register.php">Register</a></p>
      <p><a href="forgot_password.php">Forgot Password?</a></p>
    </div>
  </form>
</div>

</body>
</html>
