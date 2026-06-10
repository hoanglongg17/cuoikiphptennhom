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
    </div>

    
    <div class="study-content">
        <?php $cardType = $currentCard->cardtype ?? 1; ?>
        
</div>

<!-- Completion modal -->
<div id="completionModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.45); align-items:center; justify-content:center; z-index:10000;">
    <div style="background:#fff; padding:24px 28px; border-radius:12px; box-shadow:0 10px 30px rgba(0,0,0,0.2); max-width:420px; width:90%; text-align:center;">
        <h3 id="completionTitle" style="margin:0 0 8px;">Hoàn thành!</h3>
        <p id="completionMessage" style="color:#4a5568; margin:0 0 18px;">Bạn đã hoàn thành tất cả thẻ trong bộ này.</p>
        <div style="display:flex; gap:12px; justify-content:center;">
            <button id="btnReturnPractice" style="background:#10B981; color:#fff; padding:10px 16px; border-radius:8px; border:none; cursor:pointer; font-weight:700;">Quay lại luyện tập</button>
            <button id="btnCloseCompletion" style="background:#e5e7eb; color:#111827; padding:10px 12px; border-radius:8px; border:none; cursor:pointer;">Đóng</button>
        </div>
    </div>
</div>
        <div id="flashcard-type-1" class="flashcard" style="<?= $cardType == 1 ? '' : 'display: none;' ?>" data-flipped="false" data-cardid="<?= $currentCard->cardid ?>" data-card-status="<?= $currentCard->progress ? $currentCard->progress->status : 0 ?>" data-card-interval="<?= $currentCard->progress ? $currentCard->progress->intervaldays : 0 ?>" data-card-repetitions="<?= $currentCard->progress ? $currentCard->progress->repetitions : 0 ?>" data-card-easefactor="<?= $currentCard->progress ? $currentCard->progress->easefactor : 2.5 ?>">
            <div class="card-inner">
                <div class="card-front">
                    <div class="card-label">Mặt trước</div>
                    <div class="card-text" id="cardFrontText-1"><?= nl2br(Html::encode($currentCard->frontcontent)) ?></div>
                    <?php if ($currentCard->pronunciation): ?>
                        <div class="card-pronunciation" id="cardPronunciation-1">/ <?= Html::encode($currentCard->pronunciation) ?> /</div>
                    <?php else: ?>
                        <div class="card-pronunciation" id="cardPronunciation-1" style="display: none;"></div>
                    <?php endif; ?>
                </div>
                <div class="card-back">
                    <div class="card-label">Mặt sau</div>
                    <div class="card-text" id="cardBackText-1"><?= nl2br(Html::encode($currentCard->backcontent)) ?></div>
                    <?php if ($currentCard->pronunciation): ?>
                        <div class="card-pronunciation" id="cardPronunciation-1b">/ <?= Html::encode($currentCard->pronunciation) ?> /</div>
                    <?php endif; ?>
                    <button id="cardPlay-1" data-suffix="-1" onclick="playAudio(this.dataset.suffix)" style="background:none; border:none; cursor:pointer; margin-left: 0; font-size: 28px; padding: 0; color: #fff; transition: transform 0.12s;" title="Nghe phát âm" onmouseover="this.style.transform='scale(1.12)'" onmouseout="this.style.transform='scale(1)'">🔊</button>
                    <?php if ($currentCard->imageurl): ?>
                        <div class="card-image" id="cardImage-1">
                            <img src="<?= Html::encode($currentCard->imageurl) ?>" alt="image" />
                        </div>
                    <?php else: ?>
                        <div class="card-image" id="cardImage-1" style="display:none;"></div>
                    <?php endif; ?>
                    <?php if ($currentCard->examplesentence): ?>
                        <div class="card-example" id="cardExample-1" style="margin-top: 12px;">
                            <strong>Ví dụ:</strong> <em><?= nl2br(Html::encode($currentCard->examplesentence)) ?></em>
                        </div>
                    <?php else: ?>
                        <div class="card-example" id="cardExample-1" style="display: none;"></div>
                    <?php endif; ?>
                    <?php if ($currentCard->audiourl): ?>
                        <div class="card-audio" id="cardAudioWrap-1">
                            <audio id="cardAudio-1" preload="none" style="display:none;">
                                <source src="<?= Html::encode($currentCard->audiourl) ?>" />
                                Trình duyệt không hỗ trợ audio.
                            </audio>
                        </div>
                    <?php else: ?>
                        <div class="card-audio" id="cardAudioWrap-1" style="display:none;"></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div id="flashcard-type-2" class="flashcard<?= $cardType == 2 ? ' flipped' : '' ?>" style="<?= $cardType == 2 ? '' : 'display: none;' ?>" data-flipped="<?= $cardType == 2 ? 'true' : 'false' ?>" data-cardid="<?= $currentCard->cardid ?>" data-card-status="<?= $currentCard->progress ? $currentCard->progress->status : 0 ?>" data-card-interval="<?= $currentCard->progress ? $currentCard->progress->intervaldays : 0 ?>" data-card-repetitions="<?= $currentCard->progress ? $currentCard->progress->repetitions : 0 ?>" data-card-easefactor="<?= $currentCard->progress ? $currentCard->progress->easefactor : 2.5 ?>">
            <div class="card-inner">
                <div class="card-front">
                    <div class="card-label">Mặt trước</div>
                    <div class="card-text" id="cardFrontText-2"><?= nl2br(Html::encode($currentCard->frontcontent)) ?></div>
                    <?php if ($currentCard->pronunciation): ?>
                        <div class="card-pronunciation" id="cardPronunciation-2">/ <?= Html::encode($currentCard->pronunciation) ?> /</div>
                    <?php else: ?>
                        <div class="card-pronunciation" id="cardPronunciation-2" style="display: none;"></div>
                    <?php endif; ?>
                </div>
                <div class="card-back">
                    <div class="card-label">Mặt sau</div>
                    <div class="card-text" id="cardBackText-2"><?= nl2br(Html::encode($currentCard->backcontent)) ?></div>
                    <?php if ($currentCard->pronunciation): ?>
                        <div class="card-pronunciation" id="cardPronunciation-2b">/ <?= Html::encode($currentCard->pronunciation) ?> /</div>
                    <?php endif; ?>
                    <button id="cardPlay-2" data-suffix="-2" onclick="playAudio(this.dataset.suffix)" style="background:none; border:none; cursor:pointer; margin-left: 0; font-size: 28px; padding: 0; color: #fff; transition: transform 0.12s;" title="Nghe phát âm" onmouseover="this.style.transform='scale(1.12)'" onmouseout="this.style.transform='scale(1)'">🔊</button>
                    <?php if ($currentCard->imageurl): ?>
                        <div class="card-image" id="cardImage-2">
                            <img src="<?= Html::encode($currentCard->imageurl) ?>" alt="image" />
                        </div>
                    <?php else: ?>
                        <div class="card-image" id="cardImage-2" style="display:none;"></div>
                    <?php endif; ?>
                    <?php if ($currentCard->examplesentence): ?>
                        <div class="card-example" id="cardExample-2" style="margin-top: 12px;">
                            <strong>Ví dụ:</strong> <em><?= nl2br(Html::encode($currentCard->examplesentence)) ?></em>
                        </div>
                    <?php else: ?>
                        <div class="card-example" id="cardExample-2" style="display: none;"></div>
                    <?php endif; ?>
                    <?php if ($currentCard->audiourl): ?>
                        <div class="card-audio" id="cardAudioWrap-2">
                            <audio id="cardAudio-2" preload="none" style="display:none;">
                                <source src="<?= Html::encode($currentCard->audiourl) ?>" />
                                Trình duyệt không hỗ trợ audio.
                            </audio>
                        </div>
                    <?php else: ?>
                        <div class="card-audio" id="cardAudioWrap-2" style="display:none;"></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div id="flashcard-type-3" class="flashcard flashcard-input" style="<?= $cardType == 3 ? '' : 'display: none;' ?>" data-cardid="<?= $currentCard->cardid ?>" data-card-status="<?= $currentCard->progress ? $currentCard->progress->status : 0 ?>" data-card-interval="<?= $currentCard->progress ? $currentCard->progress->intervaldays : 0 ?>" data-card-repetitions="<?= $currentCard->progress ? $currentCard->progress->repetitions : 0 ?>" data-card-easefactor="<?= $currentCard->progress ? $currentCard->progress->easefactor : 2.5 ?>">
            <div id="inputPhase1-3" class="input-phase">
                <div class="card-front input-mode">
                    <div class="card-label">Trả lời</div>
                    <div class="card-question" id="cardQuestion-3"><?= nl2br(Html::encode($currentCard->frontcontent)) ?></div>
                    <input type="text" id="userAnswer-3" class="input-answer" placeholder="Nhập câu trả lời...">
                    <button class="btn-submit-answer" onclick="submitAnswer()">Kiểm tra</button>
                </div>
            </div>
            <div id="inputPhase2-3" class="input-phase" style="display: none;">
                <div class="card-answer-reveal">
                    <div class="question-display">
                        <div class="card-label">Câu hỏi</div>
                        <div class="card-text" id="cardQuestion2-3"><?= nl2br(Html::encode($currentCard->frontcontent)) ?></div>
                    </div>
                    <div class="answer-compare">
                        <div class="left-side">
                            <div class="card-label">Câu trả lời của bạn</div>
                            <div class="card-text user-answer-display-3"></div>
                        </div>
                        <div class="right-side">
                            <div class="card-label">Câu trả lời đúng</div>
                            <div class="card-text" id="cardBackText-3"><?= nl2br(Html::encode($currentCard->backcontent)) ?></div>
                            <?php if ($currentCard->pronunciation): ?>
                                <div class="card-pronunciation" id="cardPronunciation-3">/ <?= Html::encode($currentCard->pronunciation) ?> /</div>
                            <?php endif; ?>
                            <button id="cardPlay-3" data-suffix="-3" onclick="playAudio(this.dataset.suffix)" style="background:none; border:none; cursor:pointer; margin-left: 0; font-size: 28px; padding: 0; color: #3182ce; transition: transform 0.12s;" title="Nghe phát âm" onmouseover="this.style.transform='scale(1.12)'" onmouseout="this.style.transform='scale(1)'">🔊</button>
                            <?php if ($currentCard->imageurl): ?>
                                <div class="card-image" id="cardImage-3">
                                    <img src="<?= Html::encode($currentCard->imageurl) ?>" alt="image" />
                                </div>
                            <?php else: ?>
                                <div class="card-image" id="cardImage-3" style="display:none;"></div>
                            <?php endif; ?>
                            <?php if ($currentCard->examplesentence): ?>
                                <div class="card-example" id="cardExample-3" style="margin-top: 12px;">
                                    <strong>Ví dụ:</strong> <em><?= nl2br(Html::encode($currentCard->examplesentence)) ?></em>
                                </div>
                            <?php else: ?>
                                <div class="card-example" id="cardExample-3" style="display: none;"></div>
                            <?php endif; ?>
                            <?php if ($currentCard->audiourl): ?>
                                <div class="card-audio" id="cardAudioWrap-3">
                                    <audio id="cardAudio-3" preload="none" style="display:none;">
                                        <source src="<?= Html::encode($currentCard->audiourl) ?>" />
                                        Trình duyệt không hỗ trợ audio.
                                    </audio>
                                </div>
                            <?php else: ?>
                                <div class="card-audio" id="cardAudioWrap-3" style="display:none;"></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="study-controls">
            <div id="nextButtonContainer" class="next-button-container">
                <button class="btn-next" onclick="flipCard()">
                    Tiếp theo →
                </button>
            </div>
            
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
const baseWeb = '<?= Yii::getAlias("@web") ?>';
const deckId = <?= $deck->deckid ?>;

