<?php
/** @var yii\web\View $this */
/** @var app\models\Deck[] $decks */
/** @var array $deckQuotas */

use yii\helpers\Url;
use yii\helpers\Html;

$this->title = 'Quản lý Bộ thẻ - Andi';

// Lấy thông tin người dùng đang đăng nhập
$user = Yii::$app->user->identity;

$this->registerCssFile('@web/css/dashboard.css', ['depends' => [\app\assets\AppAsset::class]]);
$this->registerCssFile('@web/css/vocabset.css', ['depends' => [\app\assets\AppAsset::class]]);
?>

        <div class="vocabset-header">
            <h1>Quản lý Bộ thẻ</h1>
            
            <div class="vocabset-actions">
                <div class="share-import-group">
                    <input type="text" id="importDeckId" placeholder="Nhập ID bộ bài..." class="search-input-mini">
                    <button class="btn-import-share" onclick="importDeck()">Nhận bộ bài</button>
                </div>

                <div class="search-container">
                    <input type="text" id="searchInput" placeholder="Tìm kiếm bộ bài..." class="search-input">
                </div>
                <button class="btn-create-set" onclick="openModal('modalCreate')">+ Tạo mới</button>
            </div>
        </div>

        <div class="set-list" id="deckListContainer">
            <?php foreach ($decks as $deck): ?>
                <?php 
                    $n = 0; $l = 0; $r = 0;
                    $today = date('Y-m-d');
                    $newQuotaRemaining = $deckQuotas[$deck->deckid]['newRemaining'] ?? 20;
                    $reviewQuotaRemaining = $deckQuotas[$deck->deckid]['reviewRemaining'] ?? 200;
                    
                    foreach($deck->cards as $c) {
                        $s = $c->progress ? $c->progress->status : 0;
                        
                        if ($s == 0 && $newQuotaRemaining > 0) {
                            // Thẻ mới còn quota
                            $n++;
                            $newQuotaRemaining--;
                        } elseif ($s == 1) {
                            // Thẻ đang học (không quota limit)
                            $l++;
                        } elseif ($s == 2 && $c->progress && strtotime($c->progress->duedate) <= strtotime($today . ' 23:59:59') && $reviewQuotaRemaining > 0) {
                            // Thẻ ôn due hôm nay, còn quota
                            $r++;
                            $reviewQuotaRemaining--;
                        }
                    }
                ?>
                <div class="set-row deck-item" data-name="<?= strtolower(Html::encode($deck->name)) ?>" onclick="openModal('modalView-<?= $deck->deckid ?>')">
                    <div class="set-main-info">
                        <div class="set-icon-box">📂</div>
                        <div class="set-details">
                            <h3><?= Html::encode($deck->name) ?></h3>
                            <span><?= count($deck->cards) ?> thẻ</span>
                        </div>
                    </div>

                    <div class="set-stats">
                        <div class="stat-item stat-new"><span class="stat-number"><?= $n ?></span><span class="stat-label">Mới</span></div>
                        <div class="stat-item stat-learning"><span class="stat-number"><?= $l ?></span><span class="stat-label">Đang học</span></div>
                        <div class="stat-item stat-review"><span class="stat-number"><?= $r ?></span><span class="stat-label">Ôn tập</span></div>
                        
                        <button class="btn-share-id" onclick="event.stopPropagation(); shareId(<?= $deck->deckid ?>)" title="Copy ID để chia sẻ">🔗</button>
                        
                        <button class="btn-edit-trigger" onclick="event.stopPropagation(); openModal('modalEdit-<?= $deck->deckid ?>')">✏️</button>
                    </div>
                </div>

                <!-- POP-UP XEM CHI TIẾT BỘ THẺ -->
                <div id="modalView-<?= $deck->deckid ?>" class="modal-overlay" onclick="closeModal(this)">
                    <div class="modal-content" onclick="event.stopPropagation()">
                        <div class="modal-header">
                            <h2>Chi tiết bộ thẻ (ID: <?= $deck->deckid ?>)</h2>
                            <button class="btn-close-modal" onclick="closeModalById('modalView-<?= $deck->deckid ?>')">&times;</button>
                        </div>
                        
                        <div class="deck-top-info">
                            <h3 style="margin: 0 0 10px 0; font-size: 22px; color: #2b6cb0;">
                                <span class="deck-info-label">Tên bộ thẻ:</span> <?= Html::encode($deck->name) ?>
                            </h3>
                            <p style="margin: 0; color: #4a5568; font-size: 16px; line-height: 1.6;">
                                <span class="deck-info-label">Mô tả:</span> <?= Html::encode($deck->description) ?: 'Chưa có mô tả cho bộ thẻ này.' ?>
                            </p>
                        </div>

                        <hr class="deck-divider">

                        <div class="cards-area">
                            <h4 style="margin-top: 0; margin-bottom: 20px; color: #2d3748; font-size: 18px;">
                                Danh sách thẻ từ vựng (<span id="cardCount-<?= $deck->deckid ?>"><?= count($deck->cards) ?></span> thẻ)
                            </h4>
                            
                            <?php if (empty($deck->cards)): ?>
                                <p style="text-align: center; color: #a0aec0; padding: 20px;">Không có từ vựng nào trong bộ thẻ này.</p>
                            <?php else: ?>
                                <?php foreach($deck->cards as $card): ?>
                                    <div class="card-row-display" id="card-row-<?= $card->cardid ?>">
                                        <div class="card-main-content">
                                            <div class="content-part"><label>Mặt trước</label><div class="content-text" style="color:#3182ce;"><?= Html::encode($card->frontcontent) ?></div></div>
                                            <div class="content-part"><label>Mặt sau</label><div class="content-text"><?= Html::encode($card->backcontent) ?></div></div>
                                        </div>
                                        <div class="card-meta-info">
                                            <div class="meta-item"><strong>Phiên âm:</strong> <?= Html::encode($card->pronunciation) ?: 'N/A' ?></div>
                                            <div style="width:100%; margin-top:5px;"><strong>Ví dụ:</strong> <em style="color: #718096;">"<?= Html::encode($card->examplesentence) ?: 'Chưa có ví dụ' ?>"</em></div>
                                            <div style="flex-grow:1; text-align:right;">
                                                <span class="status-badge status-<?= $card->progress ? $card->progress->status : 0 ?>">
                                                    <?= $card->progress ? ($card->progress->status == 0 ? 'Mới' : ($card->progress->status == 1 ? 'Đang học' : 'Ôn tập')) : 'Mới' ?>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="card-actions-inner" style="margin-top: 15px; display: flex; justify-content: flex-end;">
                                            <button onclick="removeFromDeck(<?= $card->cardid ?>, <?= $deck->deckid ?>)" style="padding: 6px 15px; border: 1px solid #f44336; color: #f44336; background: #fff5f5; border-radius: 8px; cursor: pointer; font-weight: bold; font-family: 'Nunito'; transition: all 0.2s;">
                                                ✖ Gỡ khỏi bộ
                                            </button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- POP-UP SỬA THÔNG TIN BỘ THẺ -->
                <div id="modalEdit-<?= $deck->deckid ?>" class="modal-overlay" onclick="closeModal(this)">
                    <div class="modal-content" onclick="event.stopPropagation()">
                        <div class="modal-header">
                            <h2>Chỉnh sửa bộ thẻ</h2>
                            <button class="btn-close-modal" onclick="closeModalById('modalEdit-<?= $deck->deckid ?>')">&times;</button>
                        </div>
                        <div class="input-group">
                            <label>Tên bộ thẻ</label>
                            <input type="text" id="edit-name-<?= $deck->deckid ?>" class="full-input" value="<?= Html::encode($deck->name) ?>">
                        </div>
                        <div class="input-group">
                            <label>Mô tả bộ thẻ</label>
                            <textarea id="edit-desc-<?= $deck->deckid ?>" class="full-input" rows="5"><?= Html::encode($deck->description) ?></textarea>
                        </div>
                        <div style="display:flex; justify-content: space-between; margin-top: 20px;">
                            <button class="btn-delete-deck" onclick="deleteDeck(<?= $deck->deckid ?>)">Xóa bộ bài</button>
                            <button class="btn-save" onclick="updateDeck(<?= $deck->deckid ?>)">Lưu thay đổi</button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

