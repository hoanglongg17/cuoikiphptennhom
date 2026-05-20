<?php
/** @var yii\web\View $this */
/** @var app\models\BlogPost[] $posts */

use yii\helpers\Url;
use yii\helpers\Html;

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
                                    'draft' => '<span class="badge badge-warning">Nháp</span>',
                                    'published' => '<span class="badge badge-success">Đã Đăng</span>',
                                    'archived' => '<span class="badge badge-secondary">Lưu Trữ</span>',
                                ];
                                echo isset($statusLabels[$post->status]) ? $statusLabels[$post->status] : $post->status;
                                ?>
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
                                    
                                    <a href="<?= Url::to(['blog/edit', 'id' => $post->postid]) ?>" 
                                       class="btn btn-sm btn-warning" title="Chỉnh sửa">
                                        ✏️
                                    </a>
                                    
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

.badge-secondary {
    background: #e2e3e5;
    color: #383d41;
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
