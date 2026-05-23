<?php
/** @var yii\web\View $this */
/** @var app\models\SignupForm $model */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;
use yii\helpers\Url;

$this->title = 'Đăng ký tài khoản - Andi';


$this->registerCssFile('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

$this->registerCssFile('@web/css/login.css', ['depends' => [\yii\bootstrap5\BootstrapAsset::class]]);
?>

<div class="login-wrapper" style="font-family: 'Inter', sans-serif; background: #f8f9fa; min-height: 90vh; display: flex; align-items: center; justify-content: center; padding: 40px 20px;">
    <div class="login-container" style="background: #fff; width: 100%; max-width: 500px; padding: 40px; border-radius: 20px; box-shadow: 0 15px 35px rgba(0,0,0,0.07);">
        
        <div class="text-center mb-4">
            <h2 style="font-weight: 800; color: #1a1a1a; margin-bottom: 12px; letter-spacing: -0.5px;">Tạo tài khoản mới</h2>
            <p style="color: #666; font-size: 15px;">Tham gia cùng cộng đồng Andi để bắt đầu học tập thông minh.</p>
        </div>

        <?php $form = ActiveForm::begin([
            'id' => 'signup-form',
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

            <?= $form->field($model, 'displayname')->textInput(['placeholder' => 'Nhập tên của bạn...']) ?>

            <?= $form->field($model, 'email')->textInput(['placeholder' => 'Ví dụ: nguyenvana@gmail.com']) ?>

            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'password')->passwordInput(['placeholder' => 'Tối thiểu 6 ký tự']) ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'password_repeat')->passwordInput(['placeholder' => 'Nhập lại mật khẩu']) ?>
                </div>
            </div>

            <div class="form-group mb-3 mt-4">
                <?= Html::submitButton('Tạo tài khoản ngay', [
                    'class' => 'btn btn-primary w-100', 
                    'style' => 'background: #ff4081; border: none; padding: 14px; font-weight: 700; border-radius: 12px; font-size: 16px; box-shadow: 0 4px 12px rgba(255, 64, 129, 0.2);'
                ]) ?>
            </div>

        <?php ActiveForm::end(); ?>

        <div class="position-relative my-4 text-center">
            <hr style="border-top: 1px solid #eee;">
            <span style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: #fff; padding: 0 15px; color: #999; font-size: 12px; font-weight: 600; text-transform: uppercase;">Hoặc tiếp tục với</span>
        </div>

        <div class="social-login">
            <a href="<?= Url::to(['site/auth', 'authclient' => 'google']) ?>" 
               class="btn btn-outline-secondary w-100 d-flex align-items-center justify-content-center gap-3" 
               style="padding: 12px; border-radius: 12px; border: 1.5px solid #eee; font-weight: 600; color: #333;">
                <img src="https://upload.wikimedia.org/wikipedia/commons/c/c1/Google_%22G%22_logo.svg" alt="Google" style="width: 20px;">
                Đăng ký bằng Google
            </a>
        </div>

        <div class="text-center mt-4 pt-2">
            <p style="font-size: 14px; color: #666;">
                Bạn đã có tài khoản? 
                <a href="<?= Url::to(['site/login']) ?>" style="color: #ff4081; text-decoration: none; font-weight: 700; margin-left: 4px;">Đăng nhập tại đây</a>
            </p>
        </div>

    </div>
</div>

<?= $form->field($model, 'password')->passwordInput() ?>
<?= $form->field($model, 'password_repeat')->passwordInput() ?>
<?php $form = ActiveForm::begin([
    'id' => 'form-signup',
    'enableClientValidation' => true,
    'enableAjaxValidation' => false,
]); ?>