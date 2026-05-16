# 📚 Hướng Dẫn Các Tính Năng Blog Mới

## 🎯 Tổng Quan

Dự án Andi Flashcard Master đã được mở rộng với 6 tính năng blog mới để tăng cương khả năng tương tác và quản lý nội dung:

1. **🔍 Tìm Kiếm Blog** - Tìm kiếm bài viết theo từ khóa
2. **📂 Phân Loại Bài Viết** - Phân loại bài viết theo danh mục
3. **⭐ Đánh Giá/Thích** - Người dùng có thể thích và đánh giá bài viết
4. **📧 Thông Báo Email** - Hệ thống thông báo tự động cho bình luận
5. **📋 Nhãn Bài Viết** - Dán nhãn cho bài viết để dễ tìm kiếm
6. **💬 Trả Lời Bình Luận** - Bình luận lồng với tính năng trả lời

---

## ⚙️ Hướng Dẫn Cài Đặt

### Bước 1: Chạy Migration

Tạo các bảng cơ sở dữ liệu mới:

```bash
php yii migrate m260516_BlogFeatures
```

Các bảng được tạo:
- `blogcategories` - Danh mục bài viết
- `blogtags` - Nhãn bài viết
- `post_tags` - Liên kết bài viết và nhãn (M:M)
- `blogratings` - Đánh giá/Thích bài viết
- `blog_nested_comments` - Bình luận lồng với trả lời
- `email_notifications` - Hàng đợi thông báo email

### Bước 2: Kiểm Tra Cấu Hình Email (Nếu Dùng)

Cập nhật file `config/web.php`:

```php
'components' => [
    'mailer' => [
        'class' => 'yii\swiftmailer\Mailer',
        'useFileTransport' => false, // true = debug mode (lưu email vào file)
        'transport' => [
            'class' => 'Swift_SmtpTransport',
            'host' => 'smtp.gmail.com',
            'port' => 587,
            'encryption' => 'tls',
            'username' => 'your-email@gmail.com',
            'password' => 'your-password',
        ],
    ],
],
```

---

## 📖 Các Tính Năng Chi Tiết

### 1️⃣ Tìm Kiếm Blog (Search)

#### Người Dùng:
- Truy cập `/blog/search?q=keyword` để tìm kiếm
- Tìm kiếm trong tiêu đề, nội dung và mô tả bài viết
- Kết quả hiển thị với phân trang

#### URL:
```
/blog/search?q=từ-khóa-tìm-kiếm
```

#### View:
- `views/blog/search.php` - Trang kết quả tìm kiếm

#### Controller:
- `BlogController::actionSearch($q)` - Xử lý tìm kiếm

#### Cơ Sở Dữ Liệu:
- Sử dụng `BlogPost::search($keyword)` từ model

---

### 2️⃣ Phân Loại Bài Viết (Categories)

#### Tạo Danh Mục:
1. Admin truy cập Admin Panel
2. Tạo danh mục mới với tên, mô tả, màu sắc
3. Hệ thống tự động tạo slug

#### Phân Loại Bài Viết:
- Khi tạo/chỉnh sửa bài viết, chọn danh mục
- Mỗi bài viết chỉ thuộc 1 danh mục

#### URL:
```
/blog/category/slug-danh-mục
```

#### View:
- `views/blog/category.php` - Trang danh mục

#### Model:
- `BlogCategory` - Model danh mục
- `BlogPost->getCategory()` - Lấy danh mục của bài viết
- `BlogPost::findByCategory($categoryId)` - Tìm bài viết theo danh mục

---

### 3️⃣ Đánh Giá/Thích Bài Viết (Rating/Like)

#### Người Dùng:
1. Xem bài viết, nhấn nút ❤️ để thích
2. Nút sẽ chuyển sang ❤️ nếu đã thích
3. Hiển thị số lượt thích và đánh giá trung bình

#### Quản Lý:
- Mỗi người dùng chỉ có 1 đánh giá per bài viết
- Có thể gỡ bỏ đánh giá bằng cách nhấn lại nút

#### API:
```php
// Kiểm tra người dùng đã thích chưa
$post->isLikedByUser($userId);

// Lấy số lượt thích
$post->getLikeCount();

// Lấy đánh giá trung bình
$post->getAverageRating();
```

#### View:
- `views/blog/_rating-widget.php` - Widget đánh giá (sử dụng AJAX)

#### Model:
- `BlogRating` - Model đánh giá

#### Controller:
- `BlogController::actionLike($id)` - AJAX để tích/bỏ thích

---

### 4️⃣ Nhãn Bài Viết (Tags)

#### Tạo/Quản Lý Nhãn:
- Nhãn được tạo tự động khi thêm vào bài viết
- Hoặc admin có thể quản lý sẵn

