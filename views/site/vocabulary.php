<?php
/** @var yii\web\View $this */
/** @var app\models\Deck[] $decks */
/** @var app\models\Card[] $cards */
/** @var int $currentDeckId */
/** @var array $stats */

use yii\helpers\Url;
use yii\helpers\Html;

$this->title = 'Từ vựng - Andi';
/* Bổ sung lại file dashboard.css để load giao diện Sidebar */
$this->registerCssFile('@web/css/dashboard.css', ['depends' => [\app\assets\AppAsset::class]]);
$this->registerCssFile('@web/css/vocabulary.css', ['depends' => [\app\assets\AppAsset::class]]);
?>

<div class="dashboard">
    <!-- Sidebar -->
    <aside class="sidebar">
        <ul class="menu">
            <li><a href="<?= Url::to(['site/dashboard']) ?>"><img src="<?= Yii::getAlias('@web') ?>/icons/home.png" alt=""> Trang chủ</a></li>
            <li><a href="<?= Url::to(['site/vocabset']) ?>"><img src="<?= Yii::getAlias('@web') ?>/icons/vocabset.png" alt=""> Bộ thẻ</a></li>
            <li><a href="<?= Url::to(['site/vocabulary']) ?>" class="active"><img src="<?= Yii::getAlias('@web') ?>/icons/vocabulary.png" alt=""> Từ vựng</a></li>
            <li><a href="#"><img src="<?= Yii::getAlias('@web') ?>/icons/practice.png" alt=""> Luyện tập</a></li>
        </ul>
        <!-- Bổ sung Nút thu gọn và Nút Sáng/Tối -->
        <button class="toggle-btn">&laquo;</button>
        <div class="profile">
            <div class="avatar"><img src="<?= Yii::getAlias('@web') ?>/images/andi-avatar.png" alt="Avatar"></div>
            <p class="username">Nguyễn Văn A</p>
            <div class="profile-actions">
                <button class="btn-profile">Xem hồ sơ</button>
                <label class="theme-switch">
                    <input type="checkbox" id="darkModeToggle">
                    <span class="slider"></span>
                    <span class="label-text">Tối</span>
                </label>
            </div>
        </div>
    </aside>

    <main class="main">
        <!-- Khối thống kê -->
        <div class="vocab-stats-container">
            <div class="stat-box"><div class="stat-title">Tổng</div><div class="stat-value"><?= $stats['total'] ?></div></div>
            <div class="stat-box"><div class="stat-title">Thuộc</div><div class="stat-value"><?= $stats['memorized'] ?></div></div>
            <div class="stat-box"><div class="stat-title">Chưa</div><div class="stat-value"><?= $stats['learning'] ?></div></div>
            <div class="stat-box"><div class="stat-title">%</div><div class="stat-value"><?= $stats['percent'] ?>%</div></div>
        </div>

        <div class="vocab-main-board">
            <div class="vocab-controls">
                <select class="deck-filter-select" onchange="window.location.href='<?= Url::to(['site/vocabulary']) ?>&deck_id=' + this.value">
                    <option value="">Tất cả thẻ từ vựng</option>
                    <?php foreach ($decks as $deck): ?>
                        <option value="<?= $deck->deckid ?>" <?= $currentDeckId == $deck->deckid ? 'selected' : '' ?>><?= Html::encode($deck->name) ?></option>
                    <?php endforeach; ?>
                </select>
                <!-- Nút dấu cộng mở Pop-up thêm thẻ hàng loạt -->
                <button class="btn-add-huge" onclick="document.getElementById('modalAddBatch').style.display='flex'">+</button>
            </div>

            <table class="vocab-table">
                <thead>
                    <tr>
                        <th>Mặt trước</th>
                        <th>Mặt sau</th>
                        <th>Loại</th>
                        <th>Ví dụ</th>
                        <th>Phiên âm</th>
                        <th class="col-action" style="width: 90px; text-align: center;"></th>
                    </tr>
                </thead>
                <tbody id="vocabularyTableBody">
                    <?php if (empty($cards)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center; color: #999; padding: 40px;">Không có từ vựng nào để hiển thị.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($cards as $card): ?>
                            <tr id="row-card-<?= $card->cardid ?>">
                                <td style="font-weight: 700; color: #2d3748;"><?= Html::encode($card->frontcontent) ?></td>
                                <td><?= Html::encode($card->backcontent) ?></td>
                                <td>
                                    <?php if ($card->tags): ?>
                                        <?php foreach (explode(',', $card->tags) as $tag): ?>
                                            <?php 
                                                $tagStr = trim($tag);
                                                if ($tagStr === '') continue;
                                                
                                                $badgeClass = 'tag-pill';
                                                // Nếu là tag hệ thống thì có màu cam
                                                if (in_array($tagStr, ['Cơ bản', 'Đảo ngược', 'Nhập liệu'])) {
                                                    $badgeClass .= ' tag-system';
                                                }
                                            ?>
                                            <span class="<?= $badgeClass ?>"><?= Html::encode($tagStr) ?></span>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </td>
                                <td style="font-style: italic; color: #718096;"><?= Html::encode($card->examplesentence) ?></td>
                                <td style="font-family: monospace;"><?= Html::encode($card->pronunciation) ?></td>
                                <td class="col-action" style="width: 90px; text-align: center;">
                                    <!-- Nút ➕: Thêm vào bộ -->
                                    <button class="btn-action-add" onclick="openAssignModal(<?= $card->cardid ?>)" title="Thêm vào bộ thẻ khác">➕</button>
                                    
                                    <!-- Nút ❌: GỠ KHỎI BỘ (Chỉ làm mất liên kết với bộ thẻ hiện hành) -->
                                    <button class="btn-delete-card-table" onclick="removeFromDeck(<?= $card->cardid ?>)" title="Gỡ thẻ khỏi bộ này">&times;</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<!-- POP-UP 1: THÊM THẺ HÀNG LOẠT -->
<div id="modalAddBatch" class="modal-overlay" onclick="this.style.display='none'">
    <div class="modal-content-huge" onclick="event.stopPropagation()">
        <div class="modal-header-add">
            <div style="display:flex; align-items:center; gap:15px;">
                <span class="header-label">Thêm vào bộ:</span>
                <select id="batchDeckId" class="modal-select">
                    <?php foreach ($decks as $deck): ?>
                        <option value="<?= $deck->deckid ?>" <?= $currentDeckId == $deck->deckid ? 'selected' : '' ?>><?= Html::encode($deck->name) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="display:flex; align-items:center; gap:15px;">
                <span class="header-label">Kiểu:</span>
                <select id="batchCardType" class="modal-select">
                    <option value="1">Cơ bản (Trước -> Sau)</option>
                    <option value="2">Đảo ngược (Cả 2 mặt)</option>
                    <option value="3">Nhập câu trả lời</option>
                </select>
            </div>
            <button onclick="document.getElementById('modalAddBatch').style.display='none'" style="margin-left:auto; background:none; border:none; font-size:30px; cursor:pointer; color:#999;">&times;</button>
        </div>

        <div class="add-table-container">
            <table id="batchEntryTable">
                <thead>
                    <tr>
                        <th width="40">#</th>
                        <th>Mặt trước</th>
                        <th>Mặt sau</th>
                        <th>Phát âm</th>
                        <th>Ví dụ</th>
                        <th>Tags</th>
                        <th width="40"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="entry-row">
                        <td style="font-weight:800; color:#718096; text-align: center;">1</td>
                        <td><input type="text" class="in-front"></td>
                        <td><input type="text" class="in-back"></td>
                        <td><input type="text" class="in-pronun"></td>
                        <td><input type="text" class="in-example"></td>
                        <td><input type="text" class="in-tags" placeholder="noun, verb"></td>
                        <td><button onclick="removeEntryRow(this)">&times;</button></td>
                    </tr>
                </tbody>
            </table>
            <button class="btn-add-row-dashed" onclick="addEntryRow()">+ THÊM HÀNG MỚI</button>
        </div>

        <div class="modal-footer-add">
            <button class="btn-text-cancel" onclick="document.getElementById('modalAddBatch').style.display='none'">HỦY</button>
            <button class="btn-text-save" onclick="saveBatchCards()">LƯU TẤT CẢ</button>
        </div>
    </div>
</div>

<!-- POP-UP 2: CHỌN BỘ THẺ ĐỂ GẮN VÀO (Cho nút ➕) -->
<div id="modalAssignDeck" class="modal-overlay" onclick="this.style.display='none'">
    <div class="modal-content" style="max-width: 400px; text-align: center;" onclick="event.stopPropagation()">
        <h2 style="margin-bottom: 20px; font-size: 20px;">Thêm thẻ vào bộ</h2>
        <input type="hidden" id="assignCardId" value="">
        <select id="assignDeckId" class="modal-select" style="width: 100%; margin-bottom: 20px;">
            <option value="">-- Chọn bộ thẻ --</option>
            <?php foreach ($decks as $deck): ?>
                <option value="<?= $deck->deckid ?>"><?= Html::encode($deck->name) ?></option>
            <?php endforeach; ?>
        </select>
        <div style="display: flex; gap: 10px; justify-content: center; margin-top: 10px;">
            <button class="btn-text-cancel" onclick="document.getElementById('modalAssignDeck').style.display='none'">Hủy</button>
            <button class="btn-text-save" onclick="assignCardToDeck()">Lưu vào bộ</button>
        </div>
    </div>
</div>

<script>
// --- 1. CHỨC NĂNG GỠ THẺ KHỎI BỘ (KHÔNG XÓA VĨNH VIỄN) ---
function removeFromDeck(id) {
    if(!confirm('Bạn có chắc chắn muốn gỡ từ vựng này ra khỏi bộ thẻ? (Từ vựng vẫn sẽ nằm trong Kho chung)')) return;

    fetch('<?= Url::to(['site/ajax-remove-from-deck']) ?>&id=' + id, {
        method: 'POST',
        headers: { 'X-CSRF-Token': '<?= Yii::$app->request->csrfToken ?>' }
    })
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            const row = document.getElementById('row-card-' + id);
            row.style.transition = 'all 0.3s ease';
            row.style.opacity = '0';
            row.style.transform = 'translateX(20px)';
            
            setTimeout(() => {
                row.remove();
                // Báo hiệu và tải lại để cập nhật bảng thống kê trên đầu
                location.reload(); 
            }, 300);
        } else {
            alert('Không thể gỡ thẻ này. Vui lòng thử lại!');
        }
    });
}

