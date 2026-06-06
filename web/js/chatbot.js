/**
 * Chatbot JavaScript
 * Xử lý logic gửi tin nhắn, hiển thị messages, và tương tác người dùng
 */

class Chatbot {
    constructor() {
        this.messagesContainer = document.getElementById('chatbotMessages');
        this.form = document.getElementById('chatbotForm');
        this.input = document.getElementById('messageInput');
        this.sendButton = this.form.querySelector('.send-button');
        this.typingIndicator = document.getElementById('typingIndicator');
        this.minimizeBtn = document.getElementById('minimizeBtn');
        this.resetBtn = document.getElementById('resetBtn');

        this.messageHistory = [];
        this.isLoading = false;
        this.storageKey = 'andChatHistory';
        this.initialMessageShown = false;

        this.loadChatHistory();
        this.initEventListeners();
    }

    initEventListeners() {
        // Gửi tin nhắn khi submit form
        this.form.addEventListener('submit', (e) => this.handleSubmit(e));

        // Gửi tin nhắn bằng Enter (không phải Shift+Enter)
        this.input.addEventListener('keypress', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                this.form.dispatchEvent(new Event('submit'));
            }
        });

        // Minimize chatbot
        if (this.minimizeBtn) {
            this.minimizeBtn.addEventListener('click', () => this.toggleMinimize());
        }

        // Reset chat
        if (this.resetBtn) {
            this.resetBtn.addEventListener('click', () => this.resetChat());
        }

        // Focus vào input khi load
        this.input.focus();
    }

    handleSubmit(e) {
        e.preventDefault();

        const message = this.input.value.trim();
        if (!message || this.isLoading) return;

        // Hiển thị tin nhắn người dùng
        this.displayUserMessage(message);

        // Clear input
        this.input.value = '';

        // Gửi tin nhắn lên server
        this.sendMessage(message);
    }

    displayUserMessage(message) {
        const messageDiv = document.createElement('div');
        messageDiv.className = 'message user-message';
        messageDiv.innerHTML = `<div class="message-content"><p>${this.escapeHtml(message)}</p></div><div class="message-avatar">👤</div>`;

        this.messagesContainer.appendChild(messageDiv);
        this.saveChatHistory();
        this.scrollToBottom();
    }

    displayBotMessage(message) {
        const messageDiv = document.createElement('div');
        messageDiv.className = 'message bot-message';
        messageDiv.innerHTML = `<div class="message-avatar">🤖</div><div class="message-content"><p>${this.escapeHtml(message)}</p></div>`;

        this.messagesContainer.appendChild(messageDiv);
        this.saveChatHistory();
        this.scrollToBottom();
    }

    displayTypingIndicator() {
        const messageDiv = document.createElement('div');
        messageDiv.className = 'message bot-message';
        messageDiv.id = 'typingMessage';
        messageDiv.innerHTML = `<div class="message-avatar">🤖</div><div class="typing-indicator"><span></span><span></span><span></span></div>`;

        this.messagesContainer.appendChild(messageDiv);
        this.scrollToBottom();
    }

    removeTypingIndicator() {
        const typingMessage = document.getElementById('typingMessage');
        if (typingMessage) {
            typingMessage.remove();
        }
    }

    async sendMessage(message) {
        if (this.isLoading) return;

        this.isLoading = true;
        this.setInputDisabled(true);
        this.displayTypingIndicator();

        try {
            const response = await fetch(window.chatbotConfig.sendMessageUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-CSRF-Token': window.chatbotConfig.csrfToken,
                },
                body: new URLSearchParams({
                    message: message,
                }),
            });

            const data = await response.json();

            this.removeTypingIndicator();

            if (data.success) {
                this.displayBotMessage(data.reply);
            } else {
                this.displayBotMessage('Xin lỗi, có lỗi xảy ra: ' + (data.error || 'Không xác định được lỗi'));
            }
        } catch (error) {
            console.error('Chatbot error:', error);
            this.removeTypingIndicator();
            this.displayBotMessage('Xin lỗi, không thể kết nối tới server. Vui lòng thử lại.');
        } finally {
            this.isLoading = false;
            this.setInputDisabled(false);
            this.input.focus();
        }
    }

    scrollToBottom() {
        setTimeout(() => {
            this.messagesContainer.scrollTop = this.messagesContainer.scrollHeight;
        }, 0);
    }

    setInputDisabled(disabled) {
        this.input.disabled = disabled;
        this.sendButton.disabled = disabled;
    }

    toggleMinimize() {
        const container = document.querySelector('.chatbot-container');
        const messagesArea = document.querySelector('.chatbot-messages');
        const inputArea = document.querySelector('.chatbot-input-area');

        container.classList.toggle('minimized');

        if (container.classList.contains('minimized')) {
            messagesArea.style.display = 'none';
            inputArea.style.display = 'none';
            this.minimizeBtn.textContent = '+';
        } else {
            messagesArea.style.display = 'flex';
            inputArea.style.display = 'block';
            this.minimizeBtn.textContent = '−';
            this.scrollToBottom();
        }
    }

    saveChatHistory() {
        const messages = [];
        document.querySelectorAll('.message').forEach(msgEl => {
            const isUser = msgEl.classList.contains('user-message');
            const content = msgEl.querySelector('.message-content p')?.textContent;
            if (content) {
                messages.push({ type: isUser ? 'user' : 'bot', content: content });
            }
        });
        localStorage.setItem(this.storageKey, JSON.stringify(messages));
    }

    loadChatHistory() {
        const saved = localStorage.getItem(this.storageKey);
        if (saved) {
            try {
                const messages = JSON.parse(saved);
                messages.forEach(msg => {
                    if (msg.type === 'user') {
                        this.displayUserMessageDirect(msg.content);
                    } else {
                        this.displayBotMessageDirect(msg.content);
                    }
                });
            } catch (e) {
                console.error('Error loading chat history:', e);
                this.showInitialMessage();
            }
        } else {
            this.showInitialMessage();
        }
    }

    displayUserMessageDirect(message) {
        const messageDiv = document.createElement('div');
        messageDiv.className = 'message user-message';
        messageDiv.innerHTML = `<div class="message-content"><p>${this.escapeHtml(message)}</p></div><div class="message-avatar">👤</div>`;
        this.messagesContainer.appendChild(messageDiv);
    }

    displayBotMessageDirect(message) {
        const messageDiv = document.createElement('div');
        messageDiv.className = 'message bot-message';
        messageDiv.innerHTML = `<div class="message-avatar">🤖</div><div class="message-content"><p>${this.escapeHtml(message)}</p></div>`;
        this.messagesContainer.appendChild(messageDiv);
    }

    showInitialMessage() {
        const messageDiv = document.createElement('div');
        messageDiv.className = 'message bot-message';
        messageDiv.innerHTML = `<div class="message-avatar">🤖</div><div class="message-content"><p>Xin chào! 👋 Tôi là trợ lý hỗ trợ của ANDI. Tôi sẽ giúp bạn hiểu rõ về các loại thẻ, tính năng của ANDI, mẹo học từ vựng hiệu quả, và cách đăng bài blog. Hãy hỏi tôi bất cứ điều gì bạn muốn biết!</p></div>`;
        this.messagesContainer.appendChild(messageDiv);
    }

    resetChat() {
        if (confirm('Bạn có chắc chắn muốn xóa tất cả nội dung chat?')) {
            this.messagesContainer.innerHTML = '';
            localStorage.removeItem(this.storageKey);
            this.showInitialMessage();
        }
    }

    escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;',
        };
        return text.replace(/[&<>"']/g, (m) => map[m]);
    }
}

// Khởi tạo chatbot khi DOM sẵn sàng
document.addEventListener('DOMContentLoaded', () => {
    window.chatbot = new Chatbot();
});
