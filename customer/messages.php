<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$receiver_id = $_GET['receiver_id'] ?? 0;

$page_title = "Messages";
include '../includes/header.inc.php';

if ($_SESSION['role'] === 'Farmer') {
    include '../includes/navbar_farmer.inc.php';
} else {
    include '../includes/navbar_customer.inc.php';
}
?>

<div class="container mt-5 mb-5">
    <div class="row g-0 glass-card overflow-hidden animate-fade-in-up" style="height: 650px;">
        <!-- Sidebar -->
        <div class="col-md-4 border-end bg-light d-flex flex-direction-column">
            <div class="p-4 border-bottom w-100 bg-white">
                <h5 class="fw-bold mb-0">Messages</h5>
            </div>
            <div id="contact-list" class="overflow-auto flex-grow-1 w-100">
                <!-- Contacts will be loaded here -->
                <div class="p-5 text-center text-muted">
                    <div class="spinner-border spinner-border-sm" role="status"></div>
                </div>
            </div>
        </div>

        <!-- Chat Area -->
        <div class="col-md-8 d-flex flex-column bg-white">
            <?php if ($receiver_id > 0): ?>
                <div id="chat-header" class="p-3 border-bottom d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <div class="avatar-sm me-3 bg-success rounded-circle text-white d-flex align-items-center justify-content-center fw-bold" style="width: 40px; height: 40px;">U</div>
                        <h6 class="fw-bold mb-0" id="current-chat-name">Loading...</h6>
                    </div>
                    <div>
                        <button class="btn btn-link text-muted"><i class="fas fa-ellipsis-v"></i></button>
                    </div>
                </div>
                <div id="chat-messages" class="flex-grow-1 overflow-auto p-4 d-flex flex-column gap-3" style="background: #fdfdfd;">
                    <!-- Messages will be loaded here -->
                </div>
                <div class="p-4 border-top">
                    <form id="chat-form" class="d-flex gap-2">
                        <input type="hidden" id="receiver_id" value="<?= $receiver_id ?>">
                        <input type="text" id="message-input" class="form-control rounded-pill px-4 border-0 bg-light" placeholder="Type a message..." required>
                        <button type="submit" class="btn btn-premium btn-premium-blue rounded-circle shadow" style="width: 45px; height: 45px;">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </form>
                </div>
            <?php else: ?>
                <div class="h-100 d-flex flex-column align-items-center justify-content-center text-muted p-5 text-center">
                    <i class="fas fa-comments fa-4x mb-4 opacity-25"></i>
                    <h5 class="fw-bold">Select a contact to start chatting</h5>
                    <p class="small">Connect with farmers or customers directly for personalized service.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const receiverId = document.getElementById('receiver_id')?.value;
    const msgContainer = document.getElementById('chat-messages');
    const contactList = document.getElementById('contact-list');
    const chatForm = document.getElementById('chat-form');

    // Load Contact List
    async function loadContacts() {
        try {
            const res = await fetch('../actions/fetch_contacts.php');
            const data = await res.json();
            contactList.innerHTML = data.map(c => `
                <a href="?receiver_id=${c.id}" class="d-flex align-items-center p-3 text-decoration-none border-bottom hover-bg-light ${receiverId == c.id ? 'bg-white shadow-sm' : ''}">
                    <div class="bg-success rounded-circle text-white d-flex align-items-center justify-content-center fw-bold me-3" style="width: 45px; height: 45px; min-width: 45px;">${c.fullname[0]}</div>
                    <div class="w-100">
                        <div class="d-flex justify-content-between">
                            <h6 class="fw-bold mb-0 text-dark small">${c.fullname}</h6>
                            <small class="text-muted smaller">
                                ${c.last_time ? new Date(c.last_time).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) : ''}
                            </small>
                        </div>
                        <p class="text-muted small mb-0 text-truncate" style="max-width: 150px;">${c.last_msg || 'No messages yet'}</p>
                    </div>
                </a>
            `).join('');
        } catch (e) { contactList.innerHTML = '<p class="p-4 text-center text-muted">Error loading contacts</p>'; }
    }

    // Load Messages
    async function loadMessages() {
        if (!receiverId) return;
        try {
            const res = await fetch(`../actions/fetch_messages.php?receiver_id=${receiverId}`);
            const data = await res.json();
            if(data.receiver_name) document.getElementById('current-chat-name').innerText = data.receiver_name;
            msgContainer.innerHTML = data.messages.map(m => `
                <div class="${m.sender_id == <?= $user_id ?> ? 'align-self-end text-white bg-primary' : 'align-self-start bg-light text-dark'} p-3 rounded-4 shadow-sm" style="max-width: 75%;">
                    <p class="mb-1 small">${m.message}</p>
                    <small class="smaller opacity-75 d-block text-end">${new Date(m.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</small>
                </div>
            `).join('');
            msgContainer.scrollTop = msgContainer.scrollHeight;
        } catch (e) {}
    }

    if (chatForm) {
        chatForm.onsubmit = async (e) => {
            e.preventDefault();
            const input = document.getElementById('message-input');
            const msg = input.value.trim();
            if (!msg) return;

            try {
                await fetch('../actions/send_message_action.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `receiver_id=${receiverId}&message=${encodeURIComponent(msg)}`
                });
                input.value = '';
                loadMessages();
            } catch (e) {}
        };
    }

    loadContacts();
    if(receiverId) {
        loadMessages();
        setInterval(loadMessages, 3000); // Polling for real-time feel
    }
});
</script>

<style>
.hover-bg-light:hover { background-color: #f8fafc; }
.smaller { font-size: 0.75rem; }
</style>

<?php include '../includes/footer.inc.php'; ?>
