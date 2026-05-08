// Toggle Sidebar
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    if (sidebar) {
        sidebar.classList.toggle('collapsed');
        sidebar.dataset.collapsed = sidebar.classList.contains('collapsed') ? 'true' : 'false';
        localStorage.setItem('sidebarCollapsed', sidebar.dataset.collapsed);
    }
}

// Toggle Profile Modal
function toggleProfileModal() {
    const modal = document.getElementById('profileModal');
    if (modal) {
        modal.classList.toggle('show');
    }
    closeAllEditModals();
}

// Close modal when clicking outside
document.addEventListener('click', function(event) {
    const modal = document.getElementById('profileModal');
    const profileSection = document.querySelector('.profile');
    if (modal && event.target === modal) {
        modal.classList.remove('show');
        closeAllEditModals();
    }
});

// ===== EDIT NAME FUNCTIONS =====
function openEditNameModal() {
    const modal = document.getElementById('profileModal');
    const editNameModal = document.getElementById('editNameModal');
    if (modal) modal.classList.remove('show');
    if (editNameModal) editNameModal.classList.add('show');
    setTimeout(() => document.getElementById('inputNewName')?.focus(), 50);
    clearAllErrors();
}

function closeEditNameModal() {
    const editNameModal = document.getElementById('editNameModal');
    const modal = document.getElementById('profileModal');
    if (editNameModal) editNameModal.classList.remove('show');
    if (modal) modal.classList.add('show');
    clearAllErrors();
}

