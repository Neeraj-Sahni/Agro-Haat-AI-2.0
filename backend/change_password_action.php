<?php
session_start();
include "db.php";

$user_id = $_SESSION['user_id'];
$old = $_POST['old_password'];
$new = $_POST['new_password'];

$query = "SELECT password FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!password_verify($old, $user['password'])) {
    header("Location: ../change_password.php?error=Old password is incorrect");
    exit();
}
$newHash = password_hash($new, PASSWORD_DEFAULT);
$update = "UPDATE users SET password=? WHERE id=?";
$stmt = $conn->prepare($update);
$stmt->bind_param("si", $newHash, $user_id);
$stmt->execute();

header("Location: ../change_password.php?success=Password updated successfully");
