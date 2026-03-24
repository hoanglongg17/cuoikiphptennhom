<?php
use yii\helpers\Url;
use yii\helpers\Html;

$user = Yii::$app->user->identity;
?>
<aside class="sidebar" id="sidebar" data-collapsed="false">
    <ul class="menu">
        <li><a href="<?= Url::to(['site/dashboard']) ?>" class="<?= Yii::$app->controller->action->id == 'dashboard' ? 'active' : '' ?>" title="Trang chủ"><img src="<?= Yii::getAlias('@web') ?>/icons/home.png" alt=""><span>Trang chủ</span></a></li>
        <li><a href="<?= Url::to(['site/vocabset']) ?>" class="<?= Yii::$app->controller->action->id == 'vocabset' ? 'active' : '' ?>" title="Bộ thẻ"><img src="<?= Yii::getAlias('@web') ?>/icons/vocabset.png" alt=""><span>Bộ thẻ</span></a></li>            
        <li><a href="<?= Url::to(['site/vocabulary']) ?>" class="<?= Yii::$app->controller->action->id == 'vocabulary' ? 'active' : '' ?>" title="Từ vựng"><img src="<?= Yii::getAlias('@web') ?>/icons/vocabulary.png" alt=""><span>Từ vựng</span></a></li>            
        <li><a href="<?= Url::to(['site/practice']) ?>" class="<?= Yii::$app->controller->action->id == 'practice' || Yii::$app->controller->action->id == 'study-deck' ? 'active' : '' ?>" title="Luyện tập"><img src="<?= Yii::getAlias('@web') ?>/icons/practice.png" alt=""><span>Luyện tập</span></a></li>
    </ul>

    <button class="toggle-btn" id="toggleSidebar" onclick="toggleSidebar()" title="Ẩn/hiện sidebar">&laquo;</button>

    <div class="profile">
        <div class="avatar" onclick="toggleProfileModal()" style="cursor: pointer;">
            <img src="<?= $user->avatarurl ?: Yii::getAlias('@web/images/andi-avatar.png') ?>" alt="User Avatar">
        </div>
        <!-- Nhấn vào tên để hiện popup -->
        <p class="username" onclick="toggleProfileModal()"><?= Html::encode($user->displayname) ?></p>
        <div class="profile-actions">
            <!-- Nút Xem hồ sơ kích hoạt popup -->
            <button class="btn-profile" onclick="toggleProfileModal()">Xem hồ sơ</button>
            <label class="theme-switch">
                <input type="checkbox" id="darkModeToggle">
                <span class="slider"></span>
                <span class="label-text">Tối</span>
            </label>
        </div>
    </div>

    <!-- Profile Modal -->
    <div id="profileModal" class="profile-modal">
        <div class="modal-content">
            <button class="close-btn" onclick="toggleProfileModal()">✕</button>
            
            <div class="modal-header">
                <div class="logo-section">
                    <img src="<?= Yii::getAlias('@web/images/andilogo.png') ?>" alt="Andi Logo" class="logo-icon">
                    <h2>Andi</h2>
                </div>
            </div>

            <div class="modal-avatar">
                <img src="<?= $user->avatarurl ?: Yii::getAlias('@web/images/andi-avatar.png') ?>" alt="User Avatar">
            </div>

            <h3><?= Html::encode($user->displayname) ?></h3>

            <button class="action-btn edit-btn" onclick="openEditProfile()">
                <span class="icon">✏️</span> Đổi tên đại diện
            </button>

            <div class="info-section">
                <p><strong>Email:</strong> <?= Html::encode($user->email) ?></p>
                <p><strong>Ngày tham gia:</strong> <?= date('d/m/Y', strtotime($user->createdat ?? 'now')) ?></p>
            </div>

            <button class="action-btn google-btn" onclick="alert('Cập nhật Avatar từ Google')">
                <span class="icon">🔵</span> Cập nhật Avatar từ Google
            </button>

            <button class="action-btn help-btn" onclick="alert('Hướng dẫn cài đặt ứng dụng')">
                <span class="icon">⬇️</span> Hướng dẫn cài đặt ứng dụng
            </button>

            <button class="action-btn logout-btn" onclick="submitLogoutForm(event)">
                <span class="icon">→</span> Đăng xuất
            </button>
            
            <!-- Hidden Logout Form (Yii auto-add CSRF) -->
            <?= Html::beginForm(['/site/logout'], 'post', ['id' => 'logoutForm', 'style' => 'display:none;']) ?>
            <?= Html::endForm() ?>
        </div>
    </div>
</aside>