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
        <p class="streak"><?= $user->currentstreak ?? 0 ?> ngày</p>
        <div class="days">
            <span>T3</span><span>T4</span><span>T5</span><span>T6</span><span>T7</span><span>CN</span>
            <span class="active">T2</span>
        </div>
    </div>
</div>

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
<!-- MODAL PROFILE CẬP NHẬT CHỨC NĂNG EDIT, ĐỔI MẬT KHẨU & AVATAR -->
<div id="profileModal" class="profile-modal">
    <div class="modal-content" id="modalContent">
        <span class="close-btn" onclick="toggleProfileModal()">&times;</span>
        
        <!-- PHẦN AVATAR VỚI CHỨC NĂNG ĐỔI ẢNH -->
        <div class="modal-avatar-container">
            <div class="modal-avatar">
                <img src="<?= $user->avatarurl ? $user->avatarurl : Yii::getAlias('@web/images/andi-avatar.png') ?>" alt="Avatar" id="modalAvatarImg">
                <!-- Nút chọn ảnh chỉ hiện khi ở chế độ Edit -->
                <label for="avatarInput" class="avatar-edit-badge edit-mode" style="display: none;">
                    <img src="<?= Yii::getAlias('@web/icons/camera.png') ?>" alt="Đổi ảnh" style="width: 20px; height: 20px; border:none; padding:0; background:none;">
                </label>
                <input type="file" id="avatarInput" hidden accept="image/*" onchange="previewAvatar(this)">
            </div>
        </div>

        <!-- PHẦN TÊN -->
        <h2 class="view-mode" id="displayNameText"><?= Html::encode($user->displayname) ?></h2>
        <div class="edit-mode" style="display: none; width: 100%;">
            <label class="edit-label">Họ và tên</label>
            <input type="text" id="inputDisplayName" class="edit-input" value="<?= Html::encode($user->displayname) ?>" placeholder="Nhập tên mới...">
            <small id="nameError" class="text-danger" style="display:none;"></small>
        </div>

        

        <div class="modal-body">
            <!-- Thông tin hiển thị (View Mode) -->
            <div class="view-mode">
                <div class="info-row">
                    <span class="info-label">🔥 Chuỗi ngày:</span>
                    <span class="info-value"><?= $user->currentstreak ?? 0 ?> ngày</span>
                </div>
                <div class="info-row">
                <span class="info-label">📅 Ngày bắt đầu tham gia:</span>
                <span class="info-value"><?= date('d/m/Y', strtotime($user->createdat)) ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">📧 Email:</span>
                    <span class="info-value"><?= Html::encode($user->email) ?></span>
                </div>
            </div>

            <!-- Chỉnh sửa Mật khẩu (Edit Mode) -->
            <div class="edit-mode" style="display: none; border-top: 1px inset #eee; padding-top: 15px; margin-top: 10px;">
                <p style="font-weight: 700; color: #ff4081; margin-bottom: 10px; text-align: center;">Bảo mật & Mật khẩu</p>
                
                <div class="info-row-edit">
                    <label class="edit-label">Mật khẩu mới</label>
                    <input type="password" id="inputNewPassword" class="edit-input" placeholder="Tối thiểu 6 ký tự...">
                </div>
                
                <div class="info-row-edit">
                    <label class="edit-label">Xác nhận mật khẩu</label>
                    <input type="password" id="inputConfirmPassword" class="edit-input" placeholder="Nhập lại mật khẩu mới...">
                </div>
                <small id="passError" class="text-danger" style="display:none; text-align: center; width: 100%;"></small>
            </div>
        </div>

        <div class="modal-footer">
            <button class="btn-edit view-mode" onclick="enterEditMode()">Chỉnh sửa hồ sơ</button>
            <div class="view-mode w-100">
                <?= Html::beginForm(['/site/logout'], 'post') ?>
                    <?= Html::submitButton('Đăng xuất', ['class' => 'btn-logout-modal']) ?>
                <?= Html::endForm() ?>
            </div>

            <button class="btn-save edit-mode" style="display: none;" id="btnSaveProfile" onclick="saveProfile()">Lưu thay đổi</button>
            <button class="btn-cancel edit-mode" style="display: none;" onclick="exitEditMode()">Hủy</button>
        </div>
    </div>
</div>

<script>
let currentAvatarBase64 = null;

/**
 * Xem trước ảnh sau khi chọn file
 */
