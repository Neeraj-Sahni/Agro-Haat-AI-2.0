function openModal(modalId) {
    document.getElementById(modalId).style.display = 'flex';
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

window.onclick = function(event) {
    const modals = document.getElementsByClassName('modal');
    for (let i = 0; i < modals.length; i++) {
        if (event.target == modals[i]) {
            modals[i].style.display = "none";
        }
    }
}

function toggleChatbot() {
    const win = document.getElementById('chatbotWindow');
    if (win.style.display === 'none' || win.style.display === '') {
        win.style.display = 'flex';
    } else {
        win.style.display = 'none';
    }
}

function sendBotMessage() {
    const input = document.getElementById('botInput');
    const msg = input.value.trim();
    if (msg === '') return;

    const chatBox = document.getElementById('botMessages');
    
    // User message
    const userDiv = document.createElement('div');
    userDiv.className = 'user-bot-msg';
    userDiv.textContent = msg;
    chatBox.appendChild(userDiv);
    
    input.value = '';
    chatBox.scrollTop = chatBox.scrollHeight;

    // Simulate typing
    const typingDiv = document.createElement('div');
    typingDiv.className = 'bot-msg';
    typingDiv.textContent = 'Typing...';
    typingDiv.id = 'typingIndicator';
    chatBox.appendChild(typingDiv);
    chatBox.scrollTop = chatBox.scrollHeight;

    // Fetch from backend
    const formData = new FormData();
    formData.append('message', msg);
    
    fetch('backend/chatbot_action.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.text())
    .then(reply => {
        const indicator = document.getElementById('typingIndicator');
        if (indicator) indicator.remove();
        
        const botDiv = document.createElement('div');
        botDiv.className = 'bot-msg';
        botDiv.textContent = reply;
        chatBox.appendChild(botDiv);
        chatBox.scrollTop = chatBox.scrollHeight;
    });
}

document.addEventListener('DOMContentLoaded', () => {
    const botInput = document.getElementById('botInput');
    if(botInput) {
        botInput.addEventListener('keypress', function(e) {
            if(e.key === 'Enter') sendBotMessage();
        });
    }
});
