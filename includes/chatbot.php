<link rel="stylesheet" href="../public/css/chatbot.css">

<div id="haat-chatbot">
    <!-- Toggle Button -->
    <button id="chatbot-toggle">
        <i class="fas fa-robot"></i>
    </button>

    <!-- Chat Window -->
    <div id="chatbot-window">
        <div id="chatbot-header">
            <div class="d-flex align-items-center">
                <div class="bg-white rounded-circle me-2 d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
                    <i class="fas fa-seedling text-success"></i>
                </div>
                <div>
                    <h6 class="fw-bold mb-0">Agro-Haat Bot</h6>
                    <small class="opacity-75">Online | Smart Farming Assistant</small>
                </div>
            </div>
            <button class="btn btn-link text-white p-0" id="chatbot-close"><i class="fas fa-times"></i></button>
        </div>

        <div id="chatbot-messages">
            <div class="chat-msg bot">
                Hello! 👋 I'm your Agro-Haat assistant. How can I help you with your farm or shopping today?
            </div>
        </div>

        <div id="chatbot-input-area">
            <input type="text" id="chatbot-input" placeholder="Type your question...">
            <button id="chatbot-send">
                <i class="fas fa-paper-plane"></i>
            </button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggle = document.getElementById('chatbot-toggle');
    const close = document.getElementById('chatbot-close');
    const window = document.getElementById('chatbot-window');
    const input = document.getElementById('chatbot-input');
    const send = document.getElementById('chatbot-send');
    const messages = document.getElementById('chatbot-messages');

    toggle.onclick = () => window.classList.toggle('active');
    close.onclick = () => window.classList.remove('active');

    function addMessage(text, type) {
        const div = document.createElement('div');
        div.className = `chat-msg ${type} animate-fade-in-up`;
        div.innerText = text;
        messages.appendChild(div);
        messages.scrollTop = messages.scrollHeight;
    }

    async function getBotResponse(userMsg) {
        // Show typing indicator
        const typing = document.createElement('div');
        typing.className = 'chat-msg bot typing';
        typing.innerHTML = '<div class="dot"></div><div class="dot"></div><div class="dot"></div>';
        messages.appendChild(typing);
        messages.scrollTop = messages.scrollHeight;

        try {
            const response = await fetch('../actions/chatbot_response.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `message=${encodeURIComponent(userMsg)}`
            });
            const data = await response.text();
            typing.remove();
            addMessage(data, 'bot');
        } catch (e) {
            typing.remove();
            addMessage("Sorry, I'm having trouble connecting to the server.", 'bot');
        }
    }

    send.onclick = () => {
        const msg = input.value.trim();
        if (msg) {
            addMessage(msg, 'user');
            input.value = '';
            getBotResponse(msg);
        }
    };

    input.onkeypress = (e) => {
        if (e.key === 'Enter') send.click();
    };
});
</script>
