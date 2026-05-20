<?php
/** @var yii\web\View $this */
/** @var app\models\BlogPost[] $posts */
/** @var string $currentStatus */
/** @var string $keyword */

use yii\helpers\Url;
use yii\helpers\Html;
use app\models\BlogPost;

$this->title = 'Quản Lý Bài Viết Blog';
$this->params['breadcrumbs'][] = ['label' => 'Admin', 'url' => ['admin/dashboard']];
$this->params['breadcrumbs'][] = 'Bài Viết';
?>

<div class="admin-blog-list">
    <div class="page-header">
        <h1>📚 Quản Lý Bài Viết Blog</h1>
        <a href="<?= Url::to(['admin/blog-create']) ?>" class="btn btn-primary">
            ✍️ Tạo Bài Viết Mới
        </a>
    </div>

    <?php if (Yii::$app->session->hasFlash('success')): ?>
        <div class="alert alert-success">
            <?= Yii::$app->session->getFlash('success') ?>
        </div>
    <?php endif; ?>

    <!-- Search Form -->
    <div class="admin-search-section">
        <form method="get" class="admin-search-form">
            <div class="search-input-group">
                <input type="text" name="q" class="form-control" placeholder="🔍 Tìm kiếm theo tiêu đề, nội dung..." 
                       value="<?= Html::encode($keyword) ?>" />
                <button type="submit" class="btn btn-primary">Tìm</button>
                <?php if (!empty($keyword)): ?>
                    <a href="<?= Url::to(['admin/blog-list']) ?>" class="btn btn-secondary">Xóa</a>
                <?php endif; ?>
                
                <!-- Preserve status filter when searching -->
                <?php if (!empty($currentStatus)): ?>
                    <input type="hidden" name="status" value="<?= Html::encode($currentStatus) ?>" />
                <?php endif; ?>
            </div>
            <?php if (!empty($keyword) && empty($currentStatus)): ?>
                <p class="search-result-info">Kết quả tìm kiếm cho: <strong>"<?= Html::encode($keyword) ?>"</strong></p>
            <?php elseif (!empty($keyword) && !empty($currentStatus)): ?>
                <p class="search-result-info">Kết quả tìm kiếm cho: <strong>"<?= Html::encode($keyword) ?>"</strong> (<?= $currentStatus ?>)</p>
            <?php endif; ?>
        </form>
    </div>

    <!-- Status Filter -->
    <div class="filter-section">
        <h3>Lọc theo Trạng Thái:</h3>
        <div class="filter-buttons">
            <a href="<?= Url::to(['admin/blog-list']) ?>" 
               class="btn <?= empty($currentStatus) ? 'btn-primary' : 'btn-outline' ?>">
                Tất Cả
            </a>
            <a href="<?= Url::to(['admin/blog-list', 'status' => BlogPost::STATUS_PENDING]) ?>" 
               class="btn <?= $currentStatus === BlogPost::STATUS_PENDING ? 'btn-primary' : 'btn-outline' ?>">
                ⏳ Chờ Duyệt
            </a>
            <a href="<?= Url::to(['admin/blog-list', 'status' => BlogPost::STATUS_PUBLISHED]) ?>" 
               class="btn <?= $currentStatus === BlogPost::STATUS_PUBLISHED ? 'btn-primary' : 'btn-outline' ?>">
                ✅ Đã Đăng
            </a>
            <a href="<?= Url::to(['admin/blog-list', 'status' => BlogPost::STATUS_ARCHIVED]) ?>" 
               class="btn <?= $currentStatus === BlogPost::STATUS_ARCHIVED ? 'btn-primary' : 'btn-outline' ?>">
                🗂️ Lưu Trữ
            </a>
            <a href="<?= Url::to(['admin/blog-list', 'status' => BlogPost::STATUS_DENIED]) ?>" 
               class="btn <?= $currentStatus === BlogPost::STATUS_DENIED ? 'btn-primary' : 'btn-outline' ?>">
                ❌ Từ Chối
            </a>
        </div>
    </div>

    <!-- Blog List Table -->
    <div class="blog-list-table">
        <?php if (empty($posts)): ?>
            <div class="alert alert-info">
                <p><?php 
                    if (!empty($keyword) && empty($currentStatus)) {
                        echo 'Không tìm thấy bài viết nào với từ khóa "' . Html::encode($keyword) . '"';
                    } elseif (!empty($keyword) && !empty($currentStatus)) {
                        echo 'Không tìm thấy bài viết nào với từ khóa "' . Html::encode($keyword) . '" trong trạng thái "' . $currentStatus . '"';
                    } else {
                        echo 'Không có bài viết nào' . ($currentStatus ? ' với trạng thái "' . $currentStatus . '"' : '');
                    }
                ?></p>
            </div>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 40%;">Tiêu Đề</th>
                        <th style="width: 15%;">Tác Giả</th>
                        <th style="width: 10%;">Trạng Thái</th>
                        <th style="width: 8%;">Lượt Xem</th>
                        <th style="width: 8%;">Bình Luận</th>
                        <th style="width: 12%;">Ngày</th>
                        <th style="width: 7%;">Hành Động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($posts as $post): ?>
                        <tr class="<?= $post->status === 'draft' ? 'draft-row' : '' ?>">
                            <td>
                                <strong><?= Html::encode($post->title) ?></strong>
                                <?php if ($post->sharedeckid): ?>
                                    <br><small class="text-muted">🎴 <?= Html::encode($post->sharedDeck->name) ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?= Html::encode($post->author->displayname) ?></td>
                            <td>
                                <?php 
                                $statusLabels = [
                                    BlogPost::STATUS_PENDING => '<span class="badge badge-warning">⏳ Chờ Duyệt</span>',
                                    BlogPost::STATUS_PUBLISHED => '<span class="badge badge-success">✅ Đã Đăng</span>',
                                    BlogPost::STATUS_ARCHIVED => '<span class="badge badge-secondary">🗂️ Lưu Trữ</span>',
                                    BlogPost::STATUS_DENIED => '<span class="badge badge-danger">❌ Từ Chối</span>',
                                ];
                                echo isset($statusLabels[$post->status]) ? $statusLabels[$post->status] : Html::encode($post->status);
                                ?>
                                <?php if ($post->isDenied() && $post->getRejectionReason()): ?>
                                    <div class="text-muted" style="margin-top: 6px; font-size: 0.85em; max-width: 250px;">
                                        <strong>Lý do:</strong> <?= Html::encode($post->getRejectionReason()) ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>👁️ <?= $post->views ?></td>
                            <td>
                                💬 <?php 
                                $commentCount = \app\models\BlogNestedComment::find()
                                    ->where(['postid' => $post->postid, 'status' => \app\models\BlogNestedComment::STATUS_APPROVED])
                                    ->count();
                                echo $commentCount;
                                ?>
                            </td>
                            <td><?= Yii::$app->formatter->asDate($post->createdat, 'php:d/m/Y H:i') ?></td>
                            <td>
                                <div class="action-buttons">
                                    <?php if ($post->isPublished()): ?>
                                        <a href="<?= Url::to(['blog/view', 'slug' => $post->slug]) ?>" 
                                           class="btn btn-sm btn-info" title="Xem" target="_blank">👁️</a>
                                        <form method="post" action="<?= Url::to(['admin/blog-archive', 'id' => $post->postid]) ?>" style="display:inline;">
                                            <input type="hidden" name="_csrf" value="<?= Yii::$app->request->csrfToken ?>" />
                                            <button type="submit" class="btn btn-sm btn-secondary" title="Lưu trữ">🗂️</button>
                                        </form>
                                    <?php elseif ($post->status === BlogPost::STATUS_ARCHIVED): ?>
                                        <form method="post" action="<?= Url::to(['admin/blog-unarchive', 'id' => $post->postid]) ?>" style="display:inline;">
                                            <input type="hidden" name="_csrf" value="<?= Yii::$app->request->csrfToken ?>" />
                                            <button type="submit" class="btn btn-sm btn-success" title="Đưa về xuất bản">✅</button>
                                        </form>
                                    <?php elseif ($post->status === BlogPost::STATUS_PENDING): ?>
                                        <form method="post" action="<?= Url::to(['admin/blog-approve', 'id' => $post->postid]) ?>" style="display:inline;">
                                            <input type="hidden" name="_csrf" value="<?= Yii::$app->request->csrfToken ?>" />
                                            <button type="submit" class="btn btn-sm btn-success" title="Duyệt">✅</button>
                                        </form>
                                        <form id="reject-form-<?= $post->postid ?>" method="post" action="<?= Url::to(['admin/blog-reject', 'id' => $post->postid]) ?>" style="display:inline;">
                                            <input type="hidden" name="_csrf" value="<?= Yii::$app->request->csrfToken ?>" />
                                            <input type="hidden" name="rejectionreason" value="" />
                                            <button type="button" class="btn btn-sm btn-danger" onclick="rejectPost(<?= $post->postid ?>)" title="Từ chối">❌</button>
                                        </form>
                                    <?php endif; ?>
                                    
                                    <?php if ($post->isPublished()): ?>
                                        <button class="btn btn-sm btn-pin" 
                                                onclick="togglePin(this, <?= $post->postid ?>)"
                                                title="<?= $post->is_pinned ? 'Bỏ ghim' : 'Ghim bài viết' ?>"
                                                data-pinned="<?= $post->is_pinned ? 'true' : 'false' ?>">
                                            <?= $post->is_pinned ? '📌' : '📍' ?>
                                        </button>
                                    <?php endif; ?>
                                    
                                    <a href="<?= Url::to(['admin/blog-edit', 'id' => $post->postid]) ?>" 
                                       class="btn btn-sm btn-warning" title="Chỉnh sửa">✏️</a>
                                    
                                    <?= Html::beginForm(['admin/blog-delete', 'id' => $post->postid], 'post', 
                                        ['style' => 'display: inline;']) ?>
                                        <?= Html::submitButton('🗑️', [
                                            'class' => 'btn btn-sm btn-danger',
                                            'title' => 'Xóa',
                                            'onclick' => 'return confirm("Bạn chắc chắn muốn xóa bài viết này?");'
                                        ]) ?>
                                    <?= Html::endForm() ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<style>
