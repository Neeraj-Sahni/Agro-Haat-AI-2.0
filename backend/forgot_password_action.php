<?php
include "db.php";
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

header('Content-Type: application/json');

if(isset($_POST['email'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    
    // Check if email exists
    $check = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
    
    if(mysqli_num_rows($check) == 1) {
        // Generate OTP
        $otp = rand(100000, 999999);
        $expiry = date("Y-m-d H:i:s", strtotime("+10 minutes"));
        
        // Store OTP in database
        $update = mysqli_query($conn, 
            "UPDATE users SET reset_otp='$otp', otp_expiry='$expiry' WHERE email='$email'");
        
        if($update) {
            // Send email
            $mail = new PHPMailer(true);
            
            try {
                // Server settings
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'YOUR_GMAIL@gmail.com';
                $mail->Password = 'YOUR_APP_PASSWORD';
                $mail->SMTPSecure = 'tls';
                $mail->Port = 587;
                
                // Recipients
                $mail->setFrom('YOUR_GMAIL@gmail.com', 'MiniProject');
                $mail->addAddress($email);
                
                // Content
                $mail->isHTML(false);
                $mail->Subject = 'Password Reset OTP';
                $mail->Body = "Your OTP for password reset is: $otp\n\nThis OTP is valid for 10 minutes.";
                
                $mail->send();
                
                // Store email in session for verification
                $_SESSION['reset_email'] = $email;
                
                echo json_encode([
                    'success' => true,
                    'message' => 'OTP sent successfully!'
                ]);
            } catch(Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Failed to send email. Please try again.'
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Database error. Please try again.'
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Email not registered!'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request!'
    ]);
}
?>