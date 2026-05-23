<?php

/** @var yii\web\View $this */
/** @var app\models\User[] $admins */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Quản Lý Admin - Admin Panel';
$this->params['breadcrumbs'][] = ['label' => 'Admin', 'url' => ['dashboard']];
$this->params['breadcrumbs'][] = 'Quản Lý Admin';

$currentUser = Yii::$app->user->identity;

?>

<div class="admin-management">
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <div>
            <h1 style="margin: 0; font-size: 28px; font-weight: 700; color: #1a1a1a;">👥 Quản Lý Admin</h1>
            <p style="color: #666; margin: 8px 0 0 0; font-size: 14px;">Danh sách tất cả tài khoản Admin của hệ thống</p>
        </div>
        <div>
            <?= Html::a('➕ Tạo Admin Mới', ['admin-create'], [
                'class' => 'btn btn-primary',
                'style' => 'background: #ff4081; border: none; color: white; padding: 12px 24px; border-radius: 10px; font-weight: 600; text-decoration: none; display: inline-block;'
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

    <?php if (empty($admins)): ?>
        <div class="empty-state" style="text-align: center; padding: 60px 20px; background: #f8f9fa; border-radius: 10px;">
            <p style="font-size: 18px; color: #666;">Chưa có tài khoản Admin nào</p>
            <p style="color: #999; margin-bottom: 20px;">Tạo một tài khoản Admin mới để bắt đầu</p>
            <?= Html::a('Tạo Admin Mới', ['admin-create'], [
                'class' => 'btn btn-primary',
                'style' => 'background: #ff4081; border: none; color: white; padding: 12px 24px; border-radius: 10px; font-weight: 600; text-decoration: none;'
            ]) ?>
        </div>
    <?php else: ?>
        <div class="admin-list-container" style="background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); overflow: hidden;">
            <table class="table" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f8f9fa; border-bottom: 2px solid #ddd;">
                        <th style="padding: 16px; text-align: left; font-weight: 600; color: #333;">ID</th>
                        <th style="padding: 16px; text-align: left; font-weight: 600; color: #333;">Họ và Tên</th>
                        <th style="padding: 16px; text-align: left; font-weight: 600; color: #333;">Email</th>
                        <th style="padding: 16px; text-align: left; font-weight: 600; color: #333;">Ngày Tạo</th>
                        <th style="padding: 16px; text-align: center; font-weight: 600; color: #333;">Hành Động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($admins as $admin): ?>
                        <tr style="border-bottom: 1px solid #eee; transition: background 0.2s;">
                            <td style="padding: 16px; color: #666;"><?= $admin->userid ?></td>
                            <td style="padding: 16px; color: #333; font-weight: 500;"><?= Html::encode($admin->displayname) ?> <?php if ($admin->userid === $currentUser->userid): ?><span style="background: #cfe2ff; color: #084298; padding: 2px 8px; border-radius: 4px; font-size: 12px; font-weight: 600;">Bạn</span><?php endif; ?></td>
                            <td style="padding: 16px; color: #666;"><?= Html::encode($admin->email) ?></td>
                            <td style="padding: 16px; color: #666;"><?= Yii::$app->formatter->asDate($admin->createdat, 'php:d/m/Y H:i') ?></td>
                            <td style="padding: 16px; text-align: center;">
                                <?= Html::a('ℹ️ Chi tiết', ['admin-detail', 'id' => $admin->userid], [
                                    'class' => 'btn btn-sm btn-info',
                                    'style' => 'background: #1abc9c; border: none; color: white; padding: 6px 12px; border-radius: 6px; text-decoration: none; font-size: 12px; font-weight: 600; margin-right: 5px;'
                                ]) ?>
                                <?php if ($admin->userid !== $currentUser->userid): ?>
                                    <?php $publishedCount = \app\models\BlogPost::find()->where(['userid' => $admin->userid])->andWhere(['status' => 'published'])->count(); ?>
                                    <?php if ($publishedCount === 0): ?>
                                        <?= Html::beginForm(['admin-delete', 'id' => $admin->userid], 'POST', ['style' => 'display: inline;', 'onsubmit' => 'return confirm("Bạn có chắc muốn xóa admin này?\n\nNhững bài viết không xuất bản sẽ bị xóa tự động.\n\nHành động này không thể hoàn tác!")']) ?>
                                        <?= Html::submitButton('🗑️ Xóa', [
                                            'class' => 'btn btn-sm btn-danger',
                                            'style' => 'background: #dc3545; border: none; color: white; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer;'
                                        ]) ?>
                                        <?= Html::endForm() ?>
                                    <?php else: ?>
                                        <button class="btn btn-sm btn-danger" disabled style="background: #dc3545; opacity: 0.5; border: none; color: white; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: not-allowed;" title="Không thể xóa - admin này còn có bài viết xuất bản">
                                            🗑️ Xóa
                                        </button>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<style>
    .admin-management {
        background: transparent;
        padding: 0;
    }
    
    .table tbody tr:hover {
        background: #f8f9fa;
    }
</style>