.admin-blog-list {
    max-width: 1400px;
    margin: 0 auto;
    padding: 20px;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 2px solid #f0f0f0;
}

.page-header h1 {
    margin: 0;
}

/* Admin Search Form Styles */
.admin-search-section {
    background: #f0f8ff;
    border: 1px solid #b3d9ff;
    border-radius: 6px;
    padding: 15px;
    margin-bottom: 20px;
}

.admin-search-form {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.search-input-group {
    display: flex;
    gap: 8px;
    align-items: center;
}

.search-input-group .form-control {
    flex: 1;
    padding: 10px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 0.95em;
}

.search-input-group .form-control:focus {
    outline: none;
    border-color: #0066cc;
    box-shadow: 0 0 5px rgba(0, 102, 204, 0.3);
}

.search-input-group .btn {
    padding: 10px 16px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-weight: 500;
    transition: background-color 0.3s;
}

.search-result-info {
    font-size: 0.9em;
    color: #666;
    margin: 0;
    padding: 0 0 0 2px;
}

.filter-section {
    background: #f9f9f9;
    padding: 15px;
    border-radius: 6px;
    margin-bottom: 20px;
}

.filter-section h3 {
    margin: 0 0 10px 0;
    font-size: 0.95em;
}

.filter-buttons {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.btn {
    padding: 8px 15px;
    border: 1px solid #ddd;
    border-radius: 4px;
    text-decoration: none;
    display: inline-block;
    cursor: pointer;
    transition: all 0.3s;
    font-size: 0.9em;
}

.btn-primary {
    background: #0066cc;
    color: white;
    border-color: #0066cc;
}

.btn-outline {
    background: white;
    color: #333;
    border-color: #ddd;
}

.btn-outline:hover {
    background: #f5f5f5;
}

.blog-list-table {
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.table {
    width: 100%;
    border-collapse: collapse;
    margin: 0;
}

.table thead {
    background: #f5f5f5;
    border-bottom: 2px solid #ddd;
}

.table th {
    padding: 15px;
    text-align: left;
    font-weight: 600;
    color: #333;
}

.table td {
    padding: 15px;
    border-bottom: 1px solid #eee;
    vertical-align: middle;
}

.table tbody tr:hover {
    background: #f9f9f9;
}

.table tbody tr.draft-row {
    background: #fffbf0;
}

.badge {
    display: inline-block;
    padding: 5px 10px;
    border-radius: 4px;
    font-size: 0.85em;
    font-weight: 500;
    white-space: nowrap;
}

.badge-warning {
    background: #fff3cd;
    color: #856404;
}

.badge-success {
    background: #d4edda;
    color: #155724;
}

.badge-secondary {
    background: #e2e3e5;
    color: #383d41;
}

.action-buttons {
    display: flex;
    gap: 5px;
}

.btn-sm {
    padding: 6px 10px;
    font-size: 0.85em;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
    transition: background 0.3s;
}

.btn-info {
    background: #e3f2fd;
    color: #1976d2;
}

.btn-info:hover {
    background: #bbdefb;
}

.btn-success {
    background: #d4edda;
    color: #155724;
}

.btn-warning {
    background: #fff3cd;
    color: #856404;
}

.btn-danger {
    background: #f8d7da;
    color: #721c24;
}

.btn-pin {
    background: #fff9c4;
    color: #f57c00;
    transition: all 0.3s;
}

.btn-pin:hover {
    background: #ffd54f;
    color: #e65100;
}

.btn-pin[data-pinned="true"] {
    background: #ffc107;
    color: #fff;
}

.text-muted {
    color: #888;
}

@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
        gap: 15px;
        align-items: flex-start;
    }

    .table {
        font-size: 0.9em;
    }

    .table th,
    .table td {
        padding: 10px;
    }

    .action-buttons {
        flex-direction: column;
    }

    .btn-sm {
        width: 100%;
        text-align: center;
    }
}
</style>

<script>
function togglePin(button, postId) {
    const csrfToken = '<?= Yii::$app->request->csrfToken ?>';
    const isPinned = button.getAttribute('data-pinned') === 'true';
    
    fetch('<?= Url::to(['/admin/blog-pin']) ?>&id=' + postId, {
        method: 'POST',
        headers: {
            'X-CSRF-Token': csrfToken
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const newPinned = data.isPinned;
            button.setAttribute('data-pinned', newPinned ? 'true' : 'false');
            button.textContent = newPinned ? '📌' : '📍';
            button.title = newPinned ? 'Bỏ ghim' : 'Ghim bài viết';
            alert(data.message);
        } else {
            alert('Lỗi: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Có lỗi xảy ra');
    });
}

function rejectPost(postId) {
    const reason = prompt('Nhập lý do từ chối bài viết này:');
    if (reason === null) {
        return;
    }
    if (reason.trim() === '') {
        alert('Vui lòng nhập lý do từ chối.');
        return;
    }

    const form = document.getElementById('reject-form-' + postId);
    if (!form) {
        alert('Không tìm thấy form từ chối. Vui lòng thử lại.');
        return;
    }

    const reasonField = form.querySelector('input[name="rejectionreason"]');
    if (!reasonField) {
        alert('Không tìm thấy trường lý do từ chối.');
        return;
    }

    reasonField.value = reason;
    form.submit();
}
</script>
