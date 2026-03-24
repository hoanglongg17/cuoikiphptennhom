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
}

// Close modal when clicking outside
document.addEventListener('click', function(event) {
    const modal = document.getElementById('profileModal');
    const profileSection = document.querySelector('.profile');
    if (modal && event.target === modal) {
        modal.classList.remove('show');
    }
});

// Open Edit Profile
function openEditProfile() {
    alert('Chức năng Đổi tên đại diện');
    // TODO: Implement edit profile modal
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

    // Dark mode toggle
    const darkModeToggle = document.getElementById('darkModeToggle');
    if (darkModeToggle) {
        // Restore theme preference
        const isDarkMode = localStorage.getItem('darkMode') === 'true';
        if (isDarkMode) {
            document.body.classList.add('dark-mode');
            darkModeToggle.checked = true;
        }

        darkModeToggle.addEventListener('change', function() {
            document.body.classList.toggle('dark-mode');
            localStorage.setItem('darkMode', this.checked ? 'true' : 'false');
        });
    }
});