function previewAvatar(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        
        // Ràng buộc kích thước (ví dụ tối đa 2MB)
        if (file.size > 2 * 1024 * 1024) {
            alert("Ảnh quá lớn! Vui lòng chọn ảnh dưới 2MB.");
            input.value = "";
            return;
        }

        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('modalAvatarImg').src = e.target.result;
            currentAvatarBase64 = e.target.result; // Lưu lại để gửi AJAX
        };
        reader.readAsDataURL(file);
    }
}

function enterEditMode() {
    const modal = document.getElementById('modalContent');
    modal.classList.add('is-editing');
    document.querySelectorAll('.edit-mode').forEach(el => el.style.display = 'block');
    document.querySelectorAll('.view-mode').forEach(el => el.style.display = 'none');
    hideErrors();
}

function exitEditMode() {
    const modal = document.getElementById('modalContent');
    modal.classList.remove('is-editing');
    document.querySelectorAll('.edit-mode').forEach(el => el.style.display = 'none');
    document.querySelectorAll('.view-mode').forEach(el => el.style.display = 'block');
    
    // Khôi phục avatar cũ nếu hủy
    document.getElementById('modalAvatarImg').src = "<?= $user->avatarurl ? $user->avatarurl : Yii::getAlias('@web/images/andi-avatar.png') ?>";
    currentAvatarBase64 = null;
    document.getElementById('avatarInput').value = "";
    
    document.getElementById('inputDisplayName').value = document.getElementById('displayNameText').innerText;
    document.getElementById('inputNewPassword').value = '';
    document.getElementById('inputConfirmPassword').value = '';
    hideErrors();
}

function hideErrors() {
    document.getElementById('nameError').style.display = 'none';
    document.getElementById('passError').style.display = 'none';
}

function saveProfile() {
    const newName = document.getElementById('inputDisplayName').value.trim();
    const newPass = document.getElementById('inputNewPassword').value;
    const confirmPass = document.getElementById('inputConfirmPassword').value;
    
    const nameError = document.getElementById('nameError');
    const passError = document.getElementById('passError');
    
    hideErrors();
    let hasError = false;

    // Ràng buộc Tên
    const nameRegex = /^[\p{L}\p{N} ]+$/u;
    if (newName.length < 2 || newName.length > 50) {
        nameError.innerText = "Tên phải từ 2 đến 50 ký tự.";
        nameError.style.display = 'block';
        hasError = true;
    } else if (!nameRegex.test(newName)) {
        nameError.innerText = "Tên không được chứa ký tự đặc biệt.";
        nameError.style.display = 'block';
        hasError = true;
    }

    // Ràng buộc Mật khẩu
    if (newPass.length > 0) {
        if (newPass.length < 6) {
            passError.innerText = "Mật khẩu tối thiểu 6 ký tự.";
            passError.style.display = 'block';
            hasError = true;
        } else if (newPass !== confirmPass) {
            passError.innerText = "Xác nhận mật khẩu không khớp.";
            passError.style.display = 'block';
            hasError = true;
        }
    }

    if (hasError) return;

    const btnSave = document.getElementById('btnSaveProfile');
    btnSave.disabled = true;
    btnSave.innerText = "Đang xử lý...";

    // Gửi dữ liệu qua AJAX
    const formData = new URLSearchParams();
    formData.append('displayname', newName);
    if (newPass) formData.append('password', newPass);
    if (currentAvatarBase64) formData.append('avatar_base64', currentAvatarBase64);

    fetch('<?= Url::to(['site/ajax-update-profile']) ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-CSRF-Token': '<?= Yii::$app->request->getCsrfToken() ?>'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        btnSave.disabled = false;
        btnSave.innerText = "Lưu thay đổi";

        if (data.success) {
            document.getElementById('displayNameText').innerText = newName;
            // Cập nhật tên và ảnh ở Sidebar
            if (document.querySelector('.profile .username')) {
                document.querySelector('.profile .username').innerText = newName;
            }
            if (currentAvatarBase64 && document.querySelector('.profile .avatar img')) {
                document.querySelector('.profile .avatar img').src = currentAvatarBase64;
            }
            
            alert("Đã cập nhật hồ sơ thành công!");
            exitEditMode();
        } else {
            alert("Lỗi: " + data.message);
        }
    })
    .catch(error => {
        btnSave.disabled = false;
        btnSave.innerText = "Lưu thay đổi";
        console.error('Error:', error);
        alert("Lỗi kết nối máy chủ.");
    });
}

function toggleProfileModal() {
    const modal = document.getElementById('profileModal');
    modal.style.display = (modal.style.display === 'flex') ? 'none' : 'flex';
    if (modal.style.display === 'none') exitEditMode();
}
</script>