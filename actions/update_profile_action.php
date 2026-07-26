<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$fullname = $_POST['fullname'] ?? '';
$phone = $_POST['phone'] ?? '';
$address = $_POST['address'] ?? '';

if (!empty($fullname)) {
    $stmt = $conn->prepare("UPDATE users SET fullname = ?, phone = ?, address = ? WHERE id = ?");
    $stmt->bind_param("sssi", $fullname, $phone, $address, $user_id);
    
    if ($stmt->execute()) {
        $_SESSION['msg'] = "Profile updated successfully!";
        $_SESSION['msg_type'] = "success";
    } else {
        $_SESSION['msg'] = "Update failed.";
        $_SESSION['msg_type'] = "danger";
    }
}

header("Location: ../profile.php");
exit();
?>
