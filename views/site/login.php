<?php

/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */
/** @var app\models\LoginForm $model */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;
use yii\helpers\Url;

$this->title = 'Đăng nhập - Andi';
$this->registerCssFile('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
$this->registerCssFile('@web/css/login.css', ['depends' => [\yii\bootstrap5\BootstrapAsset::class]]);
?>

<div class="login-wrapper" style="font-family: 'Inter', sans-serif; background: #f8f9fa; min-height: 80vh; display: flex; align-items: center; justify-content: center; padding: 40px 20px;">
    <div class="login-container" style="background: #fff; width: 100%; max-width: 450px; padding: 40px; border-radius: 20px; box-shadow: 0 15px 35px rgba(0,0,0,0.07);">
        
        <div class="text-center mb-4">
            <h2 style="font-weight: 800; color: #1a1a1a; margin-bottom: 12px; letter-spacing: -0.5px;">Mừng bạn trở lại!</h2>
            <p style="color: #666; font-size: 15px;">Đăng nhập để tiếp tục hành trình chinh phục từ vựng cùng Andi.</p>
        </div>

        <?php $form = ActiveForm::begin([
            'id' => 'login-form',
            'fieldConfig' => [
                'template' => "{label}\n{input}\n{error}",
                'labelOptions' => ['class' => 'form-label', 'style' => 'font-weight: 600; font-size: 14px; color: #444; margin-bottom: 8px;'],
                'inputOptions' => [
                    'class' => 'form-control', 
                    'style' => 'padding: 12px 16px; border-radius: 12px; border: 1.5px solid #eee; font-size: 15px;'
                ],
                'errorOptions' => ['class' => 'invalid-feedback', 'style' => 'font-size: 13px; margin-top: 5px;'],
            ],
        ]); ?>

            <?= $form->field($model, 'email')->textInput(['autofocus' => true, 'placeholder' => 'Ví dụ: nguyenvana@gmail.com']) ?>

            <?= $form->field($model, 'password')->passwordInput(['placeholder' => 'Nhập mật khẩu...']) ?>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <?= $form->field($model, 'rememberMe')->checkbox([
                    'template' => "<div class=\"form-check\">{input} {label}</div>",
                ])->label('Ghi nhớ') ?>
                <a href="#" style="font-size: 14px; color: #ff4081; text-decoration: none;">Quên mật khẩu?</a>
            </div>

            <?= Html::submitButton('Đăng nhập ngay', ['class' => 'btn btn-primary w-100', 'style' => 'background: #ff4081; border: none; padding: 14px; font-weight: 700; border-radius: 12px;']) ?>

        <?php ActiveForm::end(); ?>

        <div class="position-relative my-4 text-center">
            <hr>
            <span style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: #fff; padding: 0 10px; color: #999; font-size: 12px;">HOẶC</span>
        </div>

        <a href="<?= Url::to(['site/auth', 'authclient' => 'google']) ?>" class="btn btn-outline-secondary w-100 d-flex align-items-center justify-content-center gap-2" style="border-radius: 12px; padding: 12px;">
            <img src="https://upload.wikimedia.org/wikipedia/commons/c/c1/Google_%22G%22_logo.svg" style="width: 18px;"> Tiếp tục với Google
        </a>

        <div class="text-center mt-4">
            <p style="font-size: 14px;">Chưa có tài khoản? <a href="<?= Url::to(['site/signup']) ?>" style="color: #ff4081; font-weight: 700; text-decoration: none;">Đăng ký miễn phí</a></p>
        </div>
    </div>
</div>