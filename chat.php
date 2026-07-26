<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$current_user_id = $_SESSION['user_id'];
$other_user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

if ($other_user_id === 0 || $current_user_id === $other_user_id) {
    die("Invalid user to chat with.");
}

// Fetch other user details
$stmt = $conn->prepare("SELECT fullname, role, image FROM users WHERE id = ?");
$stmt->bind_param("i", $other_user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    die("User not found.");
}
$other_user = $result->fetch_assoc();

// Dashboard link based on role
$dashboard_link = ($_SESSION['role'] === 'Farmer') ? 'farmer/dashboard.php' : 'customer/dashboard.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat with <?= htmlspecialchars($other_user['fullname']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f4f7f6; font-family: 'Inter', sans-serif; }
        .chat-container { max-width: 800px; margin: 2rem auto; background: #fff; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); display: flex; flex-direction: column; height: 80vh; }
        .chat-header { background: #2e7d32; color: white; padding: 15px; border-radius: 12px 12px 0 0; display: flex; align-items: center; }
        .chat-header img { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; margin-right: 15px; }
        .chat-messages { flex: 1; padding: 20px; overflow-y: auto; background: #e5ddd5; }
        .message { margin-bottom: 15px; max-width: 70%; clear: both; }
        .message-incoming { float: left; background: #fff; padding: 10px 15px; border-radius: 0 15px 15px 15px; box-shadow: 0 1px 1px rgba(0,0,0,0.1); }
        .message-outgoing { float: right; background: #dcf8c6; padding: 10px 15px; border-radius: 15px 0 15px 15px; box-shadow: 0 1px 1px rgba(0,0,0,0.1); }
        .chat-input { padding: 15px; background: #f0f0f0; border-radius: 0 0 12px 12px; display: flex; gap: 10px; }
        .chat-input input { flex: 1; border-radius: 20px; border: 1px solid #ccc; padding: 10px 15px; outline: none; }
        .chat-input button { border-radius: 50%; width: 45px; height: 45px; background: #2e7d32; color: white; border: none; display: flex; align-items: center; justify-content: center; }
    </style>
</head>
<body>
    <div class="container">
        <a href="<?= $dashboard_link ?>" class="btn btn-secondary mt-3"><i class="fas fa-arrow-left me-2"></i>Back to Dashboard</a>
        <div class="chat-container">
            <div class="chat-header">
                <img src="public/uploads/profile_pictures/<?= htmlspecialchars($other_user['image'] ?? 'default.jpg') ?>" alt="Profile">
                <div>
                    <h5 class="mb-0"><?= htmlspecialchars($other_user['fullname']) ?></h5>
                    <small><?= htmlspecialchars($other_user['role']) ?></small>
                </div>
            </div>
            <div class="chat-messages" id="chat-box">
                <!-- Messages will be loaded here via AJAX -->
            </div>
            <div class="chat-input">
                <input type="text" id="message-input" placeholder="Type a message..." onkeypress="handleKeyPress(event)">
                <button onclick="sendMessage()"><i class="fas fa-paper-plane"></i></button>
            </div>
        </div>
    </div>

    <script>
        const currentUserId = <?= $current_user_id ?>;
        const otherUserId = <?= $other_user_id ?>;
        const chatBox = document.getElementById('chat-box');
        const messageInput = document.getElementById('message-input');
        let autoScroll = true;

        chatBox.addEventListener('scroll', () => {
            if (chatBox.scrollTop + chatBox.clientHeight >= chatBox.scrollHeight - 10) {
                autoScroll = true;
            } else {
                autoScroll = false;
            }
        });

        function fetchMessages() {
            fetch(`actions/fetch_messages.php?receiver_id=${otherUserId}`)
                .then(response => response.json())
                .then(data => {
                    let html = '';
                    const messagesList = data.messages || [];
                    messagesList.forEach(msg => {
                        const isOutgoing = msg.sender_id == currentUserId;
                        const msgClass = isOutgoing ? 'message-outgoing' : 'message-incoming';
                        html += `<div class="message ${msgClass}">${msg.message}</div>`;
                    });
                    chatBox.innerHTML = html;
                    if(autoScroll) {
                        chatBox.scrollTop = chatBox.scrollHeight;
                    }
                });
        }

        function sendMessage() {
            const text = messageInput.value.trim();
            if(!text) return;
            
            fetch('actions/send_message_action.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `receiver_id=${otherUserId}&message=${encodeURIComponent(text)}`
            }).then(() => {
                messageInput.value = '';
                autoScroll = true;
                fetchMessages();
            });
        }

        function handleKeyPress(e) {
            if(e.key === 'Enter') {
                sendMessage();
            }
        }

        // Poll every 2 seconds
        setInterval(fetchMessages, 2000);
        fetchMessages();
    </script>
</body>
</html>
