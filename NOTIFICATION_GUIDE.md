# HƯỚNG DẪN HỆ THỐNG THÔNG BÁO

## Tổng quan
Hệ thống thông báo cho phép người dùng nhận được thông báo về tình trạng các bài viết blog của họ (duyệt/từ chối), và cho phép admin nhận được thông báo khi có bài viết mới chờ duyệt.

## Các thành phần được tạo

### 1. Database (setup.sql)
- Thêm bảng `notifications` với các cột:
  - `notificationid`: Mã thông báo (khóa chính)
  - `userid`: Mã người dùng nhận thông báo
  - `postid`: Mã bài viết liên quan
  - `type`: Loại thông báo (approved/rejected/pending)
  - `title`: Tiêu đề thông báo
  - `content`: Nội dung thông báo
  - `actionurl`: Đường dẫn khi nhấn vào thông báo
  - `isread`: Trạng thái đã đọc (0=chưa đọc, 1=đã đọc)
  - `createdat`: Thời gian tạo
  - `readedat`: Thời gian đọc

### 2. Migration (migrations/m260611_CreateNotificationsTable.php)
- Tạo bảng notifications
- Tạo các chỉ mục cho userid, isread, createdat

### 3. Model (models/Notification.php)
- Các hằng số:
  - `TYPE_APPROVED`: Bài viết được duyệt
  - `TYPE_REJECTED`: Bài viết bị từ chối
  - `TYPE_PENDING`: Bài viết chờ duyệt (từ người dùng mới)
- Các phương thức:
  - `createApprovedNotification()`: Tạo thông báo duyệt bài
  - `createRejectedNotification()`: Tạo thông báo từ chối bài
  - `createPendingNotification()`: Tạo thông báo bài chờ duyệt cho admin
  - `markAsRead()`: Đánh dấu thông báo là đã đọc
  - `markAllAsReadForUser()`: Đánh dấu tất cả thông báo của người dùng là đã đọc
  - `getUnreadCountForUser()`: Lấy số lượng thông báo chưa đọc
  - `getUnreadForUser()`: Lấy danh sách thông báo chưa đọc

### 4. Controller (controllers/NotificationController.php)
- `actionList`: Lấy danh sách thông báo chưa đọc (JSON)
- `actionMarkAsRead`: Đánh dấu một thông báo là đã đọc
- `actionMarkAllAsRead`: Đánh dấu tất cả thông báo là đã đọc
- `actionCountUnread`: Lấy số lượng thông báo chưa đọc

### 5. Widget (widgets/NotificationWidget.php và widgets/views/notification-widget.php)
- Hiển thị nút thông báo trong header
- Hiển thị số lượng thông báo chưa đọc (badge)
- Modal pop-up để xem danh sách thông báo
- Nút "Đánh dấu tất cả đã đọc"
- Phân loại thông báo theo loại (icon khác nhau)

### 6. Cập nhật Controllers
- **AdminController**:
  - Khi duyệt bài (actionBlogApprove): Tạo thông báo "Bài viết đã được duyệt"
  - Khi từ chối bài (actionBlogReject): Tạo thông báo "Bài viết bị từ chối"

- **BlogController**:
  - Khi tạo bài (actionCreate): Nếu bài chờ duyệt, tạo thông báo cho tất cả admin

### 7. Cập nhật Layout (views/layouts/main.php)
- Thêm widget NotificationWidget vào header, hiển thị trước avatar người dùng

## Cách sử dụng

### Chạy Migration
```bash
cd d:\Softwares\Xampp\htdocs\CuoiKiPHPTenNhom
php yii migrate
```

### Quy trình thông báo

#### Cho người dùng:
1. Người dùng tạo bài viết và gửi duyệt
2. Admin duyệt hoặc từ chối bài viết
3. Thông báo xuất hiện trong hộp thông báo của người dùng
4. Khi người dùng nhấn vào thông báo, nó sẽ:
   - Đánh dấu là đã đọc
   - Chuyển hướng đến trang bài viết (nếu duyệt) hoặc trang "Bài viết của tôi" (nếu từ chối)

#### Cho admin:
1. Người dùng gửi bài viết chờ duyệt
2. Admin nhận thông báo "Có bài viết chờ duyệt"
3. Khi nhấn vào thông báo, admin sẽ được chuyển đến trang quản lý bài viết

### Các trạng thái thông báo:
- **Chưa đọc** (isread = 0): Hiển thị với nền nhạt nhất
- **Đã đọc** (isread = 1): Hiển thị với nền bình thường
- Khi nhấn vào thông báo chưa đọc hoặc nút "Đánh dấu đã đọc tất cả", thông báo sẽ chuyển sang trạng thái đã đọc

## Ghi chú kỹ thuật

### Frontend (JavaScript):
- Sử dụng Fetch API để gọi các endpoint
- Xử lý CSRF token tự động
- Cập nhật badge số lượng thông báo chưa đọc
- Định dạng lại thời gian (phút trước, giờ trước, ngày trước)
- Modal pop-up tự động đóng khi nhấn ngoài

### Backend (PHP):
- Sử dụng Yii Active Record
- Các endpoint trả về JSON
- Kiểm tra quyền truy cập (chỉ người dùng đăng nhập có thể xem thông báo của họ)
- Tự động tạo thông báo khi có sự kiện

## Files được sửa/tạo
- ✅ migrations/m260611_CreateNotificationsTable.php (TẠO)
- ✅ models/Notification.php (TẠO)
- ✅ controllers/NotificationController.php (TẠO)
- ✅ widgets/NotificationWidget.php (TẠO)
- ✅ widgets/views/notification-widget.php (TẠO)
- ✅ controllers/AdminController.php (CHỈNH SỬA)
- ✅ controllers/BlogController.php (CHỈNH SỬA)
- ✅ views/layouts/main.php (CHỈNH SỬA)
- ✅ database/setup.sql (CHỈNH SỬA)

## Tiếp theo
1. Chạy lệnh: php yii migrate
2. Kiểm tra bảng notifications được tạo trong database
3. Thử tạo bài viết và gửi duyệt để kiểm tra thông báo hoạt động
