<?php
/** @var yii\web\View $this */
use yii\helpers\Url;
$this->title = 'Trang chủ Andi';
?>

<div class="dashboard">
    <!-- Sidebar -->
    <aside class="sidebar">
        <ul class="menu">
            <li><a href="<?= Url::to(['site/dashboard']) ?>" class="active"><img src="<?= Yii::getAlias('@web') ?>/icons/home.png" alt=""> Trang chủ</a></li>
            <li><a href="<?= Url::to(['site/vocabset']) ?>" class="<?= Yii::$app->controller->action->id == 'vocabset' ? 'active' : '' ?>"><img src="<?= Yii::getAlias('@web') ?>/icons/vocabset.png" alt=""> Bộ thẻ</a></li>            
            <li><a href="<?= Url::to(['site/vocabulary']) ?>" class="<?= Yii::$app->controller->action->id == 'vocabulary' ? 'active' : '' ?>"><img src="<?= Yii::getAlias('@web') ?>/icons/vocabulary.png" alt=""> Từ vựng</a></li>            
            <li><a href="#"><img src="<?= Yii::getAlias('@web') ?>/icons/practice.png" alt=""> Luyện tập</a></li>
        </ul>

        <!-- Toggle button -->
        <button class="toggle-btn">&laquo;</button>

        <!-- Profile section -->
        <div class="profile">
            <div class="avatar">
                <img src="<?= Yii::getAlias('@web') ?>/images/andi-avatar.png" alt="User Avatar">
            </div>
            <p class="username">Nguyễn Văn A</p>
            <div class="profile-actions">
                <button class="btn-profile">Xem hồ sơ</button>
                <label class="theme-switch">
                    <input type="checkbox" id="darkModeToggle">
                    <span class="slider"></span>
                    <span class="label-text">Tối</span>
                </label>
            </div>
        </div>
    </aside>

    <!-- Main content -->
    <main class="main">
        <div class="content-row">
            <!-- Cover image -->
            <div class="cover">
                <img src="<?= Yii::getAlias('@web') ?>/images/cover-default.jpg" alt="Ảnh bìa">
            </div>

            <!-- Learning streak -->
            <div class="banner">
                <h3>🔥 CHUỖI NGÀY HỌC</h3>
                <p class="streak">1 ngày</p>
                <div class="days">
                    <span>T3</span>
                    <span>T4</span>
                    <span>T5</span>
                    <span>T6</span>
                    <span>T7</span>
                    <span>CN</span>
                    <span class="active">T2</span>
                </div>
            </div>
        </div>

        <!-- Features -->
        <section class="features">
            <h2>Tính năng</h2>
            <div class="feature-buttons">
                <button class="btn-feature">
                    <img src="<?= Yii::getAlias('@web') ?>/icons/flashcard.png" alt="Flashcard Icon">
                    Thêm bộ thẻ
                </button>
                <button class="btn-feature">
                    <img src="<?= Yii::getAlias('@web') ?>/icons/practice.png" alt="Practice Icon">
                    Luyện tập
                </button>
            </div>
        </section>
    </main>
</div>