<!-- POP-UP TẠO MỚI BỘ THẺ -->
<div id="modalCreate" class="modal-overlay" onclick="closeModal(this)">
    <div class="modal-content" onclick="event.stopPropagation()">
        <div class="modal-header">
            <h2>Tạo bộ thẻ mới</h2>
            <button class="btn-close-modal" onclick="closeModalById('modalCreate')">&times;</button>
        </div>
        <div class="input-group">
            <label>Tên bộ thẻ</label>
            <input type="text" id="create-deck-name" class="full-input" placeholder="Ví dụ: Tiếng Anh giao tiếp">
        </div>
        <div class="input-group">
            <label>Mô tả bộ thẻ</label>
            <textarea id="create-deck-desc" class="full-input" rows="4" placeholder="Nhập mục đích bộ thẻ này..."></textarea>
        </div>
        <button class="btn-save" style="width:100%; margin-top:10px;" onclick="createNewDeck()">Xác nhận tạo</button>
    </div>
</div>

<script>
function openModal(id) { document.getElementById(id).style.display = 'flex'; document.body.style.overflow = 'hidden'; }
function closeModal(overlay) { overlay.style.display = 'none'; document.body.style.overflow = 'auto'; }
function closeModalById(id) { document.getElementById(id).style.display = 'none'; document.body.style.overflow = 'auto'; }

