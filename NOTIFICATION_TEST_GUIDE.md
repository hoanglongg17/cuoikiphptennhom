# HƯỚNG DẪN KIỂM THỬ HỆ THỐNG THÔNG BÁO

## Bước 1: Chuẩn bị Database

Chạy lệnh migration để tạo bảng notifications:

```bash
cd d:\Softwares\Xampp\htdocs\CuoiKiPHPTenNhom
php yii migrate
```

Hoặc chạy setup.sql để khởi tạo toàn bộ database:

```bash
cd d:\Softwares\Xampp\htdocs\CuoiKiPHPTenNhom
mysql -u root andiflashcarddb < database/setup.sql
```

## Bước 2: Kiểm thử cho Người dùng

### Kiểm thử A: Gửi bài viết chờ duyệt
1. Đăng nhập với tài khoản người dùng (ví dụ: nguyenvana@gmail.com / 123456)
2. Tạo một bài viết mới
3. Chọn trạng thái "Gửi duyệt"
4. Bài viết sẽ được gửi với trạng thái "pending"
5. **Kết quả mong đợi**: Admin sẽ nhận được thông báo "Có bài viết chờ duyệt"

### Kiểm thử B: Nhân thông báo duyệt bài
1. Đăng nhập với tài khoản admin (admin@gmail.com / 123456)
2. Vào trang Quản lý bài viết
3. Tìm bài viết chờ duyệt của người dùng
4. Nhấn nút "Duyệt" (Approve)
5. **Kết quả mong đợi**:
   - Bài viết được xuất bản
   - Người dùng nhận được thông báo "Bài viết đã được duyệt"
   - Icon thông báo hiển thị badge số (ví dụ: 🔔 1)

### Kiểm thử C: Xem thông báo
1. Người dùng nhấn vào icon 🔔 ở header
2. **Kết quả mong đợi**:
   - Modal hiển thị danh sách thông báo
   - Thông báo chưa đọc có nền nhạt hơn
   - Mỗi thông báo hiển thị:
     - Icon thích hợp (✅ cho duyệt, ❌ cho từ chối, ⏳ cho chờ duyệt)
     - Tiêu đề
     - Nội dung
     - Thời gian (ví dụ: "Vừa xong", "5 phút trước")

### Kiểm thử D: Nhấn vào thông báo
1. Từ modal thông báo, nhấn vào một thông báo chưa đọc
2. **Kết quả mong đợi**:
   - Thông báo được đánh dấu là đã đọc (isread = 1)
   - Nền thông báo trở bình thường
   - Trang web chuyển hướng đến URL tương ứng:
     - Nếu duyệt: `/blog/view?id=[postid]` (xem bài viết)
     - Nếu từ chối: `/blog/my-posts` (quay lại bài viết của tôi)

### Kiểm thử E: Đánh dấu tất cả đã đọc
1. Mở modal thông báo
2. Nhấn nút "Đánh dấu tất cả đã đọc"
3. **Kết quả mong đợi**:
   - Tất cả thông báo được đánh dấu là đã đọc
   - Badge số lượng biến mất (nếu không còn thông báo chưa đọc)
   - Toàn bộ thông báo trong danh sách trở bình thường

## Bước 3: Kiểm thử cho Admin

### Kiểm thử F: Nhận thông báo bài chờ duyệt
1. Người dùng tạo bài viết và gửi duyệt
2. Admin nhấn icon 🔔
3. **Kết quả mong đợi**:
   - Thông báo "Có bài viết chờ duyệt" xuất hiện
   - Icon là ⏳
   - Nội dung: "Người dùng [tên] đã gửi bài viết [tên bài] chờ duyệt."

### Kiểm thử G: Từ chối bài viết
1. Admin vào trang Quản lý bài viết
2. Tìm bài viết chờ duyệt
3. Nhấn nút "Từ chối" (Reject)
4. Nhập lý do từ chối (nếu cần)
5. Nhấn xác nhận
6. **Kết quả mong đợi**:
   - Bài viết được đánh dấu là "denied"
   - Người dùng nhận được thông báo "Bài viết bị từ chối"
   - Lý do từ chối được hiển thị trong nội dung thông báo (nếu có)

## Bước 4: Kiểm thử Bảo mật

### Kiểm thử H: Người dùng không thể xem thông báo của người khác
1. Kiểm tra endpoint `/notification/list` với 2 tài khoản khác nhau
2. **Kết quả mong đợi**: Mỗi tài khoản chỉ thấy thông báo của chính họ

### Kiểm thử I: Người dùng chưa đăng nhập không thể xem thông báo
1. Truy cập `/notification/list` mà không đăng nhập
2. **Kết quả mong đợi**: Lỗi "Vui lòng đăng nhập"

## Database Queries để Kiểm thử

```sql
-- Xem tất cả thông báo
SELECT * FROM notifications;

-- Xem thông báo chưa đọc của người dùng
SELECT * FROM notifications WHERE userid = 2 AND isread = 0;

-- Xem số lượng thông báo chưa đọc
SELECT COUNT(*) FROM notifications WHERE userid = 2 AND isread = 0;

-- Xem thông báo theo loại
SELECT * FROM notifications WHERE type = 'approved';
SELECT * FROM notifications WHERE type = 'rejected';
SELECT * FROM notifications WHERE type = 'pending';
```

## Các API Endpoints

### 1. Lấy danh sách thông báo
```
GET /notification/list
Kết quả: JSON array các thông báo
```

### 2. Đánh dấu một thông báo là đã đọc
```
POST /notification/mark-as-read?id=[notificationid]
Kết quả: {success: true/false, redirectUrl: '...'}
```

### 3. Đánh dấu tất cả thông báo là đã đọc
```
POST /notification/mark-all-as-read
Kết quả: {success: true/false}
```

### 4. Lấy số lượng thông báo chưa đọc
```
GET /notification/count-unread
Kết quả: {count: number}
```

## Các Trạng thái

- **isread = 0**: Chưa đọc (nền nhạt, badge hiển thị)
- **isread = 1**: Đã đọc (nền bình thường)

## Loại thông báo

- **approved**: Bài viết được duyệt ✅
- **rejected**: Bài viết bị từ chối ❌
- **pending**: Bài viết chờ duyệt (dành cho admin) ⏳

## Troubleshooting

### Thông báo không hiển thị
- Kiểm tra xem bảng notifications đã được tạo
- Kiểm tra xem migration đã chạy thành công
- Kiểm tra xem NotificationWidget đã được thêm vào layout

### Badge số không cập nhật
- Kiểm tra xem `notification/count-unread` endpoint có hoạt động
- Kiểm tra Console browser để xem lỗi JavaScript
- Kiểm tra xem CSRF token đã được gửi

### Modal không hiển thị
- Kiểm tra Console browser
- Xem xét lỗi JavaScript
- Kiểm tra xem CSS đã được tải đúng

### Lỗi quyền truy cập
- Kiểm tra xem người dùng đã đăng nhập
- Kiểm tra xem role của người dùng là admin hoặc user

## Khi hoàn thành
Xoá hoặc lưu trữ file này sau khi kiểm thử hoàn tất.
