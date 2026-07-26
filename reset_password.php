<?php
session_start();
include "config/db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $otp = $_POST['otp'] ?? '';
    $password = $_POST['password'] ?? '';
    $email = $_SESSION['reset_email'] ?? '';

    if (!empty($otp) && !empty($password) && !empty($email)) {
        
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND reset_otp = ? AND otp_expiry > NOW()");
        $stmt->bind_param("ss", $email, $otp);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $newpass = password_hash($password, PASSWORD_DEFAULT);
            $update = $conn->prepare("UPDATE users SET password = ?, reset_otp = NULL, otp_expiry = NULL WHERE email = ?");
            $update->bind_param("ss", $newpass, $email);
            $update->execute();
            
            $_SESSION['msg'] = "Password reset successful! Please login with your new password.";
            $_SESSION['msg_type'] = "success";
            unset($_SESSION['reset_email']);
            header("Location: login.php");
            exit();
        } else {
            $_SESSION['msg'] = "Invalid or expired OTP.";
            $_SESSION['msg_type'] = "danger";
            header("Location: reset_password.php");
            exit();
        }
    } else {
        $_SESSION['msg'] = "Please provide both OTP and a new password.";
        $_SESSION['msg_type'] = "danger";
        header("Location: reset_password.php");
        exit();
    }
}

if (!isset($_SESSION['reset_email'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="stylesheet" href="public/css/style.css?v=1">
    <style>
        .alert {
            padding: 10px; border-radius: 5px; margin-bottom: 15px; text-align: center; font-weight: bold;
        }
        .alert-danger { background: #ffebee; color: #c62828; border: 1px solid #ef9a9a; }
        .alert-success { background: #e8f5e9; color: #2e7d32; border: 1px solid #a5d6a7; }
    </style>
</head>
<body class="page">
    <div class="farmepage">
        <center><h1>SECURITY PORTAL</h1></center>
        <h1> AI Based Smart Farming Info Portal</h1>
        <div class="container" style="margin-top: 50px;">
            <div class="box">
                <h2><b>Reset Password</b></h2>
                <p style="color:white; text-align:center; margin-bottom:20px;">
                    We sent a 6-digit OTP to <b><?= htmlspecialchars($_SESSION['reset_email']) ?></b> and your registered phone number.
                </p>
                
                <?php if (isset($_SESSION['msg'])): ?>
                    <div class="alert alert-<?= $_SESSION['msg_type'] ?? 'info' ?>">
                        <?= $_SESSION['msg'] ?>
                    </div>
                    <?php unset($_SESSION['msg']); unset($_SESSION['msg_type']); ?>
                <?php endif; ?>

                <form method="POST" action="reset_password.php">
                    <label><b>Enter OTP :</b></label>
                    <input type="text" name="otp" placeholder="e.g. 123456" required><br><br>
                    
                    <label><b>New Password :</b></label>
                    <input type="password" name="password" placeholder="Create robust password" required><br><br>
                    
                    <button type="submit">Verify & Reset</button>
                    <a href="login.php" style="display:block; text-align:center; color:white; margin-top:20px;">Return to Login</a>
                </form>
            </div>
        </div>
    </div>
    <script src="public/js/logic.js?v=2S"></script>
</body>
</html>
