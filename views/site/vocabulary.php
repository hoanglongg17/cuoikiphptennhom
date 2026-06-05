<?php







/** @var yii\web\View $this */
/** @var array $srsByLevel */
/** @var app\models\Deck[] $decks */
/** @var int $currentDeckId */
/** @var app\models\Card[] $cards */

use yii\helpers\Url;
use yii\helpers\Html;

$this->title = 'Từ vựng - Andi';


$user = Yii::$app->user->identity;

$this->registerCssFile('@web/css/dashboard.css', ['depends' => [\app\assets\AppAsset::class]]);
$this->registerCssFile('@web/css/vocabulary.css', ['depends' => [\app\assets\AppAsset::class]]);
?>

<section class="srs-section">
    <h2>📚 Phân bố từ vựng theo cấp độ</h2>
    <p class="srs-description">Lịch ôn tập theo Hệ thống Lặp lại Ngắt quãng (SRS)</p>
    
    <div class="srs-grid">
        <?php foreach($srsByLevel as $level => $data): ?>
            <div class="srs-card" style="border-left: 4px solid <?= $data['color'] ?>;">
                <div class="srs-name"><?= Html::encode($data['name']) ?></div>
                <div class="srs-count" style="color: <?= $data['color'] ?>;"><?= $data['count'] ?> từ</div>
                <div class="srs-next">
                    <?php if ($data['nextReview'] && $level != 5): ?>
                        <span class="next-review"><?= Html::encode($data['nextReview']) ?></span>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<div class="vocab-main-board">
    <div class="vocab-controls">
        <select class="deck-filter-select" onchange="var url = '<?= Url::to(['site/vocabulary']) ?>'; window.location.href = url + (url.indexOf('?') !== -1 ? '&' : '?') + 'deck_id=' + this.value">
            <option value="">Tất cả thẻ từ vựng</option>
            <?php foreach ($decks as $deck): ?>
                <option value="<?= $deck->deckid ?>" <?= $currentDeckId == $deck->deckid ? 'selected' : '' ?>><?= Html::encode($deck->name) ?></option>
            <?php endforeach; ?>
        </select>
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
                        <td style="font-weight: 700;"><?= Html::encode($card->frontcontent) ?></td>
                        <td><?= Html::encode($card->backcontent) ?></td>
                        <td>
                            <?php if ($card->tags): ?>
                                <?php foreach (explode(',', $card->tags) as $tag): ?>
                                    <?php 
                                        $tagStr = trim($tag);
                                        if ($tagStr === '') continue;
                                        $badgeClass = 'tag-pill';
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
                        <td class="col-action">
                             <div style="display: flex; justify-content: center; gap: 8px; align-items: center; min-width: 120px;">
                                
                                <button class="btn-action-edit" 
                                        onclick="openEditCardModal(<?= htmlspecialchars(json_encode($card->attributes)) ?>)" 
                                        title="Sửa từ vựng" 
                                        style="background: #e3f2fd; border: 1px solid #bbdefb; color: #1976d2; cursor: pointer; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; transition: all 0.2s;">
                                    ✏️
                                </button>
                                
                                
                                <button class="btn-action-add" 
                                        onclick="openAssignModal(<?= $card->cardid ?>)" 
                                        title="Thêm vào bộ"
                                        style="background: #e6fffa; border: 1px solid #b2f5ea; color: #319795; cursor: pointer; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                    ➕
                                </button>

                                
                                <button class="btn-delete-card-table" 
                                        onclick="deleteCard(<?= $card->cardid ?>)" 
                                        title="Xóa vĩnh viễn"
                                        style="background: #fff5f5; border: 1px solid #fed7d7; color: #fc8181; cursor: pointer; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                    &times;
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

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

