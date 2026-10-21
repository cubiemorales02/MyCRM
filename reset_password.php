<?php
include 'db.php';

$token = $_GET['token'] ?? '';

if (empty($token)) {
    die("Invalid request!");
}

// Check token in database
$stmt = $conn->prepare("SELECT * FROM users WHERE reset_token = ? AND token_expiry > NOW()");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Invalid or expired token!");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Reset Password | MyCRM</title>
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
.reset-box {
    background:#fff;
    padding:40px 30px;
    width:100%;
    max-width:400px;
    border-radius:20px;
    box-shadow:0 25px 50px rgba(0,0,0,0.2);
    text-align:center;
}
.reset-box h2 {
    margin-bottom:25px;
    color:#3b82f6;
    font-size:28px;
    font-weight:600;
}
.input-box {
    margin-bottom:15px;
    text-align:left;
}
.input-box label {
    display:block;
    margin-bottom:5px;
    font-weight:500;
    color:#444;
}
.input-box input {
    width:100%;
    padding:12px;
    border-radius:10px;
    border:1px solid #ddd;
    outline:none;
    font-size:15px;
    transition:0.3s;
    background:#f9f9f9;
}
.input-box input:focus {
    border-color:#3b82f6;
    background:#fff;
    box-shadow:0 0 0 2px rgba(59,130,246,0.1);
}
.update-btn {
    width:100%;
    padding:14px;
    border:none;
    border-radius:12px;
    background: linear-gradient(90deg, #3b82f6, #6366f1, #818cf8);
    color:#fff;
    font-size:16px;
    font-weight:600;
    cursor:pointer;
    margin-top:10px;
    transition:all 0.3s ease;
}
.update-btn:hover {
    opacity:0.9;
    transform:translateY(-2px);
    box-shadow:0 5px 15px rgba(0,0,0,0.1);
}
.back-link {
    display:inline-block;
    margin-top:20px;
    text-decoration:none;
    color:#3b82f6;
    font-weight:600;
    transition:0.3s;
}
.back-link:hover {
    text-decoration:underline;
    color:#6366f1;
}
</style>
</head>
<body>

<div class="reset-box">
    <h2>Reset Your Password</h2>

    <form method="POST" action="update_password.php">
        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

        <div class="input-box">
            <label>Current Password</label>
            <input type="password" name="current_password" placeholder="Enter current password" required>
        </div>

        <div class="input-box">
            <label>New Password</label>
            <input type="password" name="new_password" placeholder="Enter new password" required>
        </div>

        <div class="input-box">
            <label>Confirm New Password</label>
            <input type="password" name="confirm_password" placeholder="Re-enter new password" required>
        </div>

        <button type="submit" class="update-btn">Update Password</button>
    </form>

    <a href="login.php" class="back-link">Back to Login</a>
</div>

</body>
</html>
