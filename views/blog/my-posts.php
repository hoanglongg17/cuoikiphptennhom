<?php



/** @var yii\web\View $this */
/** @var app\models\BlogPost[] $posts */

use yii\helpers\Url;
use yii\helpers\Html;
use app\models\BlogPost;

$this->title = 'Bài Viết Của Tôi';
$this->params['breadcrumbs'][] = ['label' => 'Blog', 'url' => ['blog/index']];
$this->params['breadcrumbs'][] = 'Bài Viết Của Tôi';
?>

<div class="my-posts-container">
    <div class="header">
        <h1>📚 Bài Viết Của Tôi</h1>
        <a href="<?= Url::to(['blog/create']) ?>" class="btn btn-primary">✍️ Viết Bài Mới</a>
    </div>

    <?php if (Yii::$app->session->hasFlash('success')): ?>
        <div class="alert alert-success">
            <?= Yii::$app->session->getFlash('success') ?>
        </div>
    <?php endif; ?>

    <div class="posts-table-wrapper">
        <?php if (empty($posts)): ?>
            <div class="alert alert-info">
                <p>Bạn chưa viết bài viết nào. <a href="<?= Url::to(['blog/create']) ?>">Viết bài viết đầu tiên ngay!</a></p>
            </div>
        <?php else: ?>
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Tiêu Đề</th>
                        <th>Trạng Thái</th>
                        <th>Lượt Xem</th>
                        <th>Bình Luận</th>
                        <th>Ngày Tạo</th>
                        <th>Hành Động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($posts as $post): ?>
                        <tr>
                            <td>
                                <strong><?= Html::encode($post->title) ?></strong>
                                <?php if ($post->sharedeckid): ?>
                                    <br><small class="text-muted">Chia sẻ: <?= Html::encode($post->sharedDeck->name) ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php 
                                $statusLabels = [
                                    BlogPost::STATUS_DRAFT => '<span class="badge badge-warning">Nháp</span>',
                                    BlogPost::STATUS_PENDING => '<span class="badge badge-info">Chờ Duyệt</span>',
                                    BlogPost::STATUS_PUBLISHED => '<span class="badge badge-success">Đã Đăng</span>',
                                    BlogPost::STATUS_ARCHIVED => '<span class="badge badge-secondary">Lưu Trữ</span>',
                                    BlogPost::STATUS_DENIED => '<span class="badge badge-danger">Từ Chối</span>',
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
                            <td><?= Yii::$app->formatter->asDate($post->createdat, 'php:d/m/Y') ?></td>
                            <td>
                                <div class="action-buttons">
                                    <?php if ($post->isPublished()): ?>
                                        <a href="<?= Url::to(['blog/view', 'slug' => $post->slug]) ?>" 
                                           class="btn btn-sm btn-info" title="Xem bài viết">
                                            👁️
                                        </a>
                                    <?php endif; ?>
                                    <?php if (!$post->isPublished() && $post->status !== BlogPost::STATUS_ARCHIVED): ?>
                                        <a href="<?= Url::to(['blog/edit', 'id' => $post->postid]) ?>" 
                                           class="btn btn-sm btn-warning" title="Chỉnh sửa">
                                            ✏️
                                        </a>
                                    <?php endif; ?>
                                    
                                    <?= Html::beginForm(['blog/delete', 'id' => $post->postid], 'post', 
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
.my-posts-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 2px solid #f0f0f0;
}

.header h1 {
    margin: 0;
}

.posts-table-wrapper {
    background: #fff;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.table {
    margin: 0;
    width: 100%;
    border-collapse: collapse;
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
}

.table tbody tr:hover {
    background: #f9f9f9;
}

.badge {
    display: inline-block;
    padding: 5px 12px;
    border-radius: 999px;
    font-size: 0.85em;
    font-weight: 600;
    line-height: 1;
    min-width: 70px;
    text-align: center;
    border: 1px solid transparent;
}

.badge-warning {
    background: #fff3cd;
    color: #856404;
    border-color: #ffeeba;
}

.badge-success {
    background: #d4edda;
    color: #155724;
    border-color: #c3e6cb;
}

.badge-secondary {
    background: #e2e3e5;
    color: #383d41;
    border-color: #d6d8db;
}

.badge-info {
    background: #d1ecf1;
    color: #0c5460;
    border-color: #bee5eb;
}

.badge-danger {
    background: #f8d7da;
    color: #721c24;
    border-color: #f5c6cb;
}

.action-buttons {
    display: flex;
    gap: 5px;
}

.btn-sm {
    padding: 5px 10px;
    font-size: 0.9em;
}

@media (max-width: 768px) {
    .header {
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
}
</style>
