<?php







/** @var yii\web\View $this */
/** @var app\models\Deck $deck */
/** @var app\models\Card $currentCard */
/** @var int $cardIndex */
/** @var int $totalCards */

use yii\helpers\Url;
use yii\helpers\Html;
use app\helpers\SM2Helper;

$this->title = 'Học - ' . $deck->name . ' - Andi';

$this->registerCssFile('@web/css/dashboard.css', ['depends' => [\app\assets\AppAsset::class]]);
$this->registerCssFile('@web/css/study-deck.css', ['depends' => [\app\assets\AppAsset::class]]);
?>

<div class="study-deck-container">
    
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

    
    <div class="study-content">
        <?php $cardType = $currentCard->cardtype ?? 1; ?>
        
        
        <?php if ($cardType == 1): ?>
        <div id="flashcard" class="flashcard" data-flipped="false" data-cardid="<?= $currentCard->cardid ?>" data-card-status="<?= $currentCard->progress ? $currentCard->progress->status : 0 ?>" data-card-interval="<?= $currentCard->progress ? $currentCard->progress->intervaldays : 0 ?>" data-card-repetitions="<?= $currentCard->progress ? $currentCard->progress->repetitions : 0 ?>" data-card-easefactor="<?= $currentCard->progress ? $currentCard->progress->easefactor : 2.5 ?>">
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
        
        
        <?php elseif ($cardType == 2): ?>
        <div id="flashcard" class="flashcard flashcard-reverse" data-flipped="false" data-cardid="<?= $currentCard->cardid ?>" data-card-status="<?= $currentCard->progress ? $currentCard->progress->status : 0 ?>" data-card-interval="<?= $currentCard->progress ? $currentCard->progress->intervaldays : 0 ?>" data-card-repetitions="<?= $currentCard->progress ? $currentCard->progress->repetitions : 0 ?>" data-card-easefactor="<?= $currentCard->progress ? $currentCard->progress->easefactor : 2.5 ?>">
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
        
        
        <?php elseif ($cardType == 3): ?>
        <div id="flashcard" class="flashcard flashcard-input" data-cardid="<?= $currentCard->cardid ?>" data-card-status="<?= $currentCard->progress ? $currentCard->progress->status : 0 ?>" data-card-interval="<?= $currentCard->progress ? $currentCard->progress->intervaldays : 0 ?>" data-card-repetitions="<?= $currentCard->progress ? $currentCard->progress->repetitions : 0 ?>" data-card-easefactor="<?= $currentCard->progress ? $currentCard->progress->easefactor : 2.5 ?>">
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

        
        <div class="study-controls">
            <?php $cardType = $currentCard->cardtype ?? 1; ?>
            
            
            <?php if ($cardType == 1): ?>
            <div id="nextButtonContainer" class="next-button-container">
                <button class="btn-next" onclick="flipCard()">
                    Tiếp theo →
                </button>
            </div>
            
            
            <?php elseif ($cardType == 2): ?>
            <div id="nextButtonContainer" class="next-button-container">
                <button class="btn-next" onclick="showGradeButtons()">
                    Tiếp theo →
                </button>
            </div>
            
            
            <?php elseif ($cardType == 3): ?>
            <div id="nextButtonContainer" class="next-button-container" style="display: none;">
                <button class="btn-next" onclick="showGradeButtons()">
                    Tiếp theo →
                </button>
            </div>
            <?php endif; ?>

            
            <div id="gradeButtonContainer" class="grade-buttons-container" style="display: none;">
                <p class="flip-hint" style="margin-bottom: 20px;">Chọn độ khó</p>
                <div class="grade-buttons">
                    <button class="btn-grade grade-1" onclick="gradeCard(1, 'Again')" data-grade="1">
                        <span class="grade-label">Again</span>
                        <span class="grade-desc">Quên</span>
                        <span class="grade-time">10ph</span>
                    </button>
                    <button class="btn-grade grade-2" onclick="gradeCard(2, 'Hard')" data-grade="2">
                        <span class="grade-label">Hard</span>
                        <span class="grade-desc">Khó</span>
                        <span class="grade-time">—</span>
                    </button>
                    <button class="btn-grade grade-3" onclick="gradeCard(3, 'Good')" data-grade="3">
                        <span class="grade-label">Good</span>
                        <span class="grade-desc">Ổn</span>
                        <span class="grade-time">—</span>
                    </button>
                    <button class="btn-grade grade-4" onclick="gradeCard(4, 'Easy')" data-grade="4">
                        <span class="grade-label">Easy</span>
                        <span class="grade-desc">Dễ</span>
                        <span class="grade-time">4ng</span>
                    </button>
                </div>
            </div>
        </div>

        
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
    
    
    flashcard.classList.toggle('flipped');
    flashcard.setAttribute('data-flipped', 'true');
    
    
    nextContainer.style.display = 'none';
    gradeContainer.style.display = 'block';
}

