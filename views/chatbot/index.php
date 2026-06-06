<?php
use yii\helpers\Url;
use yii\web\View;

$this->title = 'Chatbot Hỗ Trợ ANDI';
$this->registerCssFile('@web/css/chatbot.css', ['depends' => 'yii\web\YiiAsset']);
$this->registerJsFile('@web/js/chatbot.js', ['depends' => 'yii\web\YiiAsset', 'position' => View::POS_END]);
?>

<div class="chatbot-container">
    <div class="chatbot-header">
        <div class="chatbot-header-content">
            <h2 class="chatbot-title">💬 Trợ Lý ANDI</h2>
            <p class="chatbot-subtitle">Hỗ trợ học từ vựng và khám phá ANDI</p>
        </div>
        <button class="chatbot-reset" id="resetBtn" title="Reset chat">🔄</button>
        <button class="chatbot-minimize" id="minimizeBtn" title="Thu nhỏ">−</button>
    </div>

    <div class="chatbot-messages" id="chatbotMessages"></div>

    <div class="chatbot-input-area">
        <form id="chatbotForm" class="chatbot-form">
            <div class="input-wrapper">
                <input 
                    type="text" 
                    id="messageInput" 
                    class="chatbot-input" 
                    placeholder="Hỏi tôi về ANDI, từ vựng, hay bộ thẻ..." 
                    autocomplete="off"
                />
                <button type="submit" class="send-button" title="Gửi">
                    <span class="send-icon">▶</span>
                </button>
            </div>
            <div class="typing-indicator" id="typingIndicator" style="display: none;">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </form>
    </div>
</div>

<script>
// Khởi tạo configuration cho chatbot JavaScript
window.chatbotConfig = {
    sendMessageUrl: '<?php echo Url::to(['/chatbot/send-message']); ?>',
    csrfToken: '<?php echo Yii::$app->request->getCsrfToken(); ?>',
};
</script>
