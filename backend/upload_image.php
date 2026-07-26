<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['profile_image'])) {
    $user_id = $_SESSION['user_id'];
    $target_dir = "../uploads/profile_pictures/";
    
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $file_extension = pathinfo($_FILES["profile_image"]["name"], PATHINFO_EXTENSION);
    $new_filename = "user_" . $user_id . "_" . time() . "." . $file_extension;
    $target_file = $target_dir . $new_filename;
    
    if ($_FILES["profile_image"]["size"] > 5000000) {
        $_SESSION['error'] = "File is too large. Maximum size is 5MB.";
        header("Location: ../profile.php");
        exit();
    }
    $allowed_extensions = array("jpg", "jpeg", "png", "gif");
    if (!in_array(strtolower($file_extension), $allowed_extensions)) {
        $_SESSION['error'] = "Only JPG, JPEG, PNG & GIF files are allowed.";
        header("Location: ../profile.php");
        exit();
    }
    $stmt = $conn->prepare("SELECT image FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if ($user['image'] != 'default.jpg') {
        $old_image = $target_dir . $user['image'];
        if (file_exists($old_image)) {
            unlink($old_image);
        }
    }
    if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target_file)) {
        $update_stmt = $conn->prepare("UPDATE users SET image = ? WHERE id = ?");
        $update_stmt->bind_param("si", $new_filename, $user_id);
        
        if ($update_stmt->execute()) {
            $_SESSION['image'] = $new_filename;
            $_SESSION['success'] = "Profile picture updated successfully!";
        } else {
            $_SESSION['error'] = "Failed to update database.";
        }
    } else {
        $_SESSION['error'] = "Sorry, there was an error uploading your file.";
    }
    
    header("Location: ../profile.php");
    exit();
}
?>