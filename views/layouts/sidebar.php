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
            
            <!-- VIEW MODE -->
            <div class="modal-header">
                <div class="logo-section">
                    <img src="<?= Yii::getAlias('@web/images/andilogo.png') ?>" alt="Andi Logo" class="logo-icon">
                    <h2>Andi</h2>
                </div>
            </div>

            <div class="modal-avatar">
                <img id="profileAvatarDisplay" src="<?= $user->avatarurl ?: Yii::getAlias('@web/images/andi-avatar.png') ?>" alt="User Avatar">
            </div>

            <h3 id="profileNameDisplay"><?= Html::encode($user->displayname) ?></h3>

            <div class="info-section">
                <p><strong>Email:</strong> <?= Html::encode($user->email) ?></p>
                <p><strong>Ngày tham gia:</strong> <?= date('d/m/Y', strtotime($user->createdat ?? 'now')) ?></p>
            </div>

            <!-- ACTION BUTTONS -->
            <button class="action-btn edit-btn" onclick="openEditNameModal()">
                <span class="icon">✏️</span> Đổi tên đại diện
            </button>

            <button class="action-btn password-btn" onclick="openChangePasswordModal()">
                <span class="icon">🔐</span> Đổi mật khẩu
            </button>

            <button class="action-btn avatar-btn" onclick="openChangeAvatarModal()">
                <span class="icon">📷</span> Đổi ảnh đại diện
            </button>

            <button class="action-btn logout-btn" onclick="submitLogoutForm(event)">
                <span class="icon">→</span> Đăng xuất
            </button>
        </div>
    </div>

    <!-- MODAL EDIT NAME -->
    <div id="editNameModal" class="profile-modal">
        <div class="modal-content">
            <button class="close-btn" onclick="closeEditNameModal()">✕</button>
            <h2>Đổi tên đại diện</h2>
            
            <div class="edit-form">
                <div class="form-group">
                    <label>Họ và tên mới</label>
                    <input type="text" id="inputNewName" class="form-input" placeholder="Nhập tên mới..." value="<?= $user->displayname ?>">
                    <small id="nameError" class="text-danger" style="display:none;"></small>
                </div>
                
                <div class="form-actions">
                    <button class="btn-save" onclick="saveNewName()">Lưu</button>
                    <button class="btn-cancel" onclick="closeEditNameModal()">Hủy</button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL CHANGE PASSWORD -->
    <div id="changePasswordModal" class="profile-modal">
        <div class="modal-content">
            <button class="close-btn" onclick="closeChangePasswordModal()">✕</button>
            <h2>Đổi mật khẩu</h2>
            
            <div class="edit-form">
                <div class="form-group">
                    <label>Mật khẩu mới</label>
                    <input type="password" id="inputNewPassword" class="form-input" placeholder="Tối thiểu 6 ký tự...">
                </div>
                
                <div class="form-group">
                    <label>Xác nhận mật khẩu</label>
                    <input type="password" id="inputConfirmPassword" class="form-input" placeholder="Nhập lại mật khẩu mới...">
                </div>
                
                <small id="passwordError" class="text-danger" style="display:none;"></small>
                
                <div class="form-actions">
                    <button class="btn-save" onclick="saveNewPassword()">Lưu</button>
                    <button class="btn-cancel" onclick="closeChangePasswordModal()">Hủy</button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL CHANGE AVATAR -->
    <div id="changeAvatarModal" class="profile-modal">
        <div class="modal-content">
            <button class="close-btn" onclick="closeChangeAvatarModal()">✕</button>
            <h2>Đổi ảnh đại diện</h2>
            
            <div class="edit-form" style="text-align: center;">
                <div class="modal-avatar" style="margin: 20px auto;">
                    <img id="avatarPreview" src="<?= $user->avatarurl ?: Yii::getAlias('@web/images/andi-avatar.png') ?>" alt="Avatar" style="width: 120px; height: 120px; border-radius: 50%; object-fit: cover;">
                </div>
                
                <div class="form-group">
                    <label for="avatarFileInput" class="btn-upload">Chọn ảnh (JPEG, PNG, GIF, WebP)</label>
                    <input type="file" id="avatarFileInput" hidden accept="image/*" onchange="previewNewAvatar(this)">
                    <small id="avatarError" class="text-danger" style="display:none;"></small>
                </div>
                
                <div class="form-actions">
                    <button class="btn-save" id="btnSaveAvatar" onclick="saveNewAvatar()">Lưu</button>
                    <button class="btn-cancel" onclick="closeChangeAvatarModal()">Hủy</button>
                </div>
            </div>
        </div>
    </div>
</aside>