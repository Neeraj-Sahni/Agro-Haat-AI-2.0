<?php
session_start();
include "../config/db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name     = $_POST['fullname'];
    $email    = $_POST['email'];
    $phone    = $_POST['phone'];
    $username = $_POST['username'];
    $role     = $_POST['role'];
    $password = $_POST['password'];

    if (!preg_match("/^[0-9]{10}$/", $phone)) {
        $_SESSION['msg'] = "Phone number must be exactly 10 digits.";
        $_SESSION['msg_type'] = "danger";
        header("Location: ../register.php");
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['msg'] = "Invalid Email format.";
        $_SESSION['msg_type'] = "danger";
        header("Location: ../register.php");
        exit();
    }

    if (!preg_match("/@gmail\.com$/", $email)) {
        $_SESSION['msg'] = "Only @gmail.com emails are allowed.";
        $_SESSION['msg_type'] = "danger";
        header("Location: ../register.php");
        exit();
    }

    $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $_SESSION['msg'] = "Email already registered.";
        $_SESSION['msg_type'] = "danger";
        header("Location: ../register.php");
        exit();
    }
    $check->close();

    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare(
        "INSERT INTO users (fullname, email, phone, username, role, password, image) 
         VALUES (?, ?, ?, ?, ?, ?, 'default.jpg')"
    );

    $stmt->bind_param("ssssss", 
        $name, 
        $email, 
        $phone, 
        $username, 
        $role, 
        $hashedPassword
    );

    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        $_SESSION['msg'] = "Registration successful! Please login.";
        $_SESSION['msg_type'] = "success";
        header("Location: ../login.php");
        exit();
    } else {
        $stmt->close();
        $conn->close();
        $_SESSION['msg'] = "Registration failed: " . $stmt->error;
        $_SESSION['msg_type'] = "danger";
        header("Location: ../register.php");
        exit();
    }
}
?>