function saveNewName() {
    const newName = document.getElementById('inputNewName').value.trim();
    const errorEl = document.getElementById('nameError');
    
    if (!newName) {
        errorEl.textContent = 'Vui lòng nhập tên';
        errorEl.style.display = 'block';
        return;
    }
    
    if (!(/^[a-zA-Z0-9À-ỿ\s'-]{2,50}$/.test(newName))) {
        errorEl.textContent = 'Tên phải từ 2-50 ký tự, chỉ chứa chữ cái, số và khoảng trắng';
        errorEl.style.display = 'block';
        return;
    }
    
    sendProfileUpdate({ displayname: newName }, 'Cập nhật tên thành công!', () => {
        document.getElementById('profileNameDisplay').textContent = newName;
        const sidebarName = document.querySelector('.sidebar-name');
        if (sidebarName) sidebarName.textContent = newName;
        closeEditNameModal();
    });
}

// ===== CHANGE PASSWORD FUNCTIONS =====
function openChangePasswordModal() {
    const modal = document.getElementById('profileModal');
    const changePassModal = document.getElementById('changePasswordModal');
    if (modal) modal.classList.remove('show');
    if (changePassModal) changePassModal.classList.add('show');
    setTimeout(() => document.getElementById('inputNewPassword')?.focus(), 50);
    clearAllErrors();
}

function closeChangePasswordModal() {
    const changePassModal = document.getElementById('changePasswordModal');
    const modal = document.getElementById('profileModal');
    if (changePassModal) changePassModal.classList.remove('show');
    if (modal) modal.classList.add('show');
    document.getElementById('inputNewPassword').value = '';
    document.getElementById('inputConfirmPassword').value = '';
    clearAllErrors();
}

function saveNewPassword() {
    const newPassword = document.getElementById('inputNewPassword').value;
    const confirmPassword = document.getElementById('inputConfirmPassword').value;
    const errorEl = document.getElementById('passwordError');
    
    if (!newPassword) {
        errorEl.textContent = 'Vui lòng nhập mật khẩu mới';
        errorEl.style.display = 'block';
        return;
    }
    
    if (newPassword.length < 6) {
        errorEl.textContent = 'Mật khẩu mới phải tối thiểu 6 ký tự';
        errorEl.style.display = 'block';
        return;
    }
    
    if (newPassword !== confirmPassword) {
        errorEl.textContent = 'Mật khẩu không khớp';
        errorEl.style.display = 'block';
        return;
    }
    
    sendProfileUpdate({ password: newPassword }, 'Đổi mật khẩu thành công!', () => {
        closeChangePasswordModal();
    });
}

// ===== CHANGE AVATAR FUNCTIONS =====
function openChangeAvatarModal() {
    const modal = document.getElementById('profileModal');
    const changeAvatarModal = document.getElementById('changeAvatarModal');
    if (modal) modal.classList.remove('show');
    if (changeAvatarModal) changeAvatarModal.classList.add('show');
    clearAllErrors();
}

function closeChangeAvatarModal() {
    const changeAvatarModal = document.getElementById('changeAvatarModal');
    const modal = document.getElementById('profileModal');
    if (changeAvatarModal) changeAvatarModal.classList.remove('show');
    if (modal) modal.classList.add('show');
    document.getElementById('avatarFileInput').value = '';
    document.getElementById('avatarPreview').src = document.getElementById('profileAvatarDisplay').src;
    clearAllErrors();
}

function previewNewAvatar(input) {
    if (!input.files || !input.files[0]) return;
    
    const file = input.files[0];
    const maxSize = 2 * 1024 * 1024; // 2MB
    const errorEl = document.getElementById('avatarError');
    
    if (file.size > maxSize) {
        errorEl.textContent = 'Ảnh phải nhỏ hơn 2MB';
        errorEl.style.display = 'block';
        return;
    }
    
    const reader = new FileReader();
    reader.onload = function(e) {
        document.getElementById('avatarPreview').src = e.target.result;
        errorEl.style.display = 'none';
    };
    reader.readAsDataURL(file);
}

function saveNewAvatar() {
    const avatarInput = document.getElementById('avatarFileInput');
    const errorEl = document.getElementById('avatarError');
    
    if (!avatarInput.files || !avatarInput.files[0]) {
        errorEl.textContent = 'Vui lòng chọn ảnh';
        errorEl.style.display = 'block';
        return;
    }
    
    const file = avatarInput.files[0];
    const reader = new FileReader();
    reader.onload = function(e) {
        sendProfileUpdate(
            { avatar_base64: e.target.result },
            'Đổi ảnh đại diện thành công!',
            (data) => {
                if (data.avatarurl) {
                    const newUrl = data.avatarurl + '?v=' + Date.now();
                    document.getElementById('profileAvatarDisplay').src = newUrl;
                    const sidebarAvatar = document.querySelector('.sidebar-avatar');
                    if (sidebarAvatar) sidebarAvatar.src = newUrl;
                }
                closeChangeAvatarModal();
            }
        );
    };
    reader.readAsDataURL(file);
}

// ===== HELPER FUNCTIONS =====
function sendProfileUpdate(updateData, successMsg, onSuccess) {
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') 
                  || document.querySelector('meta[name="csrf"]')?.getAttribute('content');
    
    const params = new URLSearchParams(updateData);
    
    fetch('/Andi-FlashcardMaster/web/index.php?r=site/ajax-update-profile', {
        method: 'POST',
        body: params,
        headers: {
            'X-CSRF-Token': token || ''
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(successMsg);
            if (onSuccess) onSuccess(data);
        } else {
            alert('Lỗi: ' + (data.message || 'Cập nhật thất bại'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Lỗi kết nối: ' + error.message);
    });
}

function clearAllErrors() {
    const nameError = document.getElementById('nameError');
    const passwordError = document.getElementById('passwordError');
    const avatarError = document.getElementById('avatarError');
    if (nameError) nameError.style.display = 'none';
    if (passwordError) passwordError.style.display = 'none';
    if (avatarError) avatarError.style.display = 'none';
}

function closeAllEditModals() {
    const editNameModal = document.getElementById('editNameModal');
    const changePassModal = document.getElementById('changePasswordModal');
    const changeAvatarModal = document.getElementById('changeAvatarModal');
    if (editNameModal) editNameModal.classList.remove('show');
    if (changePassModal) changePassModal.classList.remove('show');
    if (changeAvatarModal) changeAvatarModal.classList.remove('show');
}

// Submit Logout Form
function submitLogoutForm(event) {
    event.preventDefault();
    if (confirm('Bạn có chắc chắn muốn đăng xuất?')) {
        document.getElementById('logoutForm').submit();
    }
}

// Restore sidebar state on page load
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    if (sidebar) {
        const collapsed = localStorage.getItem('sidebarCollapsed') === 'true';
        if (collapsed) {
            sidebar.classList.add('collapsed');
            sidebar.dataset.collapsed = 'true';
        }
    }

    // Highlight today's day in study days
    const studyDays = document.getElementById('studyDays');
    if (studyDays) {
        const today = new Date();
        const dayOfWeek = today.getDay();
        // Convert JavaScript day (0=Sun, 1=Mon, ..., 6=Sat) to data-day (1=Mon, ..., 7=Sun)
        const todayDataDay = dayOfWeek === 0 ? 7 : dayOfWeek;
        
        // Remove active from all spans
        studyDays.querySelectorAll('span').forEach(span => {
            span.classList.remove('active');
        });
        
        // Add active to today's span
        const todaySpan = studyDays.querySelector(`span[data-day="${todayDataDay}"]`);
        if (todaySpan) {
            todaySpan.classList.add('active');
        }
    }

    // Dark mode toggle with smooth transition
    const darkModeToggle = document.getElementById('darkModeToggle');
    if (darkModeToggle) {
        // Restore theme preference from localStorage
        const isDarkMode = localStorage.getItem('darkMode') === 'true';
        if (isDarkMode) {
            document.body.classList.add('dark-mode');
            darkModeToggle.checked = true;
        }

        // Listen for theme toggle
        darkModeToggle.addEventListener('change', function() {
            if (this.checked) {
                document.body.classList.add('dark-mode');
                localStorage.setItem('darkMode', 'true');
            } else {
                document.body.classList.remove('dark-mode');
                localStorage.setItem('darkMode', 'false');
            }
        });
    }
});
