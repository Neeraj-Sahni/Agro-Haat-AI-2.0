<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$query_text = $_POST['query_text'] ?? '';

if (!empty($query_text)) {
    // Fetch a valid user to act as support (e.g. admin or fallback to first user)
    $admin_query = $conn->query("SELECT id FROM users ORDER BY id ASC LIMIT 1");
    $admin_id = ($admin_query && $admin_query->num_rows > 0) ? $admin_query->fetch_assoc()['id'] : 1;

    $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $user_id, $admin_id, $query_text);
    
    if ($stmt->execute()) {
        $_SESSION['msg'] = "Your query has been posted! An expert will respond shortly.";
        $_SESSION['msg_type'] = "success";
    } else {
        $_SESSION['msg'] = "Failed to post query.";
        $_SESSION['msg_type'] = "danger";
    }
}

header("Location: ../farmer/support.php");
exit();
?>
