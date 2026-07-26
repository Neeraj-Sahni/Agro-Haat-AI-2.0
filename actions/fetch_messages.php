<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['messages' => []]);
    exit();
}

$user_id = $_SESSION['user_id'];
$receiver_id = $_GET['receiver_id'] ?? 0;

$messages = [];
$receiver_name = "";

if ($receiver_id > 0) {
    // Get receiver name
    $u_stmt = $conn->prepare("SELECT fullname FROM users WHERE id = ?");
    $u_stmt->bind_param("i", $receiver_id);
    $u_stmt->execute();
    $receiver_name = $u_stmt->get_result()->fetch_assoc()['fullname'] ?? "User";

    $stmt = $conn->prepare("SELECT * FROM messages WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?) ORDER BY created_at ASC");
    $stmt->bind_param("iiii", $user_id, $receiver_id, $receiver_id, $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $messages[] = $row;
    }
}

echo json_encode(['messages' => $messages, 'receiver_name' => $receiver_name]);
?>
