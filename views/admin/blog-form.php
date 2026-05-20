<?php




/** @var yii\web\View $this */
/** @var app\models\BlogPost $model */
/** @var bool $isNew */

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\Deck;
use app\models\BlogPost;

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
                        <?php if ($isNew): ?>
                            <?= $form->field($model, 'status')->dropDownList([
                                'draft' => '📋 Bản Nháp (Chưa Xuất Bản)',
                                'pending' => '⏳ Chờ Duyệt',
                                'published' => '✅ Xuất Bản (Công Khai)',
                                'archived' => '🗂️ Lưu Trữ (Ẩn Khỏi Danh Sách)',
                            ]) ?>
                        <?php else: ?>
                            <?php if ($model->status === BlogPost::STATUS_DENIED): ?>
                                <div class="status-readonly" style="padding: 10px 12px; border: 1px solid #ddd; border-radius: 4px; background:#f9f9f9; color:#333;">
                                    ❌ Đã Từ Chối
                                </div>
                                <?= Html::activeHiddenInput($model, 'status') ?>
                            <?php else: ?>
                                <?= $form->field($model, 'status')->dropDownList([
                                    BlogPost::STATUS_PUBLISHED => '✅ Xuất Bản (Công Khai)',
                                    BlogPost::STATUS_ARCHIVED => '🗂️ Lưu Trữ (Ẩn Khỏi Danh Sách)',
                                ]) ?>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    <?php if (!$isNew && $model->status === BlogPost::STATUS_PENDING): ?>
                        <div class="form-group col-md-6">
                            <label class="control-label">Hành Động</label>
                            <div class="pending-action-group">
                                <div id="admin-blog-reject" data-action="<?= Url::to(['admin/blog-reject', 'id' => $model->postid]) ?>">
                                    <input type="hidden" id="_csrf_token_inline" value="<?= Yii::$app->request->csrfToken ?>" />
                                    <div class="form-group">
                                        <label class="control-label">Lý do từ chối</label>
                                        <textarea id="rejectionreason-field" rows="4" class="form-control" placeholder="Nhập lý do từ chối..."></textarea>
                                    </div>
                                    <div style="display:flex; gap:8px; align-items:center;">
                                        <button id="reject-submit" type="button" class="btn btn-danger">❌ Từ chối</button>
                                    </div>
                                </div>
                                <p class="help-block">Bài viết đang chờ duyệt. Bạn có thể duyệt hoặc từ chối với lý do.</p>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="form-group col-md-6">
                        <?php if ($isNew): ?>
                            <?= $form->field($model, 'sharedeckid')->dropDownList(
                                \yii\helpers\ArrayHelper::map(
                                    Deck::find()->all(),
                                    'deckid',
                                    'name'
                                ),
                                ['prompt' => '-- Không chia sẻ bộ thẻ --']
                            )->hint('Chọn bộ thẻ để chia sẻ trong bài viết') ?>
                        <?php else: ?>
                            <?php $shareDeck = $model->sharedeckid ? Deck::findOne($model->sharedeckid) : null; ?>
                            <label class="control-label">Bộ thẻ chia sẻ</label>
                            <div class="form-control" style="background:#f9f9f9; border-color:#ddd;">
                                <?= $shareDeck ? Html::encode($shareDeck->name) : '-- Không chia sẻ bộ thẻ --' ?>
                            </div>
                            <?= Html::activeHiddenInput($model, 'sharedeckid') ?>
                        <?php endif; ?>
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

    .pending-action-group {
        display: flex;
        flex-direction: column;
        gap: 10px;
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
<script>
document.addEventListener('DOMContentLoaded', function () {
    var wrapper = document.getElementById('admin-blog-reject');
    if (!wrapper) return;

    var actionUrl = wrapper.getAttribute('data-action');
    var textarea = document.getElementById('rejectionreason-field');
    var csrfToken = document.getElementById('_csrf_token_inline') ? document.getElementById('_csrf_token_inline').value : null;
    var submitBtn = document.getElementById('reject-submit');
    var cancelBtn = document.getElementById('reject-cancel');

    if (cancelBtn) {
        cancelBtn.addEventListener('click', function () {
            if (textarea) textarea.value = '';
        });
    }

    if (submitBtn) {
        submitBtn.addEventListener('click', function (e) {
            e.preventDefault();
            var reason = (textarea && textarea.value) ? textarea.value.trim() : '';
            if (!reason) {
                alert('Vui lòng nhập lý do từ chối.');
                if (textarea) textarea.focus();
                return;
            }

            var data = new FormData();
            data.append('rejectionreason', reason);
            if (csrfToken) data.append('_csrf', csrfToken);

            fetch(actionUrl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: data,
            }).then(function (res) {
                return res.json ? res.json() : res.text();
            }).then(function (json) {
                if (json && json.success) {
                    
                    location.href = '<?= Url::to(['admin/blog-list']) ?>';
                } else {
                    alert((json && json.message) || 'Không thể từ chối bài viết.');
                }
            }).catch(function (err) {
                console.error(err);
                alert('Có lỗi xảy ra khi gửi yêu cầu.');
            });
        });
    }
});
</script>

