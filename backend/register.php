<?php
include "db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name     = $_POST['fullname'];
    $email    = $_POST['email'];
    $phone    = $_POST['phone'];
    $username = $_POST['username'];
    $role     = $_POST['role'];
    $password = $_POST['password'];

    if (!preg_match("/^[0-9]{10}$/", $phone)) {
        die("Phone number must be exactly 10 digits.");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Invalid Email format.");
    }

    if (!preg_match("/@gmail\.com$/", $email)) {
        die("Only @gmail.com emails are allowed.");
    }

    $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        die("Email already registered.");
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
        header("Location: ../login.php");
        exit();
    } else {
        echo "Registration failed: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
