<?php 
session_start();
include 'db.php';
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Get email safely
$email = trim($_POST['email'] ?? '');

// Initialize message variables
$message = '';
$messageType = '';

if (empty($email)) {
    $message = "❌ Please enter your email!";
    $messageType = 'error';
} else {
    // Check if email exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Generate secure token
        $token = bin2hex(random_bytes(50));
        $expiry = date("Y-m-d H:i:s", strtotime("+1 hour"));

        // Save token
        $updateStmt = $conn->prepare("UPDATE users SET reset_token = ?, token_expiry = ? WHERE email = ?");
        $updateStmt->bind_param("sss", $token, $expiry, $email);
        $updateStmt->execute();

        // Send email
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'moralescubie@gmail.com'; 
            $mail->Password   = 'urkq hztd rfso fuof'; // Gmail App Password
            $mail->SMTPSecure = 'tls';
            $mail->Port       = 587;

            $mail->setFrom('moralescubie@gmail.com', 'Food Shop');
            $mail->addAddress($email);

            $resetLink = "http://localhost/food/reset_password.php?token=" . urlencode($token);

            $mail->isHTML(true);
            $mail->Subject = 'Password Reset Request';
            $mail->Body    = "
                <h3>Password Reset</h3>
                <p>Click the link below to reset your password:</p>
                <a href='$resetLink'>$resetLink</a>
                <br><br>
                <small>This link will expire in 1 hour.</small>
            ";

            $mail->send();
            $message = "✅ Reset link sent to your email!";
            $messageType = 'success';

        } catch (Exception $e) {
            $message = "❌ Mailer Error: " . $mail->ErrorInfo;
            $messageType = 'error';
        }

    } else {
        $message = "❌ Email not found!";
        $messageType = 'error';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Password Reset | MyCRM</title>
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
    margin-bottom:25px;
    color:#3b82f6;
    font-size:28px;
    font-weight:600;
}
.message-box p {
    font-size:16px;
    margin-bottom:20px;
}
.message-box .success {
    color:#28a745;
}
.message-box .error {
    color:#dc3545;
}
.message-box a {
    display:inline-block;
    text-decoration:none;
    color:#3b82f6;
    font-weight:600;
    padding:10px 25px;
    border-radius:25px;
    border:1px solid #3b82f6;
    transition:0.3s;
}
.message-box a:hover {
    background:#3b82f6;
    color:#fff;
}
</style>
</head>
<body>

<div class="message-box">
    <h2>Password Reset</h2>
    <p class="<?php echo $messageType; ?>"><?php echo $message; ?></p>
    <a href="login.php">Back to Login</a>
</div>

</body>
</html>
