<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';

if ($action === 'add') {
    $name = $_POST['machinery_name'];
    $price = $_POST['rental_price'];
    $desc = $_POST['description'];
    
    $machinery_image = 'default_machinery.jpg';
    if (isset($_FILES['machinery_image']) && $_FILES['machinery_image']['error'] == 0) {
        $allowed = array('jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'gif' => 'image/gif', 'png' => 'image/png');
        $filename = $_FILES['machinery_image']['name'];
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        if (array_key_exists(strtolower($ext), $allowed)) {
            $new_filename = uniqid("machinery_") . "." . $ext;
            $upload_path = "../public/uploads/machinery/" . $new_filename;
            if (!is_dir("../public/uploads/machinery/")) {
                mkdir("../public/uploads/machinery/", 0777, true);
            }
            if (move_uploaded_file($_FILES["machinery_image"]["tmp_name"], $upload_path)) {
                $machinery_image = $new_filename;
            }
        }
    }
    
    $stmt = $conn->prepare("INSERT INTO machinery (owner_id, machinery_name, rental_price_per_day, description, image) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("isdss", $user_id, $name, $price, $desc, $machinery_image);
    
    if ($stmt->execute()) {
        $_SESSION['msg'] = "Machinery added successfully!";
        $_SESSION['msg_type'] = "success";
    } else {
        $_SESSION['msg'] = "Failed to add machinery.";
        $_SESSION['msg_type'] = "danger";
    }
} elseif ($action === 'rent') {
    $machinery_id = $_POST['machinery_id'];
    // In a real app, you'd create a rental record. 
    // Here we just toggle availability for simplicity.
    $stmt = $conn->prepare("UPDATE machinery SET availability_status = 'Rented' WHERE id = ?");
    $stmt->bind_param("i", $machinery_id);
    
    if ($stmt->execute()) {
        $_SESSION['msg'] = "Machinery rented successfully!";
        $_SESSION['msg_type'] = "success";
    } else {
        $_SESSION['msg'] = "Failed to rent machinery.";
        $_SESSION['msg_type'] = "danger";
    }
}

header("Location: ../farmer/machinery.php");
exit();
?>
