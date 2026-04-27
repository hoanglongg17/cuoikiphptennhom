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
        <?php $cardType = $currentCard->cardtype ?? 1; ?>
        
        <!-- LOẠI 1: CƠ BẢN (Lật thẻ) -->
        <?php if ($cardType == 1): ?>
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
        
        <!-- LOẠI 2: ĐẢO NGƯỢC (Cả 2 mặt) -->
        <?php elseif ($cardType == 2): ?>
        <div id="flashcard" class="flashcard flashcard-reverse" data-cardid="<?= $currentCard->cardid ?>" data-flipped="false">
            <div class="card-reverse-container">
                <div class="card-reverse-side card-front">
                    <div class="card-label">Mặt trước</div>
                    <div class="card-text"><?= nl2br(Html::encode($currentCard->frontcontent)) ?></div>
                    <?php if ($currentCard->pronunciation): ?>
                        <div class="card-pronunciation">/ <?= Html::encode($currentCard->pronunciation) ?> /</div>
                    <?php endif; ?>
                </div>
                <div class="card-reverse-divider" onclick="toggleReverse()" style="cursor: pointer; text-align: center; color: #999; padding: 20px; font-size: 14px;">
                    ⟷ Nhấn để đảo
                </div>
                <div class="card-reverse-side card-back">
                    <div class="card-label">Mặt sau</div>
                    <div class="card-text"><?= nl2br(Html::encode($currentCard->backcontent)) ?></div>
                    <?php if ($currentCard->examplesentence): ?>
                        <div class="card-example">
                            <strong>Ví dụ:</strong> <em><?= nl2br(Html::encode($currentCard->examplesentence)) ?></em>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- LOẠI 3: NHẬP LIỆU -->
        <?php elseif ($cardType == 3): ?>
        <div id="flashcard" class="flashcard flashcard-input" data-cardid="<?= $currentCard->cardid ?>">
            <div id="inputPhase1" class="input-phase">
                <div class="card-front input-mode">
                    <div class="card-label">Trả lời</div>
                    <div class="card-question"><?= nl2br(Html::encode($currentCard->frontcontent)) ?></div>
                    <input type="text" id="userAnswer" class="input-answer" placeholder="Nhập câu trả lời...">
                    <button class="btn-submit-answer" onclick="submitAnswer()">Kiểm tra</button>
                </div>
            </div>
            <div id="inputPhase2" class="input-phase" style="display: none;">
                <div class="card-answer-reveal">
                    <div class="question-display">
                        <div class="card-label">Câu hỏi</div>
                        <div class="card-text"><?= nl2br(Html::encode($currentCard->frontcontent)) ?></div>
                    </div>
                    <div class="answer-compare">
                        <div class="left-side">
                            <div class="card-label">Câu trả lời của bạn</div>
                            <div class="card-text user-answer-display"></div>
                        </div>
                        <div class="right-side">
                            <div class="card-label">Câu trả lời đúng</div>
                            <div class="card-text" id="cardBackText"><?= nl2br(Html::encode($currentCard->backcontent)) ?></div>
                            <?php if ($currentCard->examplesentence): ?>
                                <div class="card-example">
                                    <strong>Ví dụ:</strong> <em><?= nl2br(Html::encode($currentCard->examplesentence)) ?></em>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Control Buttons -->
        <div class="study-controls">
            <?php $cardType = $currentCard->cardtype ?? 1; ?>
            
            <!-- LOẠI 1: CƠ BẢN - Nút Tiếp theo (lật) -->
            <?php if ($cardType == 1): ?>
            <div id="nextButtonContainer" class="next-button-container">
                <button class="btn-next" onclick="flipCard()">
                    Tiếp theo →
                </button>
            </div>
            
            <!-- LOẠI 2: ĐẢO NGƯỢC - Nút Tiếp theo (xem đáp án) -->
            <?php elseif ($cardType == 2): ?>
            <div id="nextButtonContainer" class="next-button-container">
                <button class="btn-next" onclick="showGradeButtons()">
                    Tiếp theo →
                </button>
            </div>
            
            <!-- LOẠI 3: NHẬP LIỆU - Không cần nút ở đây (có submitAnswer) -->
            <?php elseif ($cardType == 3): ?>
            <div id="nextButtonContainer" class="next-button-container" style="display: none;">
                <button class="btn-next" onclick="showGradeButtons()">
                    Tiếp theo →
                </button>
            </div>
            <?php endif; ?>

            <!-- Nút đánh giá (hiện sau khi lật hoặc submit) -->
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
    
    // Determine card type
    const cardType = cardData.cardtype || 1;
    const typeNames = ['Cơ bản', 'Đảo ngược', 'Nhập liệu'];
    
    // Update card type display
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
    
    // Update UI based on card type
    if (cardType == 1) {
        // LOẠI 1: CƠ BẢN
        if (document.getElementById('cardFrontText')) {
            document.getElementById('cardFrontText').innerHTML = cardData.frontcontent;
        }
        if (document.getElementById('cardBackText')) {
            document.getElementById('cardBackText').innerHTML = cardData.backcontent;
        }
        if (cardData.pronunciation && document.getElementById('cardPronunciation')) {
            document.getElementById('cardPronunciation').innerHTML = '/ ' + cardData.pronunciation + ' /';
            document.getElementById('cardPronunciation').style.display = 'block';
        }
        if (cardData.examplesentence && document.getElementById('cardExample')) {
            document.getElementById('cardExample').innerHTML = '<strong>Ví dụ:</strong> <em>' + cardData.examplesentence + '</em>';
            document.getElementById('cardExample').style.display = 'block';
        }
    } else if (cardType == 2) {
        // LOẠI 2: ĐẢO NGƯỢC - cái này cần render lại từ server nên reload page
        window.location.reload();
    } else if (cardType == 3) {
        // LOẠI 3: NHẬP LIỆU - cái này cũng cần reload
        window.location.reload();
    }
    
    // Reset buttons and containers
    nextContainer.style.display = 'block';
    gradeContainer.style.display = 'none';
    
    // Reset grade buttons
    document.querySelectorAll('.btn-grade').forEach(btn => {
        btn.disabled = false;
    });
    
    // UPDATE PROGRESS BAR
    const progressBar = document.querySelector('.progress-fill');
    const progressText = document.querySelector('.progress-text');
    if (progressBar && cardData.cardIndex !== undefined && cardData.totalCards !== undefined) {
        const percentage = (cardData.cardIndex / cardData.totalCards) * 100;
        progressBar.style.width = percentage + '%';
        progressText.textContent = 'Thẻ ' + cardData.cardIndex + ' / ' + cardData.totalCards;
    }
}

