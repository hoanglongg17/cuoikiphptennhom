<?php
/** @var yii\web\View $this */
use yii\helpers\Url;
use yii\helpers\Html;

$this->title = 'Trang chủ Andi';
// Lấy thông tin người dùng đang đăng nhập
$user = Yii::$app->user->identity; 
?>

<div class="content-row">
    <div class="cover">
        <img src="<?= Yii::getAlias('@web') ?>/images/cover-default.jpg" alt="Ảnh bìa">
    </div>

    <div class="banner">
        <h3>🔥 CHUỖI NGÀY HỌC</h3>
        <p class="streak"><?= $user->currentStreak ?> ngày</p>
        <div class="days" id="studyDays">
            <?php
                $weekDays = ['T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'CN'];
                foreach ($weekDays as $index => $label) {
                    $dayNumber = $index + 1;
                    echo '<span data-day="' . $dayNumber . '">' . $label . '</span>';
                }
            ?>
        </div>
    </div>
</div>

<section class="features">
    <h2>Tính năng</h2>
    <div class="feature-buttons">
        <a href="<?= Url::to(['site/vocabset']) ?>" class="btn-feature">
            <img src="<?= Yii::getAlias('@web') ?>/icons/flashcard.png" alt="Flashcard Icon">
            Thêm bộ thẻ
        </a>
        <a href="<?= Url::to(['site/practice']) ?>" class="btn-feature">
            <img src="<?= Yii::getAlias('@web') ?>/icons/practice.png" alt="Practice Icon">
            Luyện tập
        </a>
        <a href="<?= Url::to(['blog/index']) ?>" class="btn-feature">
            📝 Blog
        </a>
        <?php if ($user && method_exists($user, 'isAdmin') && $user->isAdmin()): ?>
            <a href="<?= Url::to(['admin/dashboard']) ?>" class="btn-feature btn-admin">
                🏛️ Admin Panel
            </a>
        <?php endif; ?>
    </div>
</section>