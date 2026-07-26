<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Farmer') {
    header("Location: ../login.php");
    exit();
}

$order_id = $_GET['id'] ?? 0;
$new_status = $_GET['status'] ?? '';

$valid_statuses = ['Confirmed', 'Shipped', 'Delivered', 'Cancelled'];

if ($order_id > 0 && in_array($new_status, $valid_statuses)) {
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ? AND farmer_id = ?");
    $stmt->bind_param("sii", $new_status, $order_id, $_SESSION['user_id']);
    
    if ($stmt->execute()) {
        $_SESSION['msg'] = "Order #$order_id updated to $new_status.";
        $_SESSION['msg_type'] = "success";
    } else {
        $_SESSION['msg'] = "Failed to update order status.";
        $_SESSION['msg_type'] = "danger";
    }
}

header("Location: ../farmer/view_orders.php");
exit();
?>
