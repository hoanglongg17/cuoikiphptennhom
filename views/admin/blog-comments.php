<?php
/** @var yii\web\View $this */
/** @var string $currentStatus */
/** @var app\models\BlogComment[] $comments */

use yii\helpers\Url;
use yii\helpers\Html;

$this->title = 'Duyệt Bình Luận Blog';
$this->params['breadcrumbs'][] = ['label' => 'Admin', 'url' => ['admin/dashboard']];
$this->params['breadcrumbs'][] = 'Bình Luận';
?>

<div class="admin-comments-page">
    <div class="page-header">
        <h1>💬 Quản Lý Bình Luận Blog</h1>
    </div>

    <?php if (Yii::$app->session->hasFlash('success')): ?>
        <div class="alert alert-success">
            <?= Yii::$app->session->getFlash('success') ?>
        </div>
    <?php endif; ?>

    
    <div class="filter-section">
        <h3>Lọc theo Trạng Thái:</h3>
        <div class="filter-buttons">
            <a href="<?= Url::to(['admin/blog-comments']) ?>" 
               class="btn <?= empty($currentStatus) ? 'btn-primary' : 'btn-outline' ?>">
                Tất Cả
            </a>
            <a href="<?= Url::to(['admin/blog-comments', 'status' => 'pending']) ?>" 
               class="btn <?= $currentStatus === 'pending' ? 'btn-primary' : 'btn-outline' ?>">
                ⏳ Chờ Duyệt
            </a>
            <a href="<?= Url::to(['admin/blog-comments', 'status' => 'approved']) ?>" 
               class="btn <?= $currentStatus === 'approved' ? 'btn-primary' : 'btn-outline' ?>">
                ✅ Được Duyệt
            </a>
            <a href="<?= Url::to(['admin/blog-comments', 'status' => 'rejected']) ?>" 
               class="btn <?= $currentStatus === 'rejected' ? 'btn-primary' : 'btn-outline' ?>">
                ❌ Bị Từ Chối
            </a>
        </div>
    </div>

    
    <div class="comments-wrapper">
        <?php if (empty($comments)): ?>
            <div class="alert alert-info">
                <p>Không có bình luận nào<?= $currentStatus ? ' với trạng thái "' . $currentStatus . '"' : '' ?></p>
            </div>
        <?php else: ?>
            <div class="comments-grid">
                <?php foreach ($comments as $comment): ?>
                    <div class="comment-card <?= 'comment-' . $comment->status ?>">
                        <div class="comment-header">
                            <div class="comment-user">
                                <strong><?= Html::encode($comment->user->displayname) ?></strong>
                                <br>
                                <small class="email"><?= Html::encode($comment->user->email) ?></small>
                            </div>
                            <div class="comment-status">
                                <?php 
                                $statusBadges = [
                                    'pending' => '<span class="badge badge-warning">⏳ Chờ Duyệt</span>',
                                    'approved' => '<span class="badge badge-success">✅ Được Duyệt</span>',
                                    'rejected' => '<span class="badge badge-danger">❌ Bị Từ Chối</span>',
                                ];
                                echo isset($statusBadges[$comment->status]) ? $statusBadges[$comment->status] : $comment->status;
                                ?>
                            </div>
                        </div>

                        <div class="comment-meta">
                            <span class="post-link">
                                Trên bài: <a href="<?= Url::to(['admin/blog-edit', 'id' => $comment->post->postid]) ?>">
                                    <?= Html::encode($comment->post->title) ?>
                                </a>
                            </span>
                            <span class="time">
                                📅 <?= Yii::$app->formatter->asDate($comment->createdat, 'php:d/m/Y H:i') ?>
                            </span>
                        </div>

                        <div class="comment-content">
                            <p><?= nl2br(Html::encode($comment->content)) ?></p>
                        </div>

                        <div class="comment-actions">
                            <?php if ($comment->status !== 'approved'): ?>
                                <a href="<?= Url::to(['admin/approve-comment', 'id' => $comment->commentid]) ?>" 
                                   class="btn btn-sm btn-success">✅ Duyệt</a>
                            <?php endif; ?>

                            <?php if ($comment->status !== 'rejected'): ?>
                                <a href="<?= Url::to(['admin/reject-comment', 'id' => $comment->commentid]) ?>" 
                                   class="btn btn-sm btn-warning">❌ Từ Chối</a>
                            <?php endif; ?>

                            <?= Html::beginForm(['admin/delete-comment', 'id' => $comment->commentid], 'post', 
                                ['style' => 'display: inline;']) ?>
                                <?= Html::submitButton('🗑️ Xóa', [
                                    'class' => 'btn btn-sm btn-danger',
                                    'onclick' => 'return confirm("Bạn chắc chắn muốn xóa bình luận này?");'
                                ]) ?>
                            <?= Html::endForm() ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.admin-comments-page {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.page-header {
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

.comments-wrapper {
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    padding: 20px;
}

.comments-grid {
    display: grid;
    gap: 20px;
}

.comment-card {
    border: 1px solid #ddd;
    border-left: 4px solid #999;
    border-radius: 6px;
    padding: 20px;
    background: #fafafa;
    transition: box-shadow 0.3s;
}

.comment-card:hover {
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.comment-pending {
    border-left-color: #ffc107;
    background: #fffbf0;
}

.comment-approved {
    border-left-color: #28a745;
    background: #f0f8f4;
}

.comment-rejected {
    border-left-color: #dc3545;
    background: #f8f0f0;
}

.comment-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 15px;
    padding-bottom: 15px;
    border-bottom: 1px solid #ddd;
}

.comment-user strong {
    display: block;
    margin-bottom: 5px;
    color: #333;
}

.email {
    color: #666;
}

.comment-status {
    text-align: right;
}

.badge {
    display: inline-block;
    padding: 5px 10px;
    border-radius: 4px;
    font-size: 0.85em;
    font-weight: 500;
}

.badge-warning {
    background: #fff3cd;
    color: #856404;
}

.badge-success {
    background: #d4edda;
    color: #155724;
}

.badge-danger {
    background: #f8d7da;
    color: #721c24;
}

.comment-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    margin-bottom: 15px;
    font-size: 0.9em;
    color: #666;
    flex-wrap: wrap;
}

.post-link a {
    color: #0066cc;
    text-decoration: none;
}

.post-link a:hover {
    text-decoration: underline;
}

.comment-content {
    margin-bottom: 15px;
    color: #555;
    line-height: 1.6;
}

.comment-content p {
    margin: 0;
}

.comment-actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.btn-sm {
    padding: 6px 12px;
    font-size: 0.85em;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
    transition: background 0.3s;
}

.btn-success {
    background: #d4edda;
    color: #155724;
}

.btn-success:hover {
    background: #c3e6cb;
}

.btn-warning {
    background: #fff3cd;
    color: #856404;
}

.btn-warning:hover {
    background: #ffeaa7;
}

.btn-danger {
    background: #f8d7da;
    color: #721c24;
}

.btn-danger:hover {
    background: #f5c6cb;
}

.alert {
    padding: 15px;
    border-radius: 4px;
    margin-bottom: 20px;
}

.alert-info {
    background: #d1ecf1;
    color: #0c5460;
    border: 1px solid #bee5eb;
}

.alert-success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

@media (max-width: 768px) {
    .comment-header {
        flex-direction: column;
        gap: 10px;
    }

    .comment-meta {
        flex-direction: column;
        align-items: flex-start;
    }

    .comment-actions {
        flex-direction: column;
    }

    .btn-sm {
        width: 100%;
        text-align: center;
    }
}
</style>