function gradeCard(grade, gradeName) {
    const flashcard = document.getElementById('flashcard');
    const currentCardId = flashcard.getAttribute('data-cardid');
    const gradeBtn = event.target.closest('.btn-grade');
    
    
    const gradeLabel = gradeBtn.querySelector('.grade-label');
    const gradeDesc = gradeBtn.querySelector('.grade-desc');
    const savedLabel = gradeLabel ? gradeLabel.textContent : gradeName;
    const savedDesc = gradeDesc ? gradeDesc.textContent : '';
    
    gradeBtn.disabled = true;
    gradeBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

    
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
            
            alert(data.message);
            window.location.href = '<?= Url::to(['site/practice']) ?>';
        } else if (data.success) {
            
            updateCard(data.card);
            
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
    
    
    flashcard.setAttribute('data-cardid', cardData.cardid);
    
    
    if (cardData.status !== undefined) {
        flashcard.setAttribute('data-card-status', cardData.status);
    }
    if (cardData.intervaldays !== undefined) {
        flashcard.setAttribute('data-card-interval', cardData.intervaldays);
    }
    if (cardData.repetitions !== undefined) {
        flashcard.setAttribute('data-card-repetitions', cardData.repetitions);
    }
    if (cardData.easefactor !== undefined) {
        flashcard.setAttribute('data-card-easefactor', cardData.easefactor);
    }
    
    
    flashcard.classList.remove('flipped');
    flashcard.setAttribute('data-flipped', 'false');
    
    
    const cardType = cardData.cardtype || 1;
    const typeNames = ['Cơ bản', 'Đảo ngược', 'Nhập liệu'];
    
    
    document.getElementById('cardTypeValue').textContent = typeNames[cardData.cardtype - 1] || 'Không xác định';
    
    
    if (cardData.tags) {
        document.getElementById('cardTagsValue').textContent = cardData.tags;
        var tagsRow = document.getElementById('cardTagsRow');
        if (tagsRow) tagsRow.style.display = 'flex';
    } else {
        var tagsRow = document.getElementById('cardTagsRow');
        if (tagsRow) tagsRow.style.display = 'none';
    }
    
    
    if (cardType == 1) {
        
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
        
        window.location.reload();
    } else if (cardType == 3) {
        
        window.location.reload();
    }
    
    
    nextContainer.style.display = 'block';
    gradeContainer.style.display = 'none';
    
    
    document.querySelectorAll('.btn-grade').forEach(btn => {
        btn.disabled = false;
    });
    
    
    const progressBar = document.querySelector('.progress-fill');
    const progressText = document.querySelector('.progress-text');
    if (progressBar && cardData.cardIndex !== undefined && cardData.totalCards !== undefined) {
        const percentage = (cardData.cardIndex / cardData.totalCards) * 100;
        progressBar.style.width = percentage + '%';
        progressText.textContent = 'Thẻ ' + cardData.cardIndex + ' / ' + cardData.totalCards;
    }
    
    
    updateGradeTimings();
}


function toggleReverse() {
    const flashcard = document.getElementById('flashcard');
    flashcard.classList.toggle('reversed');
}


function submitAnswer() {
    const userAnswer = document.getElementById('userAnswer');
    if (!userAnswer || !userAnswer.value.trim()) {
        alert('Vui lòng nhập câu trả lời cả!');
        return;
    }
    
    
    const phase1 = document.getElementById('inputPhase1');
    const phase2 = document.getElementById('inputPhase2');
    
    if (phase1) phase1.style.display = 'none';
    if (phase2) phase2.style.display = 'block';
    
    
    const userAnswerDisplay = document.querySelector('.user-answer-display');
    if (userAnswerDisplay) {
        userAnswerDisplay.innerHTML = userAnswer.value;
    }
    
    
    const nextContainer = document.getElementById('nextButtonContainer');
    const gradeContainer = document.getElementById('gradeButtonContainer');
    if (nextContainer) nextContainer.style.display = 'none';
    if (gradeContainer) gradeContainer.style.display = 'block';
}


function showGradeButtons() {
    const nextContainer = document.getElementById('nextButtonContainer');
    const gradeContainer = document.getElementById('gradeButtonContainer');
    
    if (nextContainer) nextContainer.style.display = 'none';
    if (gradeContainer) gradeContainer.style.display = 'block';
    
    updateGradeTimings();
}


