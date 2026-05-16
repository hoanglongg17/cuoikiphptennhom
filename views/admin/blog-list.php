<?php
/** @var yii\web\View $this */
/** @var app\models\BlogPost[] $posts */
/** @var string $currentStatus */

use yii\helpers\Url;
use yii\helpers\Html;

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

    <!-- Status Filter -->
    <div class="filter-section">
        <h3>Lọc theo Trạng Thái:</h3>
        <div class="filter-buttons">
            <a href="<?= Url::to(['admin/blog-list']) ?>" 
               class="btn <?= empty($currentStatus) ? 'btn-primary' : 'btn-outline' ?>">
                Tất Cả
            </a>
            <a href="<?= Url::to(['admin/blog-list', 'status' => 'published']) ?>" 
               class="btn <?= $currentStatus === 'published' ? 'btn-primary' : 'btn-outline' ?>">
                ✅ Đã Đăng
            </a>
            <a href="<?= Url::to(['admin/blog-list', 'status' => 'draft']) ?>" 
               class="btn <?= $currentStatus === 'draft' ? 'btn-primary' : 'btn-outline' ?>">
                📋 Bản Nháp
            </a>
            <a href="<?= Url::to(['admin/blog-list', 'status' => 'archived']) ?>" 
               class="btn <?= $currentStatus === 'archived' ? 'btn-primary' : 'btn-outline' ?>">
                🗂️ Lưu Trữ
            </a>
        </div>
    </div>

    <!-- Blog List Table -->
    <div class="blog-list-table">
        <?php if (empty($posts)): ?>
            <div class="alert alert-info">
                <p>Không có bài viết nào<?= $currentStatus ? ' với trạng thái "' . $currentStatus . '"' : '' ?></p>
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
                                    'draft' => '<span class="badge badge-warning">📋 Nháp</span>',
                                    'published' => '<span class="badge badge-success">✅ Đã Đăng</span>',
                                    'archived' => '<span class="badge badge-secondary">🗂️ Lưu Trữ</span>',
                                ];
                                echo isset($statusLabels[$post->status]) ? $statusLabels[$post->status] : $post->status;
                                ?>
                            </td>
                            <td>👁️ <?= $post->views ?></td>
                            <td>
                                💬 <?php 
                                $commentCount = \app\models\BlogComment::find()
                                    ->where(['postid' => $post->postid])
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
                                    <?php elseif ($post->status === 'draft'): ?>
                                        <a href="<?= Url::to(['admin/blog-publish', 'id' => $post->postid]) ?>" 
                                           class="btn btn-sm btn-success" title="Xuất bản">📤</a>
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