function flipCard() {
    let flashcard = null;
    for (let type = 1; type <= 3; type++) {
        const card = document.getElementById('flashcard-type-' + type);
        if (card && card.style.display !== 'none') {
            flashcard = card;
            break;
        }
    }
    
    if (!flashcard) return;
    
    const nextContainer = document.getElementById('nextButtonContainer');
    const gradeContainer = document.getElementById('gradeButtonContainer');
    
    if (!flashcard.classList.contains('flashcard-input')) {
        flashcard.classList.toggle('flipped');
        flashcard.setAttribute('data-flipped', 'true');
    }
    
    nextContainer.style.display = 'none';
    gradeContainer.style.display = 'block';
}

function gradeCard(grade, gradeName) {
    let flashcard = null;
    for (let type = 1; type <= 3; type++) {
        const card = document.getElementById('flashcard-type-' + type);
        if (card && card.style.display !== 'none') {
            flashcard = card;
            break;
        }
    }
    
    if (!flashcard) return;
    
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
            // Show completion modal with return button
            const msg = data.message || 'Hoàn thành tất cả thẻ trong bộ này! 🎉';
            showCompletionModal(msg);
            document.querySelectorAll('.btn-grade').forEach(btn => btn.disabled = false);
            gradeBtn.innerHTML = '<span class="grade-label">' + savedLabel + '</span><span class="grade-desc">' + savedDesc + '</span>';
            return;
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
    const nextContainer = document.getElementById('nextButtonContainer');
    const gradeContainer = document.getElementById('gradeButtonContainer');
    const cardType = cardData.cardtype || 1;
    const typeNames = ['Cơ bản', 'Đảo ngược', 'Nhập liệu'];
    
    let oldFlashcard = null;
    for (let type = 1; type <= 3; type++) {
        const card = document.getElementById('flashcard-type-' + type);
        if (card && card.style.display !== 'none') {
            oldFlashcard = card;
            break;
        }
    }
    
    const newFlashcard = document.getElementById('flashcard-type-' + cardType);
    if (!newFlashcard) {
        console.error('Flashcard type ' + cardType + ' not found');
        return;
    }
    
    for (let type = 1; type <= 3; type++) {
        const card = document.getElementById('flashcard-type-' + type);
        if (card) card.style.display = type === cardType ? '' : 'none';
    }
    
    newFlashcard.setAttribute('data-cardid', cardData.cardid);
    if (cardData.status !== undefined) {
        newFlashcard.setAttribute('data-card-status', cardData.status);
    }
    if (cardData.intervaldays !== undefined) {
        newFlashcard.setAttribute('data-card-interval', cardData.intervaldays);
    }
    if (cardData.repetitions !== undefined) {
        newFlashcard.setAttribute('data-card-repetitions', cardData.repetitions);
    }
    if (cardData.easefactor !== undefined) {
        newFlashcard.setAttribute('data-card-easefactor', cardData.easefactor);
    }
    
    if (cardType === 1) {
        newFlashcard.classList.remove('flipped');
        newFlashcard.setAttribute('data-flipped', 'false');
    } else if (cardType === 2) {
        newFlashcard.classList.add('flipped');
        newFlashcard.setAttribute('data-flipped', 'true');
    } else if (cardType === 3) {
        const phase1 = newFlashcard.querySelector('#inputPhase1-3');
        const phase2 = newFlashcard.querySelector('#inputPhase2-3');
        const userAnswer = newFlashcard.querySelector('#userAnswer-3');
        if (phase1) phase1.style.display = 'block';
        if (phase2) phase2.style.display = 'none';
        if (userAnswer) userAnswer.value = '';
    }
    
    document.getElementById('cardTypeValue').textContent = typeNames[cardType - 1] || 'Không xác định';
    
    if (cardData.tags) {
        document.getElementById('cardTagsValue').textContent = cardData.tags;
        const tagsRow = document.getElementById('cardTagsRow');
        if (tagsRow) tagsRow.style.display = 'flex';
    } else {
        const tagsRow = document.getElementById('cardTagsRow');
        if (tagsRow) tagsRow.style.display = 'none';
    }
    
    if (cardType === 1) {
        updateFlashcardContent(1, cardData);
    } else if (cardType === 2) {
        updateFlashcardContent(2, cardData);
    } else if (cardType === 3) {
        updateInputCardContent(cardData);
    }
    
    if (cardType === 3) {
        nextContainer.style.display = 'none';
        gradeContainer.style.display = 'block';
    } else {
        nextContainer.style.display = 'block';
        gradeContainer.style.display = 'none';
    }
    
    document.querySelectorAll('.btn-grade').forEach(btn => {
        btn.disabled = false;
    });
    
    updateGradeTimings();
}

