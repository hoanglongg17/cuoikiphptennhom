<?php




/** @var yii\web\View $this */
/** @var app\models\BlogPost $model */
/** @var bool $isNew */

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\Deck;
use app\models\BlogPost;

$this->title = $isNew ? 'Viết Bài Blog Mới' : 'Chỉnh Sửa Bài Blog';
$this->params['breadcrumbs'][] = ['label' => 'Blog', 'url' => ['blog/index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="blog-form-container">
    <div class="form-wrapper">
        <h1><?= $this->title ?></h1>

        <?php $form = ActiveForm::begin([
            'options' => ['class' => 'blog-form'],
        ]); ?>

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
                    'rows' => 15,
                    'class' => 'form-control',
                ])->hint('Hỗ trợ HTML và Markdown') ?>
            </div>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <?= $form->field($model, 'sharedeckid')->dropDownList(
                        \yii\helpers\ArrayHelper::map(
                            Deck::find()->where(['userid' => Yii::$app->user->id])->all(),
                            'deckid',
                            'name'
                        ),
                        ['prompt' => '-- Không chia sẻ bộ thẻ --']
                    ) ?>
                </div>

                <?php
                
                $user = Yii::$app->user->identity;
                $isAdminUser = $user && method_exists($user, 'isAdmin') && $user->isAdmin();
                if ($isAdminUser): ?>
                    <div class="form-group col-md-6">
                        <?= $form->field($model, 'status')->dropDownList([
                            'draft' => '📋 Nháp',
                            'pending' => '⏳ Chờ Duyệt',
                            'published' => '✅ Xuất Bản',
                            'archived' => '🗂️ Lưu Trữ',
                            'denied' => '❌ Từ Chối',
                        ]) ?>
                    </div>
                    <div class="form-group col-md-6">
                        <?= $form->field($model, 'rejectionreason')->textarea([
                            'rows' => 4,
                            'class' => 'form-control',
                        ])->hint('Chỉ dùng khi từ chối bài viết') ?>
                    </div>
                <?php else: ?>
                    <?php if ($isNew || in_array($model->status, [BlogPost::STATUS_DRAFT, BlogPost::STATUS_DENIED], true)): ?>
                        <div class="form-group col-md-6">
                            <?= $form->field($model, 'status')->dropDownList([
                                BlogPost::STATUS_DRAFT => '📋 Bản Nháp',
                                BlogPost::STATUS_PUBLISHED => '⏳ Gửi Duyệt',
                            ]) ?>
                        </div>
                    <?php elseif ($model->status === BlogPost::STATUS_PENDING): ?>
                        <div class="form-group col-md-6">
                            <label class="control-label">Trạng Thái</label>
                            <div class="form-control-static">⏳ Chờ Duyệt</div>
                            <?= Html::activeHiddenInput($model, 'status') ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <div class="form-actions">
                <?= Html::submitButton(
                    $isNew ? '✍️ Tạo Bài Viết' : '💾 Cập Nhật',
                    ['class' => 'btn btn-primary btn-lg']
                ) ?>
                <a href="<?= Url::to(['blog/my-posts']) ?>" class="btn btn-secondary btn-lg">
                    ↩️ Quay Lại
                </a>
            </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>

<style>
.blog-form-container {
    max-width: 900px;
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

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    font-weight: 500;
    margin-bottom: 8px;
    display: block;
    color: #333;
}

.form-control {
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 10px;
    font-size: 1em;
    transition: border-color 0.3s;
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
}

.help-block {
    font-size: 0.85em;
    color: #999;
    margin-top: 5px;
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
}
</style>
