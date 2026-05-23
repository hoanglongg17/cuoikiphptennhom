<?php

/** @var yii\web\View $this */
/** @var app\models\AdminForm $model */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;
use yii\helpers\Url;

$this->title = 'Tạo Admin Mới - Admin Panel';
$this->params['breadcrumbs'][] = ['label' => 'Admin', 'url' => ['dashboard']];
$this->params['breadcrumbs'][] = ['label' => 'Quản Lý Admin', 'url' => ['admin-list']];
$this->params['breadcrumbs'][] = 'Tạo Admin Mới';

$this->registerCssFile('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

?>

<div class="admin-form-wrapper" style="background: white; padding: 40px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); max-width: 600px; margin: 0 auto;">
    <div class="form-header" style="margin-bottom: 30px;">
        <h1 style="font-size: 28px; font-weight: 700; color: #1a1a1a; margin: 0;">Tạo Admin Mới</h1>
        <p style="color: #666; margin: 8px 0 0 0; font-size: 14px;">Điền thông tin để tạo một tài khoản Admin mới</p>
    </div>

    <?php $form = ActiveForm::begin([
        'id' => 'admin-form',
        'fieldConfig' => [
            'template' => "{label}\n{input}\n{error}",
            'labelOptions' => ['class' => 'form-label', 'style' => 'font-weight: 600; font-size: 14px; color: #444; margin-bottom: 8px;'],
            'inputOptions' => [
                'class' => 'form-control', 
                'style' => 'padding: 12px 16px; border-radius: 10px; border: 1.5px solid #eee; font-size: 15px; font-family: Inter, sans-serif;'
            ],
            'errorOptions' => ['class' => 'invalid-feedback', 'style' => 'font-size: 13px; margin-top: 5px; display: block; color: #dc3545;'],
        ],
    ]); ?>

        <?= $form->field($model, 'displayname')->textInput([
            'placeholder' => 'Ví dụ: Nguyễn Văn A',
            'autocomplete' => 'off'
        ]) ?>

        <?= $form->field($model, 'email')->textInput([
            'type' => 'email',
            'placeholder' => 'Ví dụ: admin@gmail.com',
            'autocomplete' => 'off'
        ]) ?>

        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, 'password')->passwordInput([
                    'placeholder' => 'Tối thiểu 6 ký tự',
                    'autocomplete' => 'new-password'
                ]) ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'password_repeat')->passwordInput([
                    'placeholder' => 'Nhập lại mật khẩu',
                    'autocomplete' => 'new-password'
                ]) ?>
            </div>
        </div>

        <div class="form-group mb-3 mt-4">
            <div style="display: flex; gap: 10px;">
                <?= Html::submitButton('Tạo Admin', [
                    'class' => 'btn btn-primary flex-grow-1', 
                    'style' => 'background: #ff4081; border: none; padding: 14px; font-weight: 700; border-radius: 10px; font-size: 16px; color: white; box-shadow: 0 4px 12px rgba(255, 64, 129, 0.2);'
                ]) ?>
                
                <?= Html::a('← Quay Lại', ['admin-list'], [
                    'class' => 'btn btn-secondary',
                    'style' => 'background: #1abc9c; border: none; padding: 14px 24px; font-weight: 600; border-radius: 10px; font-size: 16px; color: white; text-decoration: none; display: inline-flex; align-items: center; justify-content: center;'
                ]) ?>
            </div>
        </div>

    <?php ActiveForm::end(); ?>
</div>

<style>
    .form-control:focus {
        border-color: #ff4081 !important;
        box-shadow: 0 0 0 0.2rem rgba(255, 64, 129, 0.25) !important;
    }
    
    .invalid-feedback {
        display: block;
    }
</style>