function shareId(id) {
    const tempInput = document.createElement("input");
    tempInput.value = id;
    document.body.appendChild(tempInput);
    tempInput.select();
    document.execCommand("copy");
    document.body.removeChild(tempInput);
    alert("Đã copy ID: " + id + ". Hãy gửi số này cho bạn bè nhé!");
}

function importDeck() {
    const id = document.getElementById('importDeckId').value.trim();
    if(!id) return alert("Vui lòng nhập ID bộ bài!");

    fetch('<?= Url::to(['site/ajax-import-deck']) ?>', {
        method: 'POST',
        headers: { 
            'Content-Type': 'application/x-www-form-urlencoded', 
            'X-CSRF-Token': '<?= Yii::$app->request->csrfToken ?>' 
        },
        body: new URLSearchParams({ deckId: id })
    })
    .then(res => res.json())
    .then(data => {
        alert(data.message);
        if(data.success) location.reload();
    });
}

document.getElementById('searchInput').addEventListener('input', function(e) {
    const term = e.target.value.toLowerCase();
    const decks = document.querySelectorAll('.deck-item');
    decks.forEach(deck => {
        const name = deck.getAttribute('data-name');
        deck.style.display = name.includes(term) ? 'flex' : 'none';
    });
});

function createNewDeck() {
    const name = document.getElementById('create-deck-name').value;
    const desc = document.getElementById('create-deck-desc').value;
    if(!name) return alert('Vui lòng nhập tên bộ thẻ!');
    fetch('<?= Url::to(['site/ajax-create-deck']) ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-Token': '<?= Yii::$app->request->csrfToken ?>' },
        body: new URLSearchParams({ name: name, description: desc })
    }).then(res => res.json()).then(data => { if(data.success) location.reload(); });
}

function updateDeck(id) {
    const name = document.getElementById('edit-name-' + id).value;
    const desc = document.getElementById('edit-desc-' + id).value;
    fetch('<?= Url::to(['site/ajax-update-deck']) ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-Token': '<?= Yii::$app->request->csrfToken ?>' },
        body: new URLSearchParams({ id: id, name: name, description: desc })
    }).then(res => res.json()).then(data => { if(data.success) location.reload(); });
}

function deleteDeck(id) {
    if(!confirm("Bạn có chắc chắn muốn xóa bộ bài này không?")) return;
    fetch('<?= Url::to(['site/ajax-delete-deck']) ?>', { 
        method: 'POST', 
        headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-Token': '<?= Yii::$app->request->csrfToken ?>' },
        body: new URLSearchParams({ id: id })
    })
    .then(res => res.json()).then(data => { if(data.success) location.reload(); });
}

function removeFromDeck(cardId, deckId) {
    if(!confirm('Bạn có chắc chắn muốn gỡ từ vựng này ra khỏi bộ thẻ? (Từ vựng vẫn nằm trong mục Từ Vựng chung)')) return;

    fetch('<?= Url::to(['site/ajax-remove-from-deck']) ?>', { 
        method: 'POST', 
        headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-Token': '<?= Yii::$app->request->csrfToken ?>' },
        body: new URLSearchParams({ id: cardId })
    })
    .then(res => res.json())
    .then(data => { 
        if(data.success) {
            const row = document.getElementById('card-row-' + cardId);
            if(row) {
                row.style.transition = 'all 0.3s ease';
                row.style.opacity = '0';
                row.style.transform = 'translateX(30px)';
                setTimeout(() => {
                    row.remove();
                    const countEl = document.getElementById('cardCount-' + deckId);
                    if (countEl) countEl.innerText = parseInt(countEl.innerText) - 1;
                }, 300);
            } else {
                location.reload();
            }
        } else {
            alert('Có lỗi xảy ra, không thể gỡ thẻ!');
        }
    })
    .catch(err => {
        console.error(err);
        alert('Lỗi kết nối máy chủ!');
    });
}
</script>