// --- 2. CHỨC NĂNG THÊM THẺ VÀO BỘ (Nhân bản) ---
function openAssignModal(cardId) {
    document.getElementById('assignCardId').value = cardId;
    document.getElementById('modalAssignDeck').style.display = 'flex';
}

function assignCardToDeck() {
    const cardId = document.getElementById('assignCardId').value;
    const deckId = document.getElementById('assignDeckId').value;

    if(!deckId) return alert('Vui lòng chọn một bộ thẻ!');

    fetch('<?= Url::to(['site/ajax-assign-card-to-deck']) ?>', {
        method: 'POST',
        headers: { 
            'Content-Type': 'application/x-www-form-urlencoded', 
            'X-CSRF-Token': '<?= Yii::$app->request->csrfToken ?>' 
        },
        body: new URLSearchParams({ cardId: cardId, deckId: deckId })
    })
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            alert('Đã thêm thẻ vào bộ bài thành công!');
            document.getElementById('modalAssignDeck').style.display = 'none';
        } else {
            alert(data.message || 'Có lỗi xảy ra!');
        }
    });
}

// --- 3. CHỨC NĂNG POP-UP THÊM NHIỀU HÀNG ---
function addEntryRow() {
    const tbody = document.querySelector('#batchEntryTable tbody');
    const index = tbody.children.length + 1;
    const tr = document.createElement('tr');
    tr.className = 'entry-row';
    tr.innerHTML = `
        <td style="font-weight:800; color:#718096; text-align: center;">${index}</td>
        <td><input type="text" class="in-front"></td>
        <td><input type="text" class="in-back"></td>
        <td><input type="text" class="in-pronun"></td>
        <td><input type="text" class="in-example"></td>
        <td><input type="text" class="in-tags"></td>
        <td><button onclick="removeEntryRow(this)">&times;</button></td>
    `;
    tbody.appendChild(tr);
}