// LOẠI 2: ĐẢO NGƯỢC - Toggle hiển thị 2 mặt
function toggleReverse() {
    const flashcard = document.getElementById('flashcard');
    flashcard.classList.toggle('reversed');
}

// LOẠI 3: NHẬP LIỆU - Submit đáp án
function submitAnswer() {
    const userAnswer = document.getElementById('userAnswer');
    if (!userAnswer || !userAnswer.value.trim()) {
        alert('Vui lòng nhập câu trả lời cả!');
        return;
    }
    
    // Hiển thị phase 2 (so sánh)
    const phase1 = document.getElementById('inputPhase1');
    const phase2 = document.getElementById('inputPhase2');
    
    if (phase1) phase1.style.display = 'none';
    if (phase2) phase2.style.display = 'block';
    
    // Hiển thị đáp án của user
    const userAnswerDisplay = document.querySelector('.user-answer-display');
    if (userAnswerDisplay) {
        userAnswerDisplay.innerHTML = userAnswer.value;
    }
    
    // Ẩn nút Tiếp theo, hiện nút đánh giá
    const nextContainer = document.getElementById('nextButtonContainer');
    const gradeContainer = document.getElementById('gradeButtonContainer');
    if (nextContainer) nextContainer.style.display = 'none';
    if (gradeContainer) gradeContainer.style.display = 'block';
}

// LOẠI 2 & 3: Hiển thị nút đánh giá
function showGradeButtons() {
    const nextContainer = document.getElementById('nextButtonContainer');
    const gradeContainer = document.getElementById('gradeButtonContainer');
    
    if (nextContainer) nextContainer.style.display = 'none';
    if (gradeContainer) gradeContainer.style.display = 'block';
}
</script>
