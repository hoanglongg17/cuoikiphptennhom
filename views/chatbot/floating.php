<?php
use yii\helpers\Url;

// Kiểm tra nếu ở trang study-deck thì không hiển thị
$currentAction = Yii::$app->controller->action->id ?? '';
$currentController = Yii::$app->controller->id ?? '';

// Không hiển thị floating chatbot ở trang study-deck
if ($currentAction === 'study-deck') {
    return;
}

// Chỉ hiển thị khi user đã đăng nhập
if (Yii::$app->user->isGuest) {
    return;
}
?>

<style>
    /* Floating Chatbot Button */
    .floating-chatbot-btn {
        position: fixed;
        bottom: 30px;
        right: 30px;
        width: 60px;
        height: 60px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        border-radius: 50%;
        cursor: pointer;
        box-shadow: 0 4px 20px rgba(102, 126, 234, 0.4);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
        z-index: 999;
        transition: all 0.3s ease;
        animation: pulse 2s infinite;
    }

    .floating-chatbot-btn:hover {
        transform: scale(1.1);
        box-shadow: 0 6px 30px rgba(102, 126, 234, 0.6);
        animation: none;
    }

    .floating-chatbot-btn:active {
        transform: scale(0.95);
    }

    @keyframes pulse {
        0%, 100% {
            box-shadow: 0 4px 20px rgba(102, 126, 234, 0.4);
        }
        50% {
            box-shadow: 0 4px 30px rgba(102, 126, 234, 0.7);
        }
    }

    /* Floating Chat Dialog */
    .floating-chatbot-dialog {
        position: fixed;
        bottom: 100px;
        right: 30px;
        width: 380px;
        height: 500px;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 8px 40px rgba(0, 0, 0, 0.15);
        display: none;
        flex-direction: column;
        z-index: 998;
        animation: slideUp 0.3s ease;
        overflow: hidden;
    }

    .floating-chatbot-dialog.active {
        display: flex;
    }

    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Header */
    .floating-chatbot-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: #fff;
        padding: 15px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        min-height: 60px;
    }

    .floating-chatbot-header h3 {
        margin: 0;
        font-size: 16px;
        font-weight: 600;
    }

    .floating-chatbot-header .btn-close {
        background: none;
        border: none;
        color: #fff;
        font-size: 24px;
        cursor: pointer;
        padding: 0;
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: 0.2s;
    }

    .floating-chatbot-header .btn-close:hover {
        transform: scale(1.2);
    }

    /* Chat Container */
    .floating-chatbot-messages {
        flex: 1;
        overflow-y: auto;
        padding: 15px;
        background: #f8f9fa;
    }

    /* Input Area */
    .floating-chatbot-input-area {
        padding: 15px;
        border-top: 1px solid #e0e0e0;
        background: #fff;
        display: flex;
        gap: 8px;
        align-items: center;
    }

    .floating-chatbot-input-area input {
        flex: 1;
        border: 1px solid #ddd;
        border-radius: 20px;
        padding: 10px 15px;
        font-size: 14px;
        outline: none;
        transition: 0.2s;
    }

    .floating-chatbot-input-area input:focus {
        border-color: #667eea;
        box-shadow: 0 0 8px rgba(102, 126, 234, 0.2);
    }

    .floating-chatbot-input-area button {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #667eea;
        color: #fff;
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: 0.2s;
    }

    .floating-chatbot-input-area button:hover {
        background: #764ba2;
    }

    /* Reset Button */
    .floating-chatbot-reset {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: #ff6b6b;
        color: #fff;
        border: none;
        padding: 8px 16px;
        border-radius: 20px;
        cursor: pointer;
        font-size: 12px;
        z-index: 10;
        transition: 0.2s;
    }

    .floating-chatbot-reset:hover {
        background: #ff5252;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .floating-chatbot-btn {
            width: 55px;
            height: 55px;
            bottom: 20px;
            right: 20px;
            font-size: 24px;
        }

        .floating-chatbot-dialog {
            width: calc(100% - 40px);
            height: 400px;
            bottom: 80px;
            right: 20px;
            left: 20px;
        }
    }

    @media (max-width: 480px) {
        .floating-chatbot-dialog {
            width: 100%;
            height: 100vh;
            bottom: 0;
            right: 0;
            left: 0;
            border-radius: 0;
            max-height: 100vh;
        }
    }

    /* Message Styles */
    .message {
        margin-bottom: 10px;
        display: flex;
        animation: fadeIn 0.3s ease;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .message.user {
        justify-content: flex-end;
    }

    .message.bot {
        justify-content: flex-start;
    }

    .message-content {
        max-width: 70%;
        padding: 10px 12px;
        border-radius: 12px;
        font-size: 13px;
        word-wrap: break-word;
        line-height: 1.4;
    }

    .message.user .message-content {
        background: #667eea;
        color: #fff;
        border-bottom-right-radius: 3px;
    }

    .message.bot .message-content {
        background: #e0e0e0;
        color: #333;
        border-bottom-left-radius: 3px;
    }

    .typing-indicator {
        display: flex;
        gap: 4px;
        padding: 10px 12px;
        background: #e0e0e0;
        border-radius: 12px;
        width: fit-content;
    }

    .typing-indicator span {
        width: 8px;
        height: 8px;
        background: #999;
        border-radius: 50%;
        animation: typing 1.4s infinite;
    }

    .typing-indicator span:nth-child(2) {
        animation-delay: 0.2s;
    }

    .typing-indicator span:nth-child(3) {
        animation-delay: 0.4s;
    }

    @keyframes typing {
        0%, 60%, 100% { opacity: 0.3; transform: translateY(0); }
        30% { opacity: 1; transform: translateY(-10px); }
    }
</style>

<button class="floating-chatbot-btn" id="floatingChatbotBtn" title="Mở Chatbot">💬</button>

<div class="floating-chatbot-dialog" id="floatingChatbotDialog">
    <div class="floating-chatbot-header">
        <h3>Trợ lý Andi</h3>
        <button class="btn-close" id="floatingChatbotClose">&times;</button>
    </div>
    
    <div class="floating-chatbot-messages" id="floatingChatbotMessages"></div>
    
    <div class="floating-chatbot-input-area">
        <input 
            type="text" 
            id="floatingChatbotInput" 
            placeholder="Nhập câu hỏi của bạn..." 
            autocomplete="off"
        >
        <button id="floatingChatbotSend" title="Gửi">➤</button>
        <button id="floatingChatbotReset" class="floating-chatbot-reset" style="position: relative; top: auto; left: auto; transform: none; display: none; width: 30px; height: 30px; padding: 0; font-size: 14px;" title="Làm mới">↻</button>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const btn = document.getElementById('floatingChatbotBtn');
    const dialog = document.getElementById('floatingChatbotDialog');
    const closeBtn = document.getElementById('floatingChatbotClose');
    const input = document.getElementById('floatingChatbotInput');
    const sendBtn = document.getElementById('floatingChatbotSend');
    const resetBtn = document.getElementById('floatingChatbotReset');
    const messagesContainer = document.getElementById('floatingChatbotMessages');

    const STORAGE_KEY = 'chatbot_messages_<?= Yii::$app->user->identity->userid ?? "guest" ?>';

    // Load messages from sessionStorage
    function loadMessagesFromStorage() {
        const stored = sessionStorage.getItem(STORAGE_KEY);
        if (stored) {
            try {
                const messages = JSON.parse(stored);
                messages.forEach(msg => {
                    const div = document.createElement('div');
                    div.className = `message ${msg.type}`;
                    div.innerHTML = `<div class="message-content">${msg.content}</div>`;
                    messagesContainer.appendChild(div);
                });
                if (messages.length > 0) {
                    resetBtn.style.display = 'inline-block';
                }
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            } catch (e) {
                console.error('Error loading messages:', e);
                addBotMessage('Xin chào! Tôi là Andi. Bạn cần hỗ trợ gì không?');
            }
        } else {
            addBotMessage('Xin chào! Tôi là Andi. Bạn cần hỗ trợ gì không?');
        }
    }

    // Save message to sessionStorage
    function saveMessage(type, content) {
        try {
            const stored = sessionStorage.getItem(STORAGE_KEY) || '[]';
            const messages = JSON.parse(stored);
            messages.push({ type: type, content: content });
            sessionStorage.setItem(STORAGE_KEY, JSON.stringify(messages));
        } catch (e) {
            console.error('Error saving message:', e);
        }
    }

    // Clear stored messages
    function clearStoredMessages() {
        sessionStorage.removeItem(STORAGE_KEY);
    }

    // Toggle dialog
    btn.addEventListener('click', function() {
        dialog.classList.toggle('active');
        if (dialog.classList.contains('active')) {
            input.focus();
        }
    });

    closeBtn.addEventListener('click', function() {
        dialog.classList.remove('active');
    });

    // Reset conversation
    function resetChat() {
        messagesContainer.innerHTML = '';
        input.value = '';
        resetBtn.style.display = 'none';
        clearStoredMessages();
        addBotMessage('Xin chào! Tôi là Andi. Bạn cần hỗ trợ gì không?');
        input.focus();
    }

    resetBtn.addEventListener('click', resetChat);

    // Send message
    function sendMessage() {
        const message = input.value.trim();
        if (!message) return;

        // Add user message
        addUserMessage(message);
        input.value = '';
        resetBtn.style.display = 'inline-block';

        // Show typing indicator
        addTypingIndicator();

        // Get CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                         document.querySelector('input[name="_csrf"]')?.value || '';

        // Prepare form data
        const formData = new FormData();
        formData.append('message', message);
        if (csrfToken) {
            formData.append('_csrf', csrfToken);
        }

        // Send to server
        fetch('<?= Url::to(['/chatbot/send-message']) ?>', {
            method: 'POST',
            headers: {
                'X-CSRF-Token': csrfToken
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            removeTypingIndicator();
            if (data.success) {
                addBotMessage(data.reply);
            } else {
                addBotMessage('Xin lỗi, có lỗi xảy ra: ' + (data.error || 'Unknown error'));
            }
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        })
        .catch(error => {
            removeTypingIndicator();
            addBotMessage('Xin lỗi, có lỗi xảy ra khi gửi tin nhắn.');
            console.error('Error:', error);
        });
    }

    sendBtn.addEventListener('click', sendMessage);
    input.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            sendMessage();
        }
    });

    // Helper functions
    function addUserMessage(text) {
        const div = document.createElement('div');
        div.className = 'message user';
        div.innerHTML = `<div class="message-content">${escapeHtml(text)}</div>`;
        messagesContainer.appendChild(div);
        saveMessage('user', escapeHtml(text));
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    function addBotMessage(text) {
        const div = document.createElement('div');
        div.className = 'message bot';
        div.innerHTML = `<div class="message-content">${escapeHtml(text)}</div>`;
        messagesContainer.appendChild(div);
        saveMessage('bot', escapeHtml(text));
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    function addTypingIndicator() {
        const div = document.createElement('div');
        div.className = 'message bot';
        div.innerHTML = `<div class="typing-indicator"><span></span><span></span><span></span></div>`;
        div.id = 'typingIndicator';
        messagesContainer.appendChild(div);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    function removeTypingIndicator() {
        const indicator = document.getElementById('typingIndicator');
        if (indicator) {
            indicator.remove();
        }
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Initialize with stored messages or greeting
    loadMessagesFromStorage();
});
</script>
