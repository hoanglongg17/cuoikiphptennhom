<?php

/** @var yii\web\View $this */
/** @var app\models\User $admin */
/** @var app\models\BlogPost[] $blogPosts */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Chi Tiết Admin - ' . Html::encode($admin->displayname);
$this->params['breadcrumbs'][] = ['label' => 'Admin', 'url' => ['dashboard']];
$this->params['breadcrumbs'][] = ['label' => 'Quản Lý Admin', 'url' => ['admin-list']];
$this->params['breadcrumbs'][] = Html::encode($admin->displayname);

$currentUser = Yii::$app->user->identity;
$publishedPosts = array_filter($blogPosts, function($post) { return $post->status === 'published'; });
$canDelete = $admin->userid !== $currentUser->userid && count($publishedPosts) === 0;

?>

<div class="admin-detail-wrapper" style="max-width: 1000px; margin: 0 auto;">
    <div class="detail-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <div>
            <h1 style="font-size: 28px; font-weight: 700; color: #1a1a1a; margin: 0;">👤 Chi Tiết Admin</h1>
            <p style="color: #666; margin: 8px 0 0 0; font-size: 14px;">Thông tin tài khoản và bài viết</p>
        </div>
        <div style="display: flex; gap: 10px;">
            <?= Html::a('← Quay Lại', ['admin-list'], [
                'class' => 'btn btn-secondary',
                'style' => 'background: #1abc9c; border: none; color: white; padding: 10px 20px; border-radius: 10px; font-weight: 600; text-decoration: none;'
            ]) ?>
        </div>
    </div>

    <?php if (Yii::$app->session->hasFlash('success')): ?>
        <div class="alert alert-success" style="padding: 15px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 8px; margin-bottom: 20px; color: #155724;">
            <?= Yii::$app->session->getFlash('success') ?>
        </div>
    <?php endif; ?>

    <?php if (Yii::$app->session->hasFlash('error')): ?>
        <div class="alert alert-danger" style="padding: 15px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 8px; margin-bottom: 20px; color: #721c24;">
            <?= Yii::$app->session->getFlash('error') ?>
        </div>
    <?php endif; ?>

    <!-- Admin Info Card -->
    <div class="admin-info-card" style="background: white; padding: 25px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); margin-bottom: 30px;">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
            <div>
                <h3 style="font-size: 14px; font-weight: 600; color: #666; text-transform: uppercase; margin: 0 0 10px 0;">Họ và Tên</h3>
                <p style="font-size: 18px; font-weight: 600; color: #1a1a1a; margin: 0;"><?= Html::encode($admin->displayname) ?></p>
            </div>
            <div>
                <h3 style="font-size: 14px; font-weight: 600; color: #666; text-transform: uppercase; margin: 0 0 10px 0;">Email</h3>
                <p style="font-size: 18px; font-weight: 600; color: #1a1a1a; margin: 0;"><?= Html::encode($admin->email) ?></p>
            </div>
            <div>
                <h3 style="font-size: 14px; font-weight: 600; color: #666; text-transform: uppercase; margin: 0 0 10px 0;">Ngày Tạo</h3>
                <p style="font-size: 16px; color: #333; margin: 0;"><?= Yii::$app->formatter->asDate($admin->createdat, 'php:d/m/Y H:i:s') ?></p>
            </div>
            <div>
                <h3 style="font-size: 14px; font-weight: 600; color: #666; text-transform: uppercase; margin: 0 0 10px 0;">Số Bài Viết</h3>
                <p style="font-size: 16px; color: #333; margin: 0;"><?= count($blogPosts) ?> bài</p>
            </div>
        </div>

        <!-- Admin Actions -->
        <div style="margin-top: 25px; padding-top: 25px; border-top: 1px solid #eee;">
            <?php if ($admin->userid !== $currentUser->userid): ?>
                <?php if ($canDelete): ?>
                    <?php $hasOtherPosts = count($blogPosts) - count($publishedPosts) > 0; ?>
                    <?= Html::beginForm(['admin-delete', 'id' => $admin->userid], 'POST', ['onsubmit' => 'return confirm("Bạn có chắc muốn xóa tài khoản admin này?\n' . ($hasOtherPosts ? 'Những bài viết không xuất bản sẽ bị xóa tự động.\n' : '') . 'Hành động này không thể hoàn tác!")']) ?>
                    <?= Html::submitButton('🗑️ Xóa Tài Khoản', [
                        'class' => 'btn btn-danger',
                        'style' => 'background: #dc3545; border: none; color: white; padding: 10px 20px; border-radius: 10px; font-weight: 600; cursor: pointer;'
                    ]) ?>
                    <?= Html::endForm() ?>
                <?php else: ?>
                    <button class="btn btn-danger" disabled style="background: #dc3545; opacity: 0.5; border: none; color: white; padding: 10px 20px; border-radius: 10px; font-weight: 600; cursor: not-allowed;">
                        🗑️ Không thể xóa (còn bài viết xuất bản)
                    </button>
                <?php endif; ?>
            <?php else: ?>
                <p style="color: #666; font-style: italic;">Đây là tài khoản của bạn, không thể xóa.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Blog Posts Section -->
    <div class="blog-posts-section" style="background: white; padding: 25px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
        <h2 style="font-size: 20px; font-weight: 700; color: #1a1a1a; margin: 0 0 20px 0;">📝 Tất Cả Bài Viết (<?= count($blogPosts) ?>)</h2>

        <?php if (empty($blogPosts)): ?>
            <div style="text-align: center; padding: 40px 20px; background: #f8f9fa; border-radius: 10px;">
                <p style="font-size: 16px; color: #666; margin: 0;">Admin này chưa đăng bài viết nào</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table" style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #f8f9fa; border-bottom: 2px solid #ddd;">
                            <th style="padding: 16px; text-align: left; font-weight: 600; color: #333;">Tiêu Đề</th>
                            <th style="padding: 16px; text-align: left; font-weight: 600; color: #333;">Trạng Thái</th>
                            <th style="padding: 16px; text-align: left; font-weight: 600; color: #333;">Ngày Đăng</th>
                            <th style="padding: 16px; text-align: center; font-weight: 600; color: #333;">Hành Động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($blogPosts as $post): ?>
                            <tr style="border-bottom: 1px solid #eee; background: <?= $post->status === 'published' ? '#fff' : '#fffef0' ?>;">
                                <td style="padding: 16px; color: #333; font-weight: 500;"><?= Html::encode($post->title) ?></td>
                                <td style="padding: 16px;">
                                    <?php 
                                    $statusLabels = [
                                        'draft' => '<span style="background: #fff3cd; color: #856404; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600;">Nháp</span>',
                                        'published' => '<span style="background: #d4edda; color: #155724; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600;">Đã Đăng</span>',
                                        'archived' => '<span style="background: #e2e3e5; color: #383d41; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600;">Lưu Trữ</span>',
                                        'pending' => '<span style="background: #cfe2ff; color: #084298; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600;">Chờ Duyệt</span>',
                                        'denied' => '<span style="background: #f8d7da; color: #721c24; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600;">Từ Chối</span>',
                                    ];
                                    echo isset($statusLabels[$post->status]) ? $statusLabels[$post->status] : $post->status;
                                    ?>
                                </td>
                                <td style="padding: 16px; color: #666;">
                                    <?= $post->publishedat ? Yii::$app->formatter->asDate($post->publishedat, 'php:d/m/Y') : 'N/A' ?>
                                </td>
                                <td style="padding: 16px; text-align: center;">
                                    <?= Html::a('Xem', ['blog/view', 'slug' => $post->slug], [
                                        'class' => 'btn btn-sm btn-info',
                                        'style' => 'background: #1abc9c; border: none; color: white; padding: 6px 12px; border-radius: 6px; text-decoration: none; font-size: 12px; font-weight: 600;'
                                    ]) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <?php $nonPublishedCount = count($blogPosts) - count($publishedPosts); ?>
            <?php if ($nonPublishedCount > 0): ?>
                <div style="margin-top: 15px; padding: 15px; background: #fff3cd; border: 1px solid #ffc107; border-radius: 8px; color: #856404;">
                    <strong>ℹ️ Lưu ý:</strong> Nếu xóa admin này, <?= $nonPublishedCount ?> bài viết không xuất bản sẽ bị xóa tự động.
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<style>
    .table tbody tr:hover {
        background: #f8f9fa !important;
    }
</style>