function updateGradeTimings() {
    const flashcard = document.getElementById('flashcard');
    if (!flashcard) return;
    
    const status = parseInt(flashcard.dataset.cardStatus, 10) || 0;
    const interval = parseFloat(flashcard.dataset.cardInterval) || 0;
    const repetitions = parseInt(flashcard.dataset.cardRepetitions, 10) || 0;
    const easefactor = parseFloat(flashcard.dataset.cardEasefactor) || 2.5;
    
    
    const LEARNING_STEPS = [1, 10]; 
    const GRADUATING_INTERVAL = 1; 
    const EASY_INTERVAL = 4; 
    const RELEARNING_STEPS = [10]; 
    
    
    const timings = {};
    for (let grade = 1; grade <= 4; grade++) {
        const result = calculateSM2(grade, status, repetitions, interval, easefactor, LEARNING_STEPS, GRADUATING_INTERVAL, EASY_INTERVAL, RELEARNING_STEPS);
        timings[grade] = formatTime(result.nextReview);
    }
    
    
    document.querySelectorAll('.btn-grade').forEach(btn => {
        const grade = parseInt(btn.dataset.grade, 10);
        const timeSpan = btn.querySelector('.grade-time');
        if (timeSpan && timings[grade]) {
            timeSpan.textContent = timings[grade];
        }
    });
}


function calculateSM2(grade, status, repetitions, interval, easefactor, LEARNING_STEPS, GRADUATING_INTERVAL, EASY_INTERVAL, RELEARNING_STEPS) {
    grade = Math.max(1, Math.min(4, parseInt(grade, 10)));
    easefactor = Math.max(1.3, easefactor);
    
    let nextReviewDate = new Date();
    let nextReviewMinutes = 0;
    let nextReviewDays = 0;
    let newStatus = status;
    let newRepetitions = repetitions;
    
    if (grade === 1) {
        
        nextReviewMinutes = RELEARNING_STEPS[0]; 
        newStatus = 1; 
    } else if (status === 0) {
        
        if (grade === 4) {
            newStatus = 2;
            newRepetitions = 1;
            nextReviewDays = EASY_INTERVAL; 
            easefactor = easefactor + 0.1;
        } else {
            
            newStatus = 1;
            newRepetitions = 0;
            nextReviewMinutes = LEARNING_STEPS[0]; 
        }
    } else if (status === 1) {
        
        if (grade === 4) {
            
            newStatus = 2;
            newRepetitions = 1;
            nextReviewDays = EASY_INTERVAL; 
            easefactor = easefactor + 0.1;
        } else if (grade === 2) {
            
            nextReviewMinutes = LEARNING_STEPS[0]; 
        } else if (grade === 3) {
            
            if (newRepetitions < LEARNING_STEPS.length - 1) {
                
                newRepetitions++;
                nextReviewMinutes = LEARNING_STEPS[newRepetitions]; 
            } else {
                
                newStatus = 2;
                newRepetitions = 1;
                nextReviewDays = GRADUATING_INTERVAL; 
            }
        }
    } else if (status === 2) {
        
        let newInterval = interval;
        if (grade === 2) {
            
            newInterval = Math.max(1, Math.round(interval * 0.6));
        } else if (grade === 3) {
            
            newInterval = Math.round(interval * easefactor);
        } else if (grade === 4) {
            
            newInterval = Math.round(interval * (easefactor + 0.1));
            easefactor = easefactor + 0.1;
        }
        nextReviewDays = Math.max(1, newInterval);
        newRepetitions = repetitions + 1;
    }
    
    
    
    const q = grade;
    easefactor = easefactor + (0.1 - (5 - q) * (0.08 + (5 - q) * 0.02));
    easefactor = Math.max(1.3, easefactor);
    
    
    if (nextReviewMinutes > 0) {
        nextReviewDate.setMinutes(nextReviewDate.getMinutes() + nextReviewMinutes);
    }
    if (nextReviewDays > 0) {
        nextReviewDate.setDate(nextReviewDate.getDate() + nextReviewDays);
    }
    
    return { nextReview: nextReviewDate, status: newStatus, repetitions: newRepetitions };
}


function formatTime(date) {
    const now = new Date();
    const diffMs = date.getTime() - now.getTime();
    const diffMins = Math.ceil(diffMs / 60000);
    const diffDays = Math.ceil(diffMs / (24 * 60 * 60 * 1000));
    
    
    if (diffMins < 60 && diffMins > 0) {
        return diffMins + 'ph';
    } 
    
    else if (diffDays >= 0 && diffDays < 30) {
        return Math.max(1, diffDays) + 'ng';
    } 
    
    else {
        const diffMonths = Math.ceil(diffDays / 30);
        return diffMonths + 'th';
    }
}


document.addEventListener('DOMContentLoaded', function() {
    updateGradeTimings();
});
</script>
