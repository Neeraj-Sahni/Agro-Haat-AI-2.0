<?php
include "../config/db.php";
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

header('Content-Type: application/json');

if (!isset($_POST['email'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request!']);
    exit;
}

$email = mysqli_real_escape_string($conn, trim($_POST['email']));

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format!']);
    exit;
}

// Email check karo
$check = mysqli_query($conn, "SELECT id, phone, fullname FROM users WHERE email='$email'");

if (mysqli_num_rows($check) != 1) {
    echo json_encode(['success' => false, 'message' => 'Email not registered!']);
    exit;
}

$user  = mysqli_fetch_assoc($check);
$phone = $user['phone'] ?? '';
$name  = $user['fullname'] ?? 'User';

// OTP generate karo
$otp    = rand(100000, 999999);
$expiry = date("Y-m-d H:i:s", strtotime("+10 minutes"));

// Database mein save karo
$update = mysqli_query($conn, "UPDATE users SET reset_otp='$otp', otp_expiry='$expiry' WHERE email='$email'");

if (!$update) {
    echo json_encode(['success' => false, 'message' => 'Database error. Try again.']);
    exit;
}

// Session mein save karo
$_SESSION['reset_email'] = $email;
$_SESSION['debug_otp']   = $otp; // Testing ke liye

// Email bhejne ki koshish
$subject  = 'Password Reset OTP - Agro-Haat';
$message  = "Dear $name,\n\nYour OTP for password reset is: $otp\n\nThis OTP is valid for 10 minutes.\n\nIf you did not request this, please ignore.\n\nAgro-Haat Team";
$headers  = "From: noreply@agro-haat.com\r\n";
$headers .= "Reply-To: support@agro-haat.com\r\n";

// XAMPP pe mail nahi jaati — production mein PHPMailer use karo
@mail($email, $subject, $message, $headers);

// Try PHPMailer for actual email sending
try {
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'AAPKI_GMAIL@gmail.com'; // Apni Gmail
    $mail->Password = 'GMAIL_APP_PASSWORD';     // App Password
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;
    $mail->setFrom('AAPKI_GMAIL@gmail.com', 'Agro-Haat');
    $mail->addAddress($email, $name);
    $mail->Subject = 'Password Reset OTP - Agro-Haat';
    $mail->Body = "Dear $name,\n\nYour OTP: $otp\n\nValid for 10 minutes.\n\nAgro-Haat Team";
    $mail->send();
} catch (Exception $e) {
    // PHPMailer failed — fallback to native mail() already sent above
}

echo json_encode([
    'success'   => true,
    'message'   => 'OTP sent successfully!',
    'debug_otp' => $otp // Testing ke liye — production mein ye hatao
]);
?>