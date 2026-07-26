<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Customer') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$plan_id = $_POST['plan_id'] ?? 0;

if ($plan_id > 0) {
    // Active subscription check karo — expiry bhi check karo
    $check = $conn->prepare("SELECT id, expiry_date FROM customer_subscriptions WHERE customer_id = ? AND status = 'Active'");
    $check->bind_param("i", $user_id);
    $check->execute();
    $active_sub = $check->get_result()->fetch_assoc();

    // Agar active hai to expiry check karo
    if ($active_sub) {
        $today = date('Y-m-d');
        if ($active_sub['expiry_date'] >= $today) {
            // Abhi bhi active hai
            $_SESSION['msg'] = "You already have an active subscription until " . date('d M, Y', strtotime($active_sub['expiry_date'])) . "!";
            $_SESSION['msg_type'] = "warning";
        } else {
            // Expire ho gayi — status update karo
            $expire_stmt = $conn->prepare("UPDATE customer_subscriptions SET status = 'Expired' WHERE id = ?");
            $expire_stmt->bind_param("i", $active_sub['id']);
            $expire_stmt->execute();

            // Naya subscribe karo
            $plan_stmt = $conn->prepare("SELECT duration_days FROM subscription_plans WHERE id = ?");
            $plan_stmt->bind_param("i", $plan_id);
            $plan_stmt->execute();
            $plan = $plan_stmt->get_result()->fetch_assoc();

            $expiry_date = date('Y-m-d', strtotime('+' . $plan['duration_days'] . ' days'));
            $stmt = $conn->prepare("INSERT INTO customer_subscriptions (customer_id, plan_id, status, expiry_date) VALUES (?, ?, 'Active', ?)");
            $stmt->bind_param("iis", $user_id, $plan_id, $expiry_date);

            if ($stmt->execute()) {
                $_SESSION['msg'] = "Re-subscribed successfully! Welcome back.";
                $_SESSION['msg_type'] = "success";
            } else {
                $_SESSION['msg'] = "Subscription failed. Please try again.";
                $_SESSION['msg_type'] = "danger";
            }
        }
    } else {
        // Koi active subscription nahi — naya subscribe karo
        $plan_stmt = $conn->prepare("SELECT duration_days FROM subscription_plans WHERE id = ?");
        $plan_stmt->bind_param("i", $plan_id);
        $plan_stmt->execute();
        $plan = $plan_stmt->get_result()->fetch_assoc();

        $expiry_date = date('Y-m-d', strtotime('+' . $plan['duration_days'] . ' days'));
        $stmt = $conn->prepare("INSERT INTO customer_subscriptions (customer_id, plan_id, status, expiry_date) VALUES (?, ?, 'Active', ?)");
        $stmt->bind_param("iis", $user_id, $plan_id, $expiry_date);

        if ($stmt->execute()) {
            $_SESSION['msg'] = "Subscribed successfully! Welcome to the fresh club.";
            $_SESSION['msg_type'] = "success";
        } else {
            $_SESSION['msg'] = "Subscription failed. Please try again.";
            $_SESSION['msg_type'] = "danger";
        }
    }
}

header("Location: ../customer/delivery_plans.php");
exit();
?>