#### Thêm Nhãn Vào Bài Viết:
1. Khi tạo/chỉnh sửa bài viết
2. Nhập tên nhãn, cách nhau bằng dấu phẩy
3. Hệ thống tự động tạo slug

#### Xem Bài Viết Theo Nhãn:
```
/blog/tag/slug-nhan
```

#### API:
```php
// Thêm nhãn vào bài viết
$post->addTag('Tên nhãn');

// Lấy tất cả nhãn
$post->getTags();

// Tìm bài viết theo nhãn
BlogPost::findByTag($tagId);
```

#### Model:
- `BlogTag` - Model nhãn
- `PostTag` - Bảng liên kết (Many-to-Many)

#### View:
- `views/blog/tag.php` - Trang nhãn

---

### 5️⃣ Trả Lời Bình Luận (Nested Comments)

#### Người Dùng Xem:
1. Xem bài viết, cuộn xuống phần bình luận
2. Bình luận hiển thị dạng cây (tree structure)
3. Nhấn "Trả lời" để trả lời một bình luận cụ thể

#### Người Dùng Bình Luận:
1. Đăng nhập (bắt buộc)
2. Nhập bình luận hoặc trả lời
3. Nhấn "Gửi" - bình luận sẽ chờ duyệt

#### Quản Lý (Admin):
1. Truy cập Admin Panel → Duyệt Bình Luận
2. Xem danh sách bình luận chờ duyệt
3. Hành động: Duyệt, Từ chối, Đánh dấu Spam, Xóa

#### Trạng Thái Bình Luận:
- `pending` - Chờ duyệt
- `approved` - Đã duyệt (hiển thị công khai)
- `rejected` - Bị từ chối (không hiển thị)
- `spam` - Spam

#### Model:
- `BlogNestedComment` - Model bình luận lồng

#### View:
- `views/blog/_nested-comments.php` - Widget bình luận
- `views/admin/comments.php` - Trang duyệt bình luận

#### Controller:
- `BlogController::actionAddComment($postid)` - Thêm bình luận
- `AdminController::actionComments()` - Danh sách bình luận
- `AdminController::actionApproveComment($id)` - Duyệt
- `AdminController::actionRejectComment($id)` - Từ chối
- `AdminController::actionMarkSpam($id)` - Đánh dấu Spam
- `AdminController::actionDeleteComment($id)` - Xóa

---

### 6️⃣ Thông Báo Email (Email Notifications)

#### Các Loại Thông Báo:
1. **COMMENT_ON_POST** - Khi có bình luận mới trên bài viết
   - Gửi tới: Tác giả bài viết
   
2. **REPLY_ON_COMMENT** - Khi có trả lời cho bình luận
   - Gửi tới: Tác giả bình luận gốc

#### Quản Lý Hàng Đợi:
Các email được lưu trong `email_notifications` và gửi qua cron job hoặc queue worker.

#### Chạy Hàng Đợi Email:
```bash
# Gửi tất cả email chờ xử lý
php yii send-notifications

# Gửi 100 email mỗi lần, retry tối đa 5 lần
php yii send-notifications --batch-size=100 --max-retries=5

# Xem thống kê
php yii send-notifications/stats

# Xóa email cũ (>30 ngày)
php yii send-notifications/cleanup --days=30
```

#### Cấu Hình Cron:
Thêm vào crontab để chạy tự động mỗi 5 phút:
```bash
*/5 * * * * cd /path/to/project && php yii send-notifications
```

#### Model:
- `EmailNotification` - Model hàng đợi email

#### Component/Service:
- `EmailNotificationService` - Service xử lý email
  - `notifyCommentOnPost($postId, $commentId)`
  - `notifyReplyOnComment($postId, $commentId, $parentCommentId)`
  - `sendPendingNotifications($batchSize, $maxRetries)`
  - `getStats()`

#### Command:
- `SendNotificationsController` - Console command

---

## 🛠️ Cấu Trúc Database

### Bảng blogcategories
```sql
- categoryid (PK)
- name (unique)
- slug (unique)
- description
- color (hex color, default: #0066cc)
- createdat
```

### Bảng blogtags
```sql
- tagid (PK)
- name (unique)
- slug (unique)
- usagecount (tracking)
- createdat
```

### Bảng post_tags (M:M)
```sql
- postid (FK from blogposts)
- tagid (FK from blogtags)
- PRIMARY KEY (postid, tagid)
```

### Bảng blogratings
```sql
- ratingid (PK)
- postid (FK)
- userid (FK)
- rating (1-5 stars)
- createdat
- UNIQUE (postid, userid) - một người chỉ đánh giá 1 lần per bài
```

### Bảng blog_nested_comments
```sql
- commentid (PK)
- postid (FK)
- userid (FK)
- parentcommentid (FK self, NULL = top-level)
- content
- status (pending/approved/rejected/spam)
- createdat
- updatedat
```

