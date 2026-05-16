<?php
/** @var yii\web\View $this */
/** @var app\models\BlogPost $model */
/** @var boolean $isNew */

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\Deck;

$this->title = $isNew ? 'Tạo Bài Blog Mới' : 'Chỉnh Sửa Bài Blog';
$this->params['breadcrumbs'][] = ['label' => 'Admin', 'url' => ['admin/dashboard']];
$this->params['breadcrumbs'][] = ['label' => 'Bài Viết', 'url' => ['admin/blog-list']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="blog-form-container">
    <div class="form-wrapper">
        <h1><?= $this->title ?></h1>

        <?php if (Yii::$app->session->hasFlash('success')): ?>
            <div class="alert alert-success">
                <?= Yii::$app->session->getFlash('success') ?>
            </div>
        <?php endif; ?>

        <?php $form = ActiveForm::begin([
            'options' => ['class' => 'blog-form'],
        ]); ?>

            <div class="form-section">
                <h2>✍️ Thông Tin Bài Viết</h2>
                
                <div class="form-group">
                    <?= $form->field($model, 'title')->textInput([
                        'placeholder' => 'Nhập tiêu đề bài viết',
                        'class' => 'form-control form-control-lg',
                    ]) ?>
                </div>

                <div class="form-group">
                    <?= $form->field($model, 'excerpt')->textarea([
                        'placeholder' => 'Tóm tắt bài viết (xuất hiện trên danh sách)',
                        'rows' => 3,
                        'class' => 'form-control',
                    ]) ?>
                </div>

                <div class="form-group">
                    <?= $form->field($model, 'content')->textarea([
                        'placeholder' => 'Viết nội dung bài viết...',
                        'rows' => 20,
                        'class' => 'form-control editor',
                    ])->hint('Hỗ trợ HTML và Markdown') ?>
                </div>
            </div>

            <div class="form-section">
                <h2>⚙️ Cài Đặt</h2>
                
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <?= $form->field($model, 'status')->dropDownList([
                            'draft' => '📋 Bản Nháp (Chưa Xuất Bản)',
                            'published' => '✅ Xuất Bản (Công Khai)',
                            'archived' => '🗂️ Lưu Trữ (Ẩn Khỏi Danh Sách)',
                        ]) ?>
                    </div>

                    <div class="form-group col-md-6">
                        <?= $form->field($model, 'sharedeckid')->dropDownList(
                            \yii\helpers\ArrayHelper::map(
                                Deck::find()->all(),
                                'deckid',
                                'name'
                            ),
                            ['prompt' => '-- Không chia sẻ bộ thẻ --']
                        )->hint('Chọn bộ thẻ để chia sẻ trong bài viết') ?>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <?= Html::submitButton(
                    $isNew ? '✍️ Tạo Bài Viết' : '💾 Cập Nhật',
                    ['class' => 'btn btn-primary btn-lg']
                ) ?>
                <a href="<?= Url::to(['admin/blog-list']) ?>" class="btn btn-secondary btn-lg">
                    ↩️ Quay Lại
                </a>
            </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>

<style>
.blog-form-container {
    max-width: 1000px;
    margin: 0 auto;
    padding: 20px;
}

.form-wrapper {
    background: #fff;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.blog-form {
    margin-top: 20px;
}

.form-section {
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 1px solid #eee;
}

.form-section:last-of-type {
    border-bottom: none;
}

.form-section h2 {
    margin: 0 0 20px 0;
    color: #333;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    font-weight: 500;
    margin-bottom: 8px;
    display: block;
    color: #333;
}

.form-group .help-block {
    font-size: 0.85em;
    color: #999;
    margin-top: 5px;
}

.form-control {
    display: block;
    width: 100%;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 10px;
    font-size: 1em;
    font-family: inherit;
    transition: border-color 0.3s, box-shadow 0.3s;
}

.form-control:focus {
    border-color: #0066cc;
    box-shadow: 0 0 0 3px rgba(0, 102, 204, 0.1);
    outline: none;
}

.form-control-lg {
    font-size: 1.2em;
    padding: 12px;
}

.editor {
    min-height: 400px;
    font-family: 'Courier New', monospace;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.form-actions {
    margin-top: 30px;
    display: flex;
    gap: 10px;
}

.btn-lg {
    padding: 10px 20px;
    font-size: 1em;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
    transition: background 0.3s;
}

.btn-primary {
    background: #0066cc;
    color: white;
}

.btn-primary:hover {
    background: #0052a3;
}

.btn-secondary {
    background: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background: #5a6268;
}

@media (max-width: 768px) {
    .form-row {
        grid-template-columns: 1fr;
    }

    .form-actions {
        flex-direction: column;
    }

    .btn-lg {
        width: 100%;
    }

    .editor {
        min-height: 300px;
    }
}
</style>
