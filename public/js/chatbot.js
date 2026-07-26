document.addEventListener("DOMContentLoaded", function() {
    const toggle = document.getElementById("chatbot-toggle");
    const widget = document.getElementById("chatbot-widget");
    const closeBtn = document.getElementById("chatbot-close");
    const sendBtn = document.getElementById("chatbot-send");
    const input = document.getElementById("chatbot-input");
    const body = document.getElementById("chatbot-body");

    if(!toggle) return;

    toggle.addEventListener("click", () => widget.style.display = "flex");
    closeBtn.addEventListener("click", () => widget.style.display = "none");

    function sendMessage() {
        const text = input.value.trim();
        if (!text) return;
        
        // Add User Message
        const userDiv = document.createElement('div');
        userDiv.className = 'chatbot-msg user';
        userDiv.textContent = text;
        body.appendChild(userDiv);
        input.value = '';
        body.scrollTop = body.scrollHeight;

        // Fetch AI Response
        fetch(`../actions/chatbot_response.php?query=${encodeURIComponent(text)}`)
            .then(r => r.text())
            .then(resp => {
                const aiDiv = document.createElement('div');
                aiDiv.className = 'chatbot-msg ai';
                aiDiv.textContent = resp;
                body.appendChild(aiDiv);
                body.scrollTop = body.scrollHeight;
            });
    }

    sendBtn.addEventListener("click", sendMessage);
    input.addEventListener("keypress", (e) => {
        if (e.key === "Enter") sendMessage();
    });
});
