<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['receiver_id'])) {
    http_response_code(401);
    exit('Unauthorized');
}

$user_id = $_SESSION['user_id'];
$receiver_id = intval($_GET['receiver_id']);

$stmt = $conn->prepare("SELECT sender_id, message, created_at FROM messages WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?) ORDER BY created_at ASC");
$stmt->bind_param("iiii", $user_id, $receiver_id, $receiver_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $class = ($row['sender_id'] == $user_id) ? "msg sent" : "msg received";
    $time = date('h:i A', strtotime($row['created_at']));
    echo "<div class='$class'>" . htmlspecialchars($row['message']) . "<span class='msg-time'>$time</span></div>";
}
?>
