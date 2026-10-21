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
<title>Register | MyCRM</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<!-- Google Fonts + FontAwesome -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Poppins', sans-serif;
}

body{
    min-height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
    background: linear-gradient(135deg, #3b82f6, #6366f1, #818cf8);
    padding:20px;
}

.register-box{
    background:#fff;
    padding:40px 30px;
    width:100%;
    max-width:450px;
    border-radius:20px;
    box-shadow:0 25px 50px rgba(0,0,0,0.2);
    text-align:center;
}

.register-box h2{
    margin-bottom:25px;
    color:#3b82f6;
    font-size:28px;
    font-weight:600;
}

.input-group{
    display:flex;
    gap:15px;
    margin-bottom:15px;
}

.input-box{
    margin-bottom:18px;
    text-align:left;
}

.input-box.full-width{
    width:100%;
}

.input-box.half-width{
    flex:1;
}

.input-box label{
    display:block;
    font-size:14px;
    color:#374151;
    font-weight:500;
    margin-bottom:6px;
}

.input-box input{
    width:100%;
    padding:12px 15px;
    border-radius:12px;
    border:1px solid #ddd;
    outline:none;
    font-size:15px;
    background:#f9f9f9;
    transition: all 0.3s ease;
}

.input-box input:focus{
    border-color:#3b82f6;
    background:#fff;
    box-shadow:0 0 0 2px rgba(59,130,246,0.15);
}

.register-btn{
    width:100%;
    padding:14px;
    border:none;
    border-radius:25px;
    background: linear-gradient(90deg, #3b82f6, #6366f1);
    color:#fff;
    font-size:16px;
    font-weight:600;
    cursor:pointer;
    margin-top:10px;
    transition:all 0.3s ease;
}

.register-btn:hover{
    opacity:0.9;
    transform:translateY(-2px);
    box-shadow:0 5px 15px rgba(0,0,0,0.1);
}

.login-link{
    margin-top:20px;
    font-size:14px;
    color:#555;
}

.login-link a{
    color:#3b82f6;
    font-weight:600;
    text-decoration:none;
}

.login-link a:hover{
    color:#6366f1;
    text-decoration:underline;
}

.strength-meter{
    height:8px;
    width:100%;
    background:#eee;
    border-radius:4px;
    margin-top:8px;
    overflow:hidden;
}

.strength-bar{
    height:100%;
    width:0%;
    border-radius:4px;
    transition: width 0.3s ease;
}

.strength-text{
    font-size:12px;
    margin-top:6px;
    min-height:18px;
}

.strength-weak{color:#dc3545;}
.strength-fair{color:#ffc107;}
.strength-good{color:#17a2b8;}
.strength-strong{color:#28a745;}

.match-success{color:#28a745; font-size:12px; margin-top:4px;}
.match-error{color:#dc3545; font-size:12px; margin-top:4px;}

.password-requirements{
    background:#f8f9fa;
    padding:10px 12px;
    border-radius:8px;
    margin-top:10px;
    font-size:12px;
}

.requirement{
    margin:5px 0;
    display:flex;
    align-items:center;
    gap:8px;
}

.requirement i{font-style:normal; font-size:14px;}
.requirement.valid{color:#28a745;}
.requirement.invalid{color:#6c757d;}
</style>
</head>
<body>

<div class="register-box">
  <h2>Create Your Account</h2>

  <form id="registerForm" action="register_process.php" method="POST" onsubmit="return validateForm(event)">
    
    <div class="input-box full-width">
      <label>Full Name</label>
      <input type="text" id="fullName" name="fullName" placeholder="Enter your full name" required>
    </div>

    <div class="input-group">
      <div class="input-box half-width">
        <label>Email Address</label>
        <input type="email" id="email" name="email" placeholder="email@example.com" required>
      </div>
      <div class="input-box half-width">
        <label>Phone Number</label>
        <input type="tel" id="phone" name="phone" placeholder="1234567890" pattern="[0-9]{10,15}" required>
      </div>
    </div>

    <div class="input-box full-width">
      <label>Password</label>
      <input type="password" id="password" name="password" placeholder="Create a strong password" onkeyup="checkPasswordStrength()" required>
      <div class="strength-meter"><div class="strength-bar" id="strength-bar"></div></div>
      <div class="strength-text" id="strength-text"></div>

      <div class="password-requirements" id="passwordRequirements">
        <div class="requirement" id="req-length"><i>○</i> At least 8 characters</div>
        <div class="requirement" id="req-uppercase"><i>○</i> 1 uppercase letter</div>
        <div class="requirement" id="req-lowercase"><i>○</i> 1 lowercase letter</div>
        <div class="requirement" id="req-number"><i>○</i> 1 number</div>
        <div class="requirement" id="req-special"><i>○</i> 1 special character (!@#$%^&*)</div>
      </div>
    </div>

    <div class="input-box full-width">
      <label>Confirm Password</label>
      <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Re-enter your password" onkeyup="checkPasswordMatch()" required>
      <div class="strength-text" id="match-text"></div>
    </div>

    <button type="submit" class="register-btn">Create Account</button>

    <div class="login-link">
      Already have an account? <a href="login.php">Login here</a>
    </div>

  </form>
</div>

<script>
// Password strength
function checkPasswordStrength() {
  const password = document.getElementById('password').value;
  const strengthBar = document.getElementById('strength-bar');
  const strengthText = document.getElementById('strength-text');

  const requirements = {
    length: password.length >= 8,
    uppercase: /[A-Z]/.test(password),
    lowercase: /[a-z]/.test(password),
    number: /[0-9]/.test(password),
    special: /[!@#$%^&*(),.?":{}|<>]/.test(password)
  };

  for(let [key, valid] of Object.entries(requirements)){
    const el = document.getElementById(`req-${key}`);
    if(el){
      el.className = `requirement ${valid?'valid':'invalid'}`;
      el.querySelector('i').textContent = valid?'✅':'○';
    }
  }

  const score = Object.values(requirements).filter(Boolean).length;
  let width = (score/5)*100 + '%';
  strengthBar.style.width = width;

  if(score<=2){strengthBar.style.background='#dc3545'; strengthText.textContent='Weak password'; strengthText.className='strength-text strength-weak';}
  else if(score===3){strengthBar.style.background='#ffc107'; strengthText.textContent='Fair password'; strengthText.className='strength-text strength-fair';}
  else if(score===4){strengthBar.style.background='#17a2b8'; strengthText.textContent='Good password'; strengthText.className='strength-text strength-good';}
  else if(score===5){strengthBar.style.background='#28a745'; strengthText.textContent='Strong password'; strengthText.className='strength-text strength-strong';}
}

// Password match
function checkPasswordMatch() {
  const password = document.getElementById('password').value;
  const confirmPassword = document.getElementById('confirmPassword').value;
  const matchText = document.getElementById('match-text');

  if(confirmPassword.length===0){ matchText.textContent=''; return; }

  if(password===confirmPassword){
    matchText.textContent='✅ Passwords match';
    matchText.className='match-success';
  } else {
    matchText.textContent='❌ Passwords do not match';
    matchText.className='match-error';
  }
}

// Form validation
function validateForm(event){
  const password = document.getElementById('password').value;
  const confirmPassword = document.getElementById('confirmPassword').value;

  if(password!==confirmPassword){ alert('Passwords do not match'); return false; }

  return true;
}
</script>

</body>
</html>
