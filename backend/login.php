<?php
session_start();
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    $stmt = $conn->prepare("SELECT id, fullname, email, password, image, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['fullname'] = $user['fullname'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['image'] = $user['image'];
            $_SESSION['role'] = $user['role'];
            
            if ($user['role'] === 'Farmer') {
                header("Location: ../farmer_dashboard.php");
            } else if ($user['role'] === 'Customer') {
                header("Location: ../customer_dashboard.php");
            } else {
                header("Location: ../index.php");
            }
            exit();
        }
    }
}
?>