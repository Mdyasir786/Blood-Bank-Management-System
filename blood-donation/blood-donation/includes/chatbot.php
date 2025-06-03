<?php if (isset($_SESSION['user_id'])): ?>
<div id="chatbot-container" style="display:none;">
    <div class="chatbot-header">
        <h4>Blood Bank Assistant</h4>
        <button class="close-chatbot">&times;</button>
    </div>
    <div class="chatbot-messages">
        <div class="bot-msg">Hello! Ask me about blood donation.</div>
    </div>
    <div class="chatbot-input">
        <input type="text" placeholder="Type your question...">
        <button class="send-btn"><i class="fas fa-paper-plane"></i></button>
    </div>
</div>
<button class="chatbot-toggle"><i class="fas fa-robot"></i></button>
<?php endif; ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (!document.querySelector('.chatbot-toggle')) return;
    const toggleBtn = document.querySelector('.chatbot-toggle');
    const container = document.getElementById('chatbot-container');
    const messagesDiv = document.querySelector('.chatbot-messages');
    const input = document.querySelector('.chatbot-input input');
    const sendBtn = document.querySelector('.send-btn');
    const voiceBtn = document.querySelector('.voice-btn');
    const closeBtn = document.querySelector('.close-chatbot');
    toggleBtn.addEventListener('click', () => {
        container.style.display = container.style.display === 'flex' ? 'none' : 'flex';
    });

    closeBtn.addEventListener('click', () => {
        container.style.display = 'none';
    });
    function sendMessage() {
        const message = input.value.trim();
        if (!message) return;
        
        addMessage(message, 'user');
        input.value = '';
        
        fetch('api/chatbot.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ question: message })
        })
        .then(response => response.json())
        .then(data => {
            addMessage(data.response, 'bot');
        });
    }
    function addMessage(text, sender) {
        const msgDiv = document.createElement('div');
        msgDiv.className = `${sender}-msg`;
        msgDiv.textContent = text;
        messagesDiv.appendChild(msgDiv);
        messagesDiv.scrollTop = messagesDiv.scrollHeight;
    }
    sendBtn.addEventListener('click', sendMessage);
    input.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') sendMessage();
    });
});
</script>