function removeEntryRow(btn) {
    const tbody = document.querySelector('#batchEntryTable tbody');
    if (tbody.children.length > 1) {
        btn.closest('tr').remove();
        document.querySelectorAll('.entry-row').forEach((tr, i) => {
            tr.cells[0].innerText = i + 1;
        });
    }
}

// --- 4. CHỨC NĂNG LƯU NHIỀU THẺ CÙNG LÚC ---
function saveBatchCards() {
    const deckId = document.getElementById('batchDeckId').value;
    const cardType = document.getElementById('batchCardType').value;
    const rows = document.querySelectorAll('.entry-row');
    const cards = [];

    rows.forEach(row => {
        const front = row.querySelector('.in-front').value.trim();
        const back = row.querySelector('.in-back').value.trim();
        if (front && back) {
            cards.push({
                front: front,
                back: back,
                pronunciation: row.querySelector('.in-pronun').value,
                example: row.querySelector('.in-example').value,
                tags: row.querySelector('.in-tags').value
            });
        }
    });

    if (cards.length === 0) return alert('Vui lòng nhập nội dung cho ít nhất 1 thẻ!');

    fetch('<?= Url::to(['site/ajax-save-batch-cards']) ?>', {
        method: 'POST',
        headers: { 
            'Content-Type': 'application/x-www-form-urlencoded', 
            'X-CSRF-Token': '<?= Yii::$app->request->csrfToken ?>' 
        },
        body: new URLSearchParams({ 
            deckId: deckId, 
            cardType: cardType, 
            cards: JSON.stringify(cards) 
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('Đã lưu thành công!');
            window.location.href = '<?= Url::to(['site/vocabulary']) ?>&deck_id=' + deckId;
        } else {
            alert('Lỗi: ' + data.message);
        }
    });
}
</script>