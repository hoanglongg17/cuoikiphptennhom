<?php
/** @var yii\web\View $this */
/** @var int $pendingCount */
/** @var int $approvedCount */
/** @var int $spamCount */
/** @var string $statusFilter */
/** @var app\models\BlogComment[] $comments */
/** @var yii\data\Pagination $pagination */

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\LinkPager;

$this->title = 'Duyệt Bình Luận';
$this->params['breadcrumbs'][] = ['label' => 'Admin Panel', 'url' => ['admin/index']];
$this->params['breadcrumbs'][] = 'Duyệt Bình Luận';
?>

<div class="admin-comments-moderation">
    <h1>💬 Duyệt Bình Luận</h1>

    <div class="moderation-stats">
        <div class="stat-card">
            <div class="stat-number"><?= $pendingCount ?></div>
            <div class="stat-label">Chờ duyệt</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?= $approvedCount ?></div>
            <div class="stat-label">Đã duyệt</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?= $spamCount ?></div>
            <div class="stat-label">Spam</div>
        </div>
    </div>

    <div class="moderation-filter">
        <form method="get" class="filter-form">
            <select name="status">
                <option value="">Tất cả trạng thái</option>
                <option value="pending" <?= $statusFilter === 'pending' ? 'selected' : '' ?>>Chờ duyệt</option>
                <option value="approved" <?= $statusFilter === 'approved' ? 'selected' : '' ?>>Đã duyệt</option>
                <option value="spam" <?= $statusFilter === 'spam' ? 'selected' : '' ?>>Spam</option>
            </select>
            <button type="submit">Lọc</button>
        </form>
    </div>

    <div class="comments-table">
        <?php if (empty($pendingComments)): ?>
            <div class="alert alert-info">
                Không có bình luận nào để duyệt.
            </div>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Bài Viết</th>
                        <th>Tác Giả</th>
                        <th>Nội Dung</th>
                        <th>Ngày Gửi</th>
                        <th>Trạng Thái</th>
                        <th>Hành Động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pendingComments as $comment): ?>
                        <tr>
                            <td>
                                <a href="<?= Url::to(['blog/view', 'slug' => $comment->post->slug]) ?>">
                                    <?= Html::encode($comment->post->title) ?>
                                </a>
                            </td>
                            <td>
                                <strong><?= Html::encode($comment->user->displayname) ?></strong>
                                <span class="text-muted">(<?= Html::encode($comment->user->email) ?>)</span>
                            </td>
                            <td class="comment-content">
                                <?php if ($comment->parentcommentid): ?>
                                    <em>💬 Trả lời</em><br>
                                <?php endif; ?>
                                <?= Html::encode(substr($comment->content, 0, 100)) ?>...
                            </td>
                            <td>
                                <?= Yii::$app->formatter->asDate($comment->createdat, 'php:d/m/Y H:i') ?>
                            </td>
                            <td>
                                <span class="status-badge status-<?= $comment->status ?>">
                                    <?php 
                                    $statusText = [
                                        'pending' => '⏳ Chờ',
                                        'approved' => '✅ Duyệt',
                                        'rejected' => '❌ Từ chối',
                                        'spam' => '🚫 Spam',
                                    ];
                                    echo $statusText[$comment->status] ?? $comment->status;
                                    ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="<?= Url::to(['admin/approve-comment', 'id' => $comment->commentid]) ?>" class="btn btn-sm btn-success">
                                        Duyệt
                                    </a>
                                    <a href="<?= Url::to(['admin/reject-comment', 'id' => $comment->commentid]) ?>" class="btn btn-sm btn-warning">
                                        Từ chối
                                    </a>
                                    <a href="<?= Url::to(['admin/mark-spam', 'id' => $comment->commentid]) ?>" class="btn btn-sm btn-danger">
                                        Spam
                                    </a>
                                    <a href="<?= Url::to(['admin/delete-comment', 'id' => $comment->commentid]) ?>" 
                                       class="btn btn-sm btn-dark"
                                       onclick="return confirm('Bạn chắc chắn muốn xóa bình luận này?')">
                                        Xóa
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    
    <?php if ($pagination && $pagination->pageCount > 1): ?>
        <div class="pagination-wrap">
            <?= LinkPager::widget([
                'pagination' => $pagination,
                'options' => ['class' => 'pagination justify-content-center'],
            ]) ?>
        </div>
    <?php endif; ?>
</div>

<style>
.admin-comments-moderation {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.admin-comments-moderation h1 {
    margin-bottom: 30px;
    color: #333;
}

.moderation-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 20px;
    text-align: center;
}

.stat-number {
    font-size: 2.5em;
    font-weight: bold;
    color: #0066cc;
    margin-bottom: 10px;
}

.stat-label {
    color: #888;
    font-size: 0.95em;
}

.moderation-filter {
    margin-bottom: 20px;
}

.filter-form {
    display: flex;
    gap: 10px;
}

.filter-form select {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 0.95em;
}

.filter-form button {
    padding: 8px 20px;
    background: #0066cc;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-weight: 500;
}

.filter-form button:hover {
    background: #0052a3;
}

.comments-table {
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    overflow-x: auto;
}

.table {
    width: 100%;
    border-collapse: collapse;
    margin: 0;
}

.table thead {
    background: #f9f9f9;
    border-bottom: 2px solid #e0e0e0;
}

.table th {
    padding: 12px 15px;
    text-align: left;
    font-weight: 600;
    color: #333;
}

.table td {
    padding: 12px 15px;
    border-bottom: 1px solid #f0f0f0;
}

.table tbody tr:hover {
    background: #f9f9f9;
}

.comment-content {
    max-width: 400px;
    word-break: break-word;
}

.text-muted {
    color: #999;
    font-size: 0.85em;
}

.status-badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 0.85em;
    font-weight: 500;
}

.status-pending {
    background: #fff3cd;
    color: #856404;
}

.status-approved {
    background: #d4edda;
    color: #155724;
}

.status-spam {
    background: #f8d7da;
    color: #721c24;
}

.status-rejected {
    background: #e2e3e5;
    color: #383d41;
}

.action-buttons {
    display: flex;
    gap: 5px;
    flex-wrap: wrap;
}

.btn {
    padding: 6px 12px;
    border-radius: 4px;
    text-decoration: none;
    font-size: 0.85em;
    border: none;
    cursor: pointer;
    display: inline-block;
}

.btn-sm {
    padding: 4px 8px;
    font-size: 0.8em;
}

.btn-success {
    background: #28a745;
    color: white;
}

.btn-success:hover {
    background: #218838;
}

.btn-warning {
    background: #ffc107;
    color: #333;
}

.btn-warning:hover {
    background: #e0a800;
}

.btn-danger {
    background: #dc3545;
    color: white;
}

.btn-danger:hover {
    background: #c82333;
}

.btn-dark {
    background: #6c757d;
    color: white;
}

.btn-dark:hover {
    background: #5a6268;
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

.pagination-wrap {
    margin-top: 20px;
    text-align: center;
}

@media (max-width: 768px) {
    .moderation-stats {
        grid-template-columns: 1fr;
    }

    .action-buttons {
        flex-direction: column;
    }

    .btn {
        width: 100%;
    }
}
</style>