function updateFlashcardContent(cardType, cardData) {
    const suffix = '-' + cardType;
    const frontEl = document.getElementById('cardFrontText' + suffix);
    const backEl = document.getElementById('cardBackText' + suffix);
    const pronounEl = document.getElementById('cardPronunciation' + suffix);
    const exampleEl = document.getElementById('cardExample' + suffix);
    
    if (frontEl) frontEl.innerHTML = cardData.frontcontent;
    if (backEl) backEl.innerHTML = cardData.backcontent;
    
    if (cardData.pronunciation && pronounEl) {
        pronounEl.innerHTML = '/ ' + cardData.pronunciation + ' /';
        pronounEl.style.display = 'block';
    } else if (pronounEl) {
        pronounEl.style.display = 'none';
    }
    
    if (cardData.examplesentence && exampleEl) {
        exampleEl.innerHTML = '<strong>Ví dụ:</strong> <em>' + cardData.examplesentence + '</em>';
        exampleEl.style.display = 'block';
    } else if (exampleEl) {
        exampleEl.style.display = 'none';
    }
    // image handling
    const imageEl = document.getElementById('cardImage' + suffix);
    if (imageEl) {
        if (cardData.imageurl) {
            imageEl.style.display = 'block';
            const img = imageEl.querySelector('img');
            if (img) img.src = cardData.imageurl;
            else imageEl.innerHTML = '<img src="' + cardData.imageurl + '" alt="image" />';
        } else {
            imageEl.style.display = 'none';
        }
    }
    // audio handling
    const audioWrap = document.getElementById('cardAudioWrap' + suffix);
    const audioEl = document.getElementById('cardAudio' + suffix);
            if (audioWrap) {
                if (cardData.audiourl) {
                    // resolve relative URLs
                    let resolved = cardData.audiourl;
                    if (!/^https?:\/\//i.test(resolved) && resolved.indexOf('/') !== 0) {
                        resolved = baseWeb.replace(/\/$/, '') + '/' + resolved.replace(/^\//, '');
                    }
                    console.log('updateFlashcardContent audio url (suffix ' + suffix + '):', resolved);
                    audioWrap.style.display = 'block';
                    if (audioEl) {
                        const src = audioEl.querySelector('source');
                        if (src) src.setAttribute('src', resolved);
                        else audioEl.innerHTML = '<source src="' + resolved + '" />';
                        // DO NOT call audioEl.load() here — will load on user play to avoid interruptions
                    } else {
                        audioWrap.innerHTML = '<audio id="cardAudio' + suffix + '" preload="none" style="display:none;"><source src="' + resolved + '" />Trình duyệt không hỗ trợ audio.</audio>';
                    }
                } else {
                    audioWrap.style.display = 'none';
                }
            }
    // always show play button (uses audio file if present, otherwise falls back to TTS)
    const playBtn = document.getElementById('cardPlay' + suffix);
    if (playBtn) playBtn.style.display = 'inline-block';
}

function updateInputCardContent(cardData) {
    const questionEl = document.getElementById('cardQuestion-3');
    const question2El = document.getElementById('cardQuestion2-3');
    const backEl = document.getElementById('cardBackText-3');
    const exampleEl = document.getElementById('cardExample-3');
    
    if (questionEl) questionEl.innerHTML = cardData.frontcontent;
    if (question2El) question2El.innerHTML = cardData.frontcontent;
    if (backEl) backEl.innerHTML = cardData.backcontent;
    
    if (cardData.examplesentence && exampleEl) {
        exampleEl.innerHTML = '<strong>Ví dụ:</strong> <em>' + cardData.examplesentence + '</em>';
        exampleEl.style.display = 'block';
    } else if (exampleEl) {
        exampleEl.style.display = 'none';
    }
    // image
    const imageEl3 = document.getElementById('cardImage-3');
    if (imageEl3) {
        if (cardData.imageurl) {
            imageEl3.style.display = 'block';
            const img = imageEl3.querySelector('img');
            if (img) img.src = cardData.imageurl;
            else imageEl3.innerHTML = '<img src="' + cardData.imageurl + '" alt="image" />';
        } else {
            imageEl3.style.display = 'none';
        }
    }
    // audio
    const audioWrap3 = document.getElementById('cardAudioWrap-3');
    const audioEl3 = document.getElementById('cardAudio-3');
    if (audioWrap3) {
        if (cardData.audiourl) {
            let resolved3 = cardData.audiourl;
            if (!/^https?:\/\//i.test(resolved3) && resolved3.indexOf('/') !== 0) {
                resolved3 = baseWeb.replace(/\/$/, '') + '/' + resolved3.replace(/^\//, '');
            }
            console.log('updateInputCardContent audio url:', resolved3);
            audioWrap3.style.display = 'block';
            if (audioEl3) {
                const src = audioEl3.querySelector('source');
                if (src) src.setAttribute('src', resolved3);
                else audioEl3.innerHTML = '<source src="' + resolved3 + '" />';
                // DO NOT call audioEl3.load() here — load only when user clicks play
            } else {
                audioWrap3.innerHTML = '<audio id="cardAudio-3" preload="none" style="display:none;"><source src="' + resolved3 + '" />Trình duyệt không hỗ trợ audio.</audio>';
            }
        } else {
            audioWrap3.style.display = 'none';
        }
    }
    const playBtn3 = document.getElementById('cardPlay-3');
    if (playBtn3) playBtn3.style.display = 'inline-block';
}

function showToast(message) {
    // simple non-blocking toast
    let toast = document.createElement('div');
    toast.className = 'simple-toast';
    toast.textContent = message;
    toast.style.position = 'fixed';
    toast.style.right = '20px';
    toast.style.bottom = '20px';
    toast.style.background = 'rgba(0,0,0,0.8)';
    toast.style.color = '#fff';
    toast.style.padding = '10px 14px';
    toast.style.borderRadius = '6px';
    toast.style.zIndex = 9999;
    document.body.appendChild(toast);
    setTimeout(() => {
        try { toast.remove(); } catch(e) {}
    }, 4000);
}

function playAudio(suffix) {
    try {
        const audioId = 'cardAudio' + suffix;
        const audioWrap = document.getElementById('cardAudioWrap' + suffix);
        let audioEl = document.getElementById(audioId);

        // Determine resolved source URL
        let src = null;
        if (audioEl) {
            const s = audioEl.querySelector('source');
            src = s ? (s.getAttribute('src') || s.src) : (audioEl.getAttribute('src') || audioEl.src);
        }
        if (!src && audioWrap) {
            const s2 = audioWrap.querySelector('source');
            src = s2 ? (s2.getAttribute('src') || s2.src) : null;
        }

        if (!src) {
            // No audio file — fallback to speechSynthesis (like vocabulary page)
            const textEl = document.getElementById('cardFrontText' + suffix) || document.getElementById('cardQuestion' + suffix) || document.getElementById('cardBackText' + suffix);
            const text = textEl ? textEl.textContent.trim() : '';
            if ('speechSynthesis' in window && text) {
                window.speechSynthesis.cancel();
                const msg = new SpeechSynthesisUtterance();
                msg.text = text;
                msg.lang = 'en-US';
                msg.rate = 0.7;
                msg.pitch = 1.0;
                window.speechSynthesis.speak(msg);
                return;
            }
            showToast('Không có âm thanh cho thẻ này');
            return;
        }

        // Resolve relative
        let resolved = src;
        if (!/^https?:\/\//i.test(resolved) && resolved.indexOf('/') !== 0) {
            resolved = baseWeb.replace(/\/$/, '') + '/' + resolved.replace(/^\//, '');
        }

        // If we have an <audio> element, set its source then load+play safely
        if (audioEl) {
            try {
                const sEl = audioEl.querySelector('source');
                if (sEl) sEl.setAttribute('src', resolved);
                else audioEl.setAttribute('src', resolved);
                // load then play — use promise chain to avoid interrupted play
                audioEl.load();
                audioEl.pause();
                audioEl.currentTime = 0;
                audioEl.play().catch(err => {
                    console.warn('play failed after load', err);
                    // fallback to speechSynthesis if available
                    const textEl = document.getElementById('cardFrontText' + suffix) || document.getElementById('cardQuestion' + suffix) || document.getElementById('cardBackText' + suffix);
                    const text = textEl ? textEl.textContent.trim() : '';
                    if ('speechSynthesis' in window && text) {
                        window.speechSynthesis.cancel();
                        const msg = new SpeechSynthesisUtterance();
                        msg.text = text;
                        msg.lang = 'en-US';
                        msg.rate = 0.7;
                        msg.pitch = 1.0;
                        window.speechSynthesis.speak(msg);
                    } else {
                        showToast('Không thể phát âm thanh: ' + (err.message || ''));
                    }
                });
                return;
            } catch (e) {
                console.warn('audio element play error', e);
            }
        }

        // Fallback: use new Audio()
        try {
            const a = new Audio(resolved);
            a.play().catch(err => {
                console.warn('Audio() play failed', err);
                showToast('Không thể phát âm thanh');
            });
            return;
        } catch (e) {
            console.error(e);
            showToast('Lỗi khi phát âm thanh');
            return;
        }
    } catch (e) {
        console.error(e);
        showToast('Lỗi khi phát âm thanh');
    }
}


function toggleReverse() {
    const flashcard = document.getElementById('flashcard');
    flashcard.classList.toggle('reversed');
}


function submitAnswer() {
    const activeCard = document.getElementById('flashcard-type-3');
    if (!activeCard || activeCard.style.display === 'none') {
        return;
    }
    
    const userAnswer = activeCard.querySelector('#userAnswer-3');
    if (!userAnswer || !userAnswer.value.trim()) {
        alert('Bạn chưa nhập câu trả lời cho thẻ này');
        return;
    }
    
    
    const phase1 = activeCard.querySelector('#inputPhase1-3');
    const phase2 = activeCard.querySelector('#inputPhase2-3');
    
    if (phase1) phase1.style.display = 'none';
    if (phase2) phase2.style.display = 'block';
    
    
    const userAnswerDisplay = activeCard.querySelector('.user-answer-display-3');
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
    let flashcard = null;
    for (let type = 1; type <= 3; type++) {
        const card = document.getElementById('flashcard-type-' + type);
        if (card && card.style.display !== 'none') {
            flashcard = card;
            break;
        }
    }
    
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
    // Resolve any initial audio srcs and show play buttons when audio exists
    document.querySelectorAll('.card-audio').forEach(wrapper => {
        try {
            const audio = wrapper.querySelector('audio');
            if (audio) {
                const srcEl = audio.querySelector('source');
                let src = srcEl ? (srcEl.getAttribute('src') || srcEl.src) : (audio.getAttribute('src') || audio.src);
                if (src) {
                    if (!/^https?:\/\//i.test(src) && src.indexOf('/') !== 0) {
                        src = baseWeb.replace(/\/$/, '') + '/' + src.replace(/^\//, '');
                    }
                    if (srcEl) srcEl.src = src;
                    else audio.src = src;
                    // do not auto-load to avoid interrupting playback — load when user clicks play
                    // always show play button (TTS fallback will be used if no audio file)
                    const id = wrapper.id || '';
                    const match = id.match(/cardAudioWrap-(\d)$/);
                    if (match) {
                        const suffix = '-' + match[1];
                        const playBtn = document.getElementById('cardPlay' + suffix);
                        if (playBtn) playBtn.style.display = 'inline-block';
                    } else {
                        ['-1','-2','-3'].forEach(suffix => {
                            const playBtn = document.getElementById('cardPlay' + suffix);
                            if (playBtn) playBtn.style.display = 'inline-block';
                        });
                    }
                }
            }
        } catch(e) { console.warn(e); }
    });
    // completion modal buttons
    const btnReturn = document.getElementById('btnReturnPractice');
    const btnClose = document.getElementById('btnCloseCompletion');
    if (btnReturn) btnReturn.addEventListener('click', () => { window.location.href = '<?= Url::to(['site/practice']) ?>'; });
    if (btnClose) btnClose.addEventListener('click', () => { document.getElementById('completionModal').style.display = 'none'; });
});

function showCompletionModal(message) {
    const modal = document.getElementById('completionModal');
    if (!modal) return alert(message || 'Hoàn thành');
    const msgEl = document.getElementById('completionMessage');
    if (msgEl) msgEl.textContent = message;
    modal.style.display = 'flex';
}
</script>
