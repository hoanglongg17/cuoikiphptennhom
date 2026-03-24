<?php
/** @var yii\web\View $this */
/** @var app\models\Deck $deck */
/** @var app\models\Card $currentCard */
/** @var int $cardIndex */
/** @var int $totalCards */
/** @var array $priorityQueue */

use yii\helpers\Url;
use yii\helpers\Html;
use app\helpers\SM2Helper;

$this->title = 'Học - ' . $deck->name . ' - Andi';

$this->registerCssFile('@web/css/dashboard.css', ['depends' => [\app\assets\AppAsset::class]]);
$this->registerCssFile('@web/css/study-deck.css', ['depends' => [\app\assets\AppAsset::class]]);
?>

<div class="study-deck-container">
    <!-- Header -->
    <div class="study-header">
        <div class="study-deck-name">
            <a href="<?= Url::to(['site/practice']) ?>" class="back-btn">← Quay lại</a>
            <h1><?= Html::encode($deck->name) ?></h1>
        </div>
        <div class="study-progress">
            <span class="progress-text">Thẻ <?= $cardIndex ?> / <?= $totalCards ?></span>
            <div class="progress-bar">
                <div class="progress-fill" style="width: <?= ($cardIndex / $totalCards * 100) ?>%"></div>
            </div>
        </div>
    </div>

    <!-- Flashcard -->
    <div class="study-content">
        <div id="flashcard" class="flashcard" data-flipped="false" data-cardid="<?= $currentCard->cardid ?>">
            <div class="card-inner">
                <div class="card-front">
                    <div class="card-label">Mặt trước</div>
                    <div class="card-text" id="cardFrontText"><?= nl2br(Html::encode($currentCard->frontcontent)) ?></div>
                    <?php if ($currentCard->pronunciation): ?>
                        <div class="card-pronunciation" id="cardPronunciation">/ <?= Html::encode($currentCard->pronunciation) ?> /</div>
                    <?php else: ?>
                        <div class="card-pronunciation" id="cardPronunciation" style="display: none;"></div>
                    <?php endif; ?>
                </div>
                <div class="card-back">
                    <div class="card-label">Mặt sau</div>
                    <div class="card-text" id="cardBackText"><?= nl2br(Html::encode($currentCard->backcontent)) ?></div>
                    <?php if ($currentCard->examplesentence): ?>
                        <div class="card-example" id="cardExample">
                            <strong>Ví dụ:</strong> <em><?= nl2br(Html::encode($currentCard->examplesentence)) ?></em>
                        </div>
                    <?php else: ?>
                        <div class="card-example" id="cardExample" style="display: none;"></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Control Buttons -->
        <div class="study-controls">
            <!-- Nút Tiếp theo (hiện khi chưa lật) -->
            <div id="nextButtonContainer" class="next-button-container">
                <button class="btn-next" onclick="flipCard()">
                    Tiếp theo →
                </button>
            </div>

            <!-- Nút đánh giá (hiện sau khi lật) -->
            <div id="gradeButtonContainer" class="grade-buttons-container" style="display: none;">
                <p class="flip-hint" style="margin-bottom: 20px;">Chọn độ khó</p>
                <div class="grade-buttons">
                    <button class="btn-grade grade-1" onclick="gradeCard(1, 'Again')">
                        <span class="grade-label">Again</span>
                        <span class="grade-desc">Quên</span>
                    </button>
                    <button class="btn-grade grade-2" onclick="gradeCard(2, 'Hard')">
                        <span class="grade-label">Hard</span>
                        <span class="grade-desc">Khó</span>
                    </button>
                    <button class="btn-grade grade-3" onclick="gradeCard(3, 'Good')">
                        <span class="grade-label">Good</span>
                        <span class="grade-desc">Ổn</span>
                    </button>
                    <button class="btn-grade grade-4" onclick="gradeCard(4, 'Easy')">
                        <span class="grade-label">Easy</span>
                        <span class="grade-desc">Dễ</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Card Info -->
        <div class="card-info">
            <div class="info-row">
                <span class="label">Loại thẻ:</span>
                <span class="value" id="cardTypeValue"><?= ['Cơ bản', 'Đảo ngược', 'Nhập liệu'][$currentCard->cardtype - 1] ?? 'Không xác định' ?></span>
            </div>
            <?php if ($currentCard->tags): ?>
                <div class="info-row">
                    <span class="label">Nhãn:</span>
                    <span class="value" id="cardTagsValue"><?= Html::encode($currentCard->tags) ?></span>
                </div>
            <?php else: ?>
                <div class="info-row" id="cardTagsRow" style="display: none;">
                    <span class="label">Nhãn:</span>
                    <span class="value" id="cardTagsValue"></span>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<script>
const deckId = <?= $deck->deckid ?>;

function flipCard() {
    const flashcard = document.getElementById('flashcard');
    const nextContainer = document.getElementById('nextButtonContainer');
    const gradeContainer = document.getElementById('gradeButtonContainer');
    
    // Lật thẻ
    flashcard.classList.toggle('flipped');
    flashcard.setAttribute('data-flipped', 'true');
    
    // Ẩn nút Tiếp theo, hiện nút đánh giá
    nextContainer.style.display = 'none';
    gradeContainer.style.display = 'block';
}