### Bảng email_notifications
```sql
- notificationid (PK)
- userid (FK)
- type (comment_on_post/reply_on_comment)
- relatedpostid (FK)
- relatedcommentid (FK, nullable)
- subject
- status (pending/sent/failed)
- sendattempts
- sentat (nullable)
- createdat
```

### Cột bổ sung cho blogposts
```sql
- categoryid (FK from blogcategories, nullable)
```

---

## 🔐 Kiểm Soát Truy Cập

### Công Khai (?)
- `/blog/index` - Danh sách bài viết
- `/blog/view/{slug}` - Xem bài viết
- `/blog/search?q=...` - Tìm kiếm
- `/blog/category/{slug}` - Xem danh mục
- `/blog/tag/{slug}` - Xem nhãn

### Đăng Nhập (@)
- `/blog/create` - Tạo bài viết
- `/blog/edit/{id}` - Chỉnh sửa (chỉ chủ sở hữu hoặc admin)
- `/blog/delete/{id}` - Xóa (chỉ chủ sở hữu hoặc admin)
- `/blog/add-comment` - Thêm bình luận
- `/blog/like/{id}` - Thích bài viết (AJAX)

### Admin-Only (Admin)
- `/admin/comments` - Duyệt bình luận
- `/admin/approve-comment/{id}` - Duyệt bình luận
- `/admin/reject-comment/{id}` - Từ chối bình luận
- `/admin/mark-spam/{id}` - Đánh dấu spam
- `/admin/delete-comment/{id}` - Xóa bình luận

---

## 📝 Các File Mới

### Models
- `models/BlogCategory.php` - Danh mục
- `models/BlogTag.php` - Nhãn
- `models/PostTag.php` - Liên kết post-tags
- `models/BlogRating.php` - Đánh giá/Thích
- `models/BlogNestedComment.php` - Bình luận lồng
- `models/EmailNotification.php` - Thông báo email

### Views
- `views/blog/search.php` - Trang tìm kiếm
- `views/blog/category.php` - Trang danh mục
- `views/blog/tag.php` - Trang nhãn
- `views/blog/_rating-widget.php` - Widget đánh giá
- `views/blog/_nested-comments.php` - Widget bình luận
- `views/admin/comments.php` - Trang duyệt bình luận

### Components/Services
- `components/EmailNotificationService.php` - Service email

### Commands
- `commands/SendNotificationsController.php` - Console command email

### Migrations
- `migrations/m260516_BlogFeatures.php` - Tạo bảng mới

---

## 🐛 Debugging

### Xem Log Email
```bash
# Email debug mode - lưu vào file thay vì gửi
# file: config/web.php
'useFileTransport' => true, // true = debug mode
// Email sẽ được lưu vào runtime/mail/
```

### Xem Thống Kê Email
```bash
php yii send-notifications/stats
```

### Test Email Notification
```php
// Trong console hoặc test file
use app\components\EmailNotificationService;

// Tạo thông báo giả
EmailNotificationService::notifyCommentOnPost($postId, $commentId);

// Gửi email
php yii send-notifications
```

---

## ✅ Checklist Cài Đặt

- [ ] Chạy migration: `php yii migrate m260516_BlogFeatures`
- [ ] Cấu hình email trong `config/web.php` (nếu dùng)
- [ ] Tạo ít nhất 1 danh mục blog
- [ ] Tạo ít nhất 1 bài viết với danh mục và nhãn
- [ ] Test tìm kiếm: `/blog/search?q=test`
- [ ] Test bình luận và trả lời
- [ ] Test thích bài viết
- [ ] Setup cron job để gửi email (nếu dùng)
- [ ] Test admin panel duyệt bình luận

---

## 🚀 Tiếp Theo

### Tính năng có thể thêm:
1. **Bộ lọc nâng cao** - Lọc theo tìm kiếm + danh mục + nhãn
2. **Widget liên quan** - Gợi ý bài viết liên quan
3. **Share Social** - Chia sẻ trên mạng xã hội
4. **Bookmark** - Lưu bài viết yêu thích
5. **Author Page** - Trang tác giả với tất cả bài viết
6. **Comment Mentions** - Đề cập người dùng trong bình luận (@username)
7. **Email Subscription** - Đăng ký theo dõi danh mục/nhãn

---

## 📧 Hỗ Trợ

Nếu gặp lỗi:
1. Kiểm tra migration đã chạy thành công
2. Xem error log: `runtime/logs/app.log`
3. Test email configuration (debug mode)
4. Kiểm tra database relationships
5. Xem console output: `php yii send-notifications` (nếu email)

---

**Phiên bản: 1.0 - Ngày cập nhật: 2026-05-16**
