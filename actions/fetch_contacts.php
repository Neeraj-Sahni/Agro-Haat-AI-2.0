<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode([]);
    exit();
}

$user_id = $_SESSION['user_id'];
$role    = $_SESSION['role'];

if ($role === 'Farmer') {
    // Farmers ko wo customers dikhao jo message kar chuke hain
    $query = "SELECT u.id, u.fullname, u.image,
                (SELECT m.message FROM messages m 
                 WHERE (m.sender_id = u.id AND m.receiver_id = $user_id) 
                 OR (m.sender_id = $user_id AND m.receiver_id = u.id) 
                 ORDER BY m.created_at DESC LIMIT 1) as last_msg,
                (SELECT m.created_at FROM messages m 
                 WHERE (m.sender_id = u.id AND m.receiver_id = $user_id) 
                 OR (m.sender_id = $user_id AND m.receiver_id = u.id) 
                 ORDER BY m.created_at DESC LIMIT 1) as last_time
              FROM users u 
              JOIN messages m ON (u.id = m.sender_id OR u.id = m.receiver_id) 
              WHERE (m.sender_id = $user_id OR m.receiver_id = $user_id) 
              AND u.id != $user_id
              GROUP BY u.id
              ORDER BY last_time DESC";
} else {
    // Customers ko saare farmers dikhao with last message
    $query = "SELECT u.id, u.fullname, u.image,
                (SELECT m.message FROM messages m 
                 WHERE (m.sender_id = u.id AND m.receiver_id = $user_id) 
                 OR (m.sender_id = $user_id AND m.receiver_id = u.id) 
                 ORDER BY m.created_at DESC LIMIT 1) as last_msg,
                (SELECT m.created_at FROM messages m 
                 WHERE (m.sender_id = u.id AND m.receiver_id = $user_id) 
                 OR (m.sender_id = $user_id AND m.receiver_id = u.id) 
                 ORDER BY m.created_at DESC LIMIT 1) as last_time
              FROM users u 
              WHERE u.role = 'Farmer'
              ORDER BY last_time DESC, u.fullname ASC";
}

$res     = $conn->query($query);
$contacts = [];
while ($row = $res->fetch_assoc()) {
    $contacts[] = [
        'id'       => $row['id'],
        'fullname' => $row['fullname'],
        'image'    => $row['image'],
        'last_msg' => $row['last_msg'] ?? null,
        'last_time'=> $row['last_time'] ?? null
    ];
}

header('Content-Type: application/json');
echo json_encode($contacts);
?>