function gradeCard(grade, gradeName) {
    const flashcard = document.getElementById('flashcard');
    const currentCardId = flashcard.getAttribute('data-cardid');
    const gradeBtn = event.target.closest('.btn-grade');
    
    // LƯU label/desc trước khi tay lên spinner
    const gradeLabel = gradeBtn.querySelector('.grade-label');
    const gradeDesc = gradeBtn.querySelector('.grade-desc');
    const savedLabel = gradeLabel ? gradeLabel.textContent : gradeName;
    const savedDesc = gradeDesc ? gradeDesc.textContent : '';
    
    gradeBtn.disabled = true;
    gradeBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

    // 1. Submit grade
    fetch('<?= Url::to(['site/ajax-grade-card']) ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-CSRF-Token': '<?= Yii::$app->request->csrfToken ?>'
        },
        body: new URLSearchParams({
            cardId: currentCardId,
            grade: grade
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            // 2. Get next card
            return fetch('<?= Url::to(['site/ajax-get-next-card']) ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-CSRF-Token': '<?= Yii::$app->request->csrfToken ?>'
                },
                body: new URLSearchParams({
                    deckId: deckId,
                    currentCardId: currentCardId
                })
            });
        } else {
            throw new Error(data.message || 'Lỗi khi lưu grade');
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.finished) {
            // Hết thẻ, quay lại practice
            alert(data.message);
            window.location.href = '<?= Url::to(['site/practice']) ?>';
        } else if (data.success) {
            // Update UI với thẻ tiếp theo
            updateCard(data.card);
            // RESTORE button text AFTER updateCard
            setTimeout(() => {
                gradeBtn.innerHTML = '<span class="grade-label">' + savedLabel + '</span><span class="grade-desc">' + savedDesc + '</span>';
            }, 0);
        } else {
            throw new Error(data.message || 'Lỗi khi lấy thẻ tiếp theo');
        }
    })
    .catch(err => {
        console.error(err);
        alert('Lỗi: ' + err.message);
        gradeBtn.disabled = false;
        gradeBtn.innerHTML = '<span class="grade-label">' + savedLabel + '</span><span class="grade-desc">' + savedDesc + '</span>';
    });
}

function updateCard(cardData) {
    const flashcard = document.getElementById('flashcard');
    const nextContainer = document.getElementById('nextButtonContainer');
    const gradeContainer = document.getElementById('gradeButtonContainer');
    
    // Update card data attributes
    flashcard.setAttribute('data-cardid', cardData.cardid);
    
    // Reset flip state
    flashcard.classList.remove('flipped');
    flashcard.setAttribute('data-flipped', 'false');
    
    // Update text
    document.getElementById('cardFrontText').innerHTML = cardData.frontcontent;
    document.getElementById('cardBackText').innerHTML = cardData.backcontent;
    
    // Update pronunciation
    if (cardData.pronunciation) {
        document.getElementById('cardPronunciation').innerHTML = '/ ' + cardData.pronunciation + ' /';
        document.getElementById('cardPronunciation').style.display = 'block';
    } else {
        document.getElementById('cardPronunciation').style.display = 'none';
    }
    
    // Update example
    if (cardData.examplesentence) {
        document.getElementById('cardExample').innerHTML = '<strong>Ví dụ:</strong> <em>' + cardData.examplesentence + '</em>';
        document.getElementById('cardExample').style.display = 'block';
    } else {
        document.getElementById('cardExample').style.display = 'none';
    }
    
    // Update card type
    const typeNames = ['Cơ bản', 'Đảo ngược', 'Nhập liệu'];
    document.getElementById('cardTypeValue').textContent = typeNames[cardData.cardtype - 1] || 'Không xác định';
    
    // Update tags
    if (cardData.tags) {
        document.getElementById('cardTagsValue').textContent = cardData.tags;
        var tagsRow = document.getElementById('cardTagsRow');
        if (tagsRow) tagsRow.style.display = 'flex';
    } else {
        var tagsRow = document.getElementById('cardTagsRow');
        if (tagsRow) tagsRow.style.display = 'none';
    }
    
    // Reset buttons and containers
    nextContainer.style.display = 'block';
    gradeContainer.style.display = 'none';
    
    // Reset grade buttons: disable everything first, then re-enable
    // (gradeCard sẽ restore HTML/state sau khi hàm này chạy xong)
    document.querySelectorAll('.btn-grade').forEach(btn => {
        btn.disabled = false;
    });
    
    // UPDATE PROGRESS BAR (FIX: thanh tiến độ không chạy)
    const progressBar = document.querySelector('.progress-fill');
    const progressText = document.querySelector('.progress-text');
    if (progressBar && cardData.cardIndex !== undefined && cardData.totalCards !== undefined) {
        const percentage = (cardData.cardIndex / cardData.totalCards) * 100;
        progressBar.style.width = percentage + '%';
        progressText.textContent = 'Thẻ ' + cardData.cardIndex + ' / ' + cardData.totalCards;
    }
}
</script>