<div id="modalEditCard" class="modal-overlay" onclick="this.style.display='none'">
    <div class="modal-content" style="max-width: 550px;" onclick="event.stopPropagation()">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 10px;">
            <h2 style="margin: 0; font-size: 20px; color: #2d3748;">Chỉnh sửa từ vựng</h2>
            <button onclick="document.getElementById('modalEditCard').style.display='none'" style="background:none; border:none; font-size:24px; cursor:pointer; color:#aaa;">&times;</button>
        </div>
        
        <input type="hidden" id="editCardId">
        
        <div style="margin-bottom: 15px;">
            <label style="display:block; font-weight:700; margin-bottom:5px; font-size:14px; color:#4a5568;">Mặt trước (Từ vựng/Câu hỏi)</label>
            <input type="text" id="editFront" style="width:100%; padding:10px; border:1px solid #e2e8f0; border-radius:8px; font-family:'Nunito'; outline:none;">
        </div>

        <div style="margin-bottom: 15px;">
            <label style="display:block; font-weight:700; margin-bottom:5px; font-size:14px; color:#4a5568;">Mặt sau (Nghĩa/Đáp án)</label>
            <input type="text" id="editBack" style="width:100%; padding:10px; border:1px solid #e2e8f0; border-radius:8px; font-family:'Nunito'; outline:none;">
        </div>

        <div style="margin-bottom: 15px;">
            <label style="display:block; font-weight:700; margin-bottom:5px; font-size:14px; color:#4a5568;">Phiên âm</label>
            <input type="text" id="editPronun" style="width:100%; padding:10px; border:1px solid #e2e8f0; border-radius:8px; font-family:'Nunito'; outline:none;">
        </div>

        <div style="margin-bottom: 15px;">
            <label style="display:block; font-weight:700; margin-bottom:5px; font-size:14px; color:#4a5568;">Ví dụ minh họa</label>
            <textarea id="editExample" rows="3" style="width:100%; padding:10px; border:1px solid #e2e8f0; border-radius:8px; font-family:'Nunito'; outline:none; resize:none;"></textarea>
        </div>

        <div style="margin-bottom: 25px;">
            <label style="display:block; font-weight:700; margin-bottom:5px; font-size:14px; color:#4a5568;">Tags (ngăn cách bằng dấu phẩy)</label>
            <input type="text" id="editTags" placeholder="Ví dụ: ielts, verb, essential" style="width:100%; padding:10px; border:1px solid #e2e8f0; border-radius:8px; font-family:'Nunito'; outline:none;">
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 15px;">
            <button class="btn-text-cancel" onclick="document.getElementById('modalEditCard').style.display='none'">HỦY BỎ</button>
            <button class="btn-text-save" onclick="updateCardData()" style="background: #3182ce; color: white; padding: 8px 25px; border-radius: 8px;">LƯU THAY ĐỔI</button>
        </div>
    </div>
</div>

<script>


function openEditCardModal(cardData) {
    
    document.getElementById('editCardId').value = cardData.cardid;
    document.getElementById('editFront').value = cardData.frontcontent;
    document.getElementById('editBack').value = cardData.backcontent;
    document.getElementById('editPronun').value = cardData.pronunciation || '';
    document.getElementById('editExample').value = cardData.examplesentence || '';
    document.getElementById('editTags').value = cardData.tags || '';
    
    
    document.getElementById('modalEditCard').style.display = 'flex';
}

function updateCardData() {
    const id = document.getElementById('editCardId').value;
    const front = document.getElementById('editFront').value.trim();
    const back = document.getElementById('editBack').value.trim();
    const pronun = document.getElementById('editPronun').value.trim();
    const example = document.getElementById('editExample').value.trim();
    const tags = document.getElementById('editTags').value.trim();

    if(!front || !back) return alert('Mặt trước và mặt sau không được để trống!');

    fetch('<?= Url::to(['site/ajax-update-card']) ?>', {
        method: 'POST',
        headers: { 
            'Content-Type': 'application/x-www-form-urlencoded', 
            'X-CSRF-Token': '<?= Yii::$app->request->csrfToken ?>' 
        },
        body: new URLSearchParams({
            cardid: id,
            frontcontent: front,
            backcontent: back,
            pronunciation: pronun,
            examplesentence: example,
            tags: tags
        })
    })
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            alert('Đã cập nhật từ vựng thành công!');
            location.reload(); 
        } else {
            alert('Lỗi: ' + data.message);
        }
    })
    .catch(err => alert('Lỗi kết nối máy chủ!'));
}

function deleteCard(id) {
    if(!confirm('Bạn có chắc chắn muốn XÓA VĨNH VIỄN thẻ này?')) return;

    fetch('<?= Url::to(['site/ajax-delete-card']) ?>', { 
        method: 'POST',
        headers: { 
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-CSRF-Token': '<?= Yii::$app->request->csrfToken ?>' 
        },
        body: new URLSearchParams({ id: id })
    })
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            const row = document.getElementById('row-card-' + id);
            row.style.opacity = '0';
            setTimeout(() => { row.remove(); }, 300);
        } else {
            alert('Lỗi: ' + data.message);
        }
    });
}

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
            location.reload();
        } else {
            alert('Lỗi: ' + data.message);
        }
    });
}
</script>