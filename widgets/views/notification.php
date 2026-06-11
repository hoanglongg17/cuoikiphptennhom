<?php

use yii\helpers\Html;
use yii\helpers\Url;

?>

<div class="notification-widget">
    <button class="notification-btn" id="notification-btn" type="button">
        <span class="notification-icon">🔔</span>
        <span class="notification-badge" id="notification-badge" style="display: none;">
            0
        </span>
    </button>

    <div class="notification-modal" id="notification-modal" style="display: none;">
        <div class="notification-modal-content">
            <div class="notification-header">
                <h3>Thông báo</h3>
                <button class="notification-close-btn" id="notification-close-btn" type="button">
                    <span>✕</span>
                </button>
            </div>

            <div class="notification-list" id="notification-list">
                <div class="notification-loading">Đang tải...</div>
            </div>

            <div class="notification-footer" id="notification-footer" style="display: none;">
                <button class="mark-all-read-btn" id="mark-all-read-btn" type="button">
                    Đánh dấu tất cả đã đọc
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.notification-widget {
    position: relative;
}

.notification-btn {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    position: relative;
    padding: 8px;
    border-radius: 4px;
    transition: background-color 0.2s;
}

.notification-btn:hover {
    background-color: rgba(0, 0, 0, 0.05);
}

.notification-icon {
    display: inline-block;
}

.notification-badge {
    position: absolute;
    top: 0;
    right: 0;
    background-color: #dc3545;
    color: white;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: bold;
}

.notification-modal {
    position: absolute;
    top: 100%;
    right: 0;
    margin-top: 8px;
    background: white;
    border-radius: 8px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
    z-index: 1000;
    min-width: 400px;
    max-width: 500px;
    max-height: 600px;
    display: flex;
    flex-direction: column;
}

.notification-modal-content {
    display: flex;
    flex-direction: column;
    height: 100%;
}

.notification-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px;
    border-bottom: 1px solid #e0e0e0;
}

.notification-header h3 {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
}

.notification-close-btn {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: #666;
    padding: 0;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.notification-close-btn:hover {
    color: #000;
}

.notification-list {
    flex: 1;
    overflow-y: auto;
    padding: 8px 0;
}

.notification-item {
    padding: 12px 16px;
    border-bottom: 1px solid #f0f0f0;
    cursor: pointer;
    transition: background-color 0.2s;
    display: flex;
    align-items: flex-start;
    gap: 12px;
}

.notification-item:hover {
    background-color: #f9f9f9;
}

.notification-item.unread {
    background-color: #f0f8ff;
}

.notification-item-icon {
    font-size: 20px;
    flex-shrink: 0;
    margin-top: 2px;
}

.notification-item-content {
    flex: 1;
}

.notification-item-title {
    font-weight: 600;
    color: #000;
    margin: 0 0 4px 0;
}

.notification-item-text {
    color: #666;
    font-size: 14px;
    margin: 0;
    line-height: 1.4;
}

.notification-item-time {
    font-size: 12px;
    color: #999;
    margin-top: 4px;
}

.notification-loading {
    padding: 24px;
    text-align: center;
    color: #999;
}

.notification-empty {
    padding: 32px 16px;
    text-align: center;
    color: #999;
}

.notification-footer {
    padding: 12px 16px;
    border-top: 1px solid #e0e0e0;
}

.mark-all-read-btn {
    width: 100%;
    padding: 8px 16px;
    background-color: #007bff;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
    transition: background-color 0.2s;
}

.mark-all-read-btn:hover {
    background-color: #0056b3;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const notificationBtn = document.getElementById('notification-btn');
    const notificationModal = document.getElementById('notification-modal');
    const notificationCloseBtn = document.getElementById('notification-close-btn');
    const notificationList = document.getElementById('notification-list');
    const markAllReadBtn = document.getElementById('mark-all-read-btn');
    const notificationBadge = document.getElementById('notification-badge');
    const notificationFooter = document.getElementById('notification-footer');

    let isModalOpen = false;

    function formatTime(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const diffMs = now - date;
        const diffMins = Math.floor(diffMs / 60000);
        const diffHours = Math.floor(diffMs / 3600000);
        const diffDays = Math.floor(diffMs / 86400000);

        if (diffMins < 1) return 'Vừa xong';
        if (diffMins < 60) return diffMins + ' phút trước';
        if (diffHours < 24) return diffHours + ' giờ trước';
        if (diffDays < 7) return diffDays + ' ngày trước';

        return date.toLocaleDateString('vi-VN');
    }

    function getNotificationIcon(type) {
        const icons = {
            'approved': '✅',
            'rejected': '❌',
            'pending': '⏳'
        };
        return icons[type] || '📝';
    }

    function loadNotifications() {
        fetch('<?= Yii::$app->urlManager->createAbsoluteUrl(['notification/list']) ?>')
            .then(response => response.json())
            .then(data => {
                notificationList.innerHTML = '';

                if (!data.notifications || data.notifications.length === 0) {
                    notificationList.innerHTML = '<div class="notification-empty">Không có thông báo</div>';
                    notificationFooter.style.display = 'none';
                    return;
                }

                notificationFooter.style.display = 'block';

                data.notifications.forEach(notification => {
                    const div = document.createElement('div');
                    const isUnread = !notification.isread;
                    div.className = 'notification-item' + (isUnread ? ' unread' : '');
                    div.innerHTML = `
                        <div class="notification-item-icon">${getNotificationIcon(notification.type)}</div>
                        <div class="notification-item-content">
                            <p class="notification-item-title">${notification.title}</p>
                            <p class="notification-item-text">${notification.content}</p>
                            <div class="notification-item-time">${formatTime(notification.createdat)}</div>
                        </div>
                    `;

                    div.addEventListener('click', function(e) {
                        e.stopPropagation();
                        e.preventDefault();
                        
                        if (isUnread) {
                            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
                            const csrfParam = document.querySelector('meta[name="csrf-param"]')?.content || '_csrf';
                            
                            const formData = new FormData();
                            formData.append('id', notification.notificationid);
                            formData.append(csrfParam, csrfToken);
                            
                            fetch('<?= Yii::$app->urlManager->createAbsoluteUrl(['notification/mark-as-read']) ?>', {
                                method: 'POST',
                                body: formData
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success && data.redirectUrl) {
                                    window.location.href = data.redirectUrl;
                                }
                            })
                            .catch(error => {
                                console.error('Lỗi:', error);
                                if (notification.actionurl) {
                                    window.location.href = notification.actionurl;
                                }
                            });
                        } else if (notification.actionurl) {
                            window.location.href = notification.actionurl;
                        }
                    });

                    notificationList.appendChild(div);
                });
            })
            .catch(error => {
                console.error('Lỗi khi tải thông báo:', error);
                notificationList.innerHTML = '<div class="notification-empty">Lỗi khi tải thông báo</div>';
            });
    }

    function updateUnreadCount() {
        fetch('<?= Yii::$app->urlManager->createAbsoluteUrl(['notification/count-unread']) ?>')
            .then(response => response.json())
            .then(data => {
                if (data.count > 0) {
                    notificationBadge.textContent = data.count;
                    notificationBadge.style.display = 'inline-flex';
                } else {
                    notificationBadge.style.display = 'none';
                }
            });
    }

    function markAsRead(notificationId, actionUrl) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
        fetch('<?= Yii::$app->urlManager->createAbsoluteUrl(['notification/mark-as-read']) ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': csrfToken
            },
            body: JSON.stringify({ id: notificationId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateUnreadCount();
                if (actionUrl) {
                    window.location.href = actionUrl;
                } else {
                    loadNotifications();
                }
            } else {
                console.error('Lỗi:', data.message);
            }
        })
        .catch(error => {
            console.error('Lỗi khi đánh dấu thông báo:', error);
        });
    }

    notificationBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        isModalOpen = !isModalOpen;
        notificationModal.style.display = isModalOpen ? 'flex' : 'none';
        if (isModalOpen) {
            loadNotifications();
        }
    });

    notificationCloseBtn.addEventListener('click', function() {
        isModalOpen = false;
        notificationModal.style.display = 'none';
    });

    markAllReadBtn.addEventListener('click', function() {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
        const csrfParam = document.querySelector('meta[name="csrf-param"]')?.content || '_csrf';
        
        const formData = new FormData();
        formData.append(csrfParam, csrfToken);
        
        fetch('<?= Yii::$app->urlManager->createAbsoluteUrl(['notification/mark-all-as-read']) ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateUnreadCount();
                loadNotifications();
            }
        })
        .catch(error => console.error('Lỗi:', error));
    });

    document.addEventListener('click', function(e) {
        if (!notificationModal.contains(e.target) && e.target !== notificationBtn && !notificationBtn.contains(e.target)) {
            if (isModalOpen) {
                isModalOpen = false;
                notificationModal.style.display = 'none';
            }
        }
    });

    updateUnreadCount();
});
</script>
