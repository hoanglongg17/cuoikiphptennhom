# 📦 Hướng Dẫn Cài Đặt Features Mới (Blog & Admin Panel)

## 📋 Checklist Cài Đặt

### ✅ Phase 1: Cập nhật Database

**Bước 1: Backup Database (QUAN TRỌNG!)**
```bash
# Windows - Command Prompt
mysqldump -u root -p andiflashcarddb > backup_andiflashcarddb.sql

# Ubuntu/Linux/Mac - Terminal
mysqldump -u root -p andiflashcarddb > backup_andiflashcarddb.sql
```

**Bước 2: Cập nhật Schema**
```bash
# Tùy chọn 1: Import trực tiếp file setup.sql
mysql -u root -p andiflashcarddb < database/setup.sql

# Tùy chọn 2: Chạy từ MySQL Workbench hoặc PhpMyAdmin
# - Mở file: database/setup.sql
# - Copy toàn bộ
# - Paste vào SQL Editor
# - Thực thi (Execute)
```

**Bước 3: Xác minh Database**
```sql
-- Kiểm tra table mới
SHOW TABLES;
-- Nên hiển thị: blogcomments, blogposts, ... (2 table mới)

-- Kiểm tra cột 'role' trong users
DESC users;
-- Nên có column 'role' với default value 'user'

-- Kiểm tra Admin user
SELECT * FROM users WHERE role = 'admin';
-- Nên có ít nhất 1 user với role = 'admin'
```

---

### ✅ Phase 2: Kiểm Tra Files Đã Tạo

**Models:**
```
✓ app/models/BlogPost.php (NEW)
✓ app/models/BlogComment.php (NEW)
✓ app/models/User.php (UPDATED - thêm isAdmin(), getBlogPosts())
✓ app/models/Deck.php (No change required)
```

**Controllers:**
```
✓ app/controllers/AdminController.php (NEW)
✓ app/controllers/BlogController.php (NEW)
✓ app/controllers/SiteController.php (No major changes needed)
```

**Views:**
```
✓ app/views/admin/dashboard.php (NEW)
✓ app/views/admin/blog-list.php (NEW)
✓ app/views/admin/blog-form.php (NEW)
✓ app/views/admin/blog-comments.php (NEW)
✓ app/views/blog/index.php (NEW)
✓ app/views/blog/view.php (NEW)
✓ app/views/blog/form.php (NEW)
✓ app/views/blog/my-posts.php (NEW)
✓ app/views/site/dashboard.php (UPDATED - thêm link Blog/Admin)
```

---

### ✅ Phase 3: Kiểm Tra Config

**File: config/web.php**
- Không cần thay đổi (đã có cấu hình auth client)

**File: config/db.php**
- Không cần thay đổi (database đã được cấu hình)

---

### ✅ Phase 4: Kiểm Tra Permissions

**Xác minh Access Control:**
- AdminController - Chỉ Admin truy cập
- BlogController - User/Admin tạo, xem công khai

```php
// app/controllers/AdminController.php
// Kiểm tra:
// 1. behaviors() - có access rule cho admin
// 2. Tất cả actions được bảo vệ

// app/controllers/BlogController.php
// Kiểm tra:
// 1. actionIndex() - public (không yêu cầu login)
// 2. actionCreate/Edit/Delete() - yêu cầu login
// 3. Admin có thể publish ngay
```

---

### ✅ Phase 5: Test Features

#### 👤 Test với User Thường

```
1. Tạo tài khoản user mới:
   - Email: user@example.com
   - Password: 123456
   - Role: user (auto)

2. Xem Blog:
   - Truy cập: /blog
   - Nên thấy danh sách bài viết

3. Viết Bài Viết:
   - Nhấp "Viết Bài Mới"
   - Điền thông tin
   - Nhấp "Tạo Bài Viết"
   - Nên thấy: Bài nằm ở /blog/my-posts (trạng thái Nháp)

4. Bình Luận:
   - Truy cập /blog (xem các bài được duyệt)
   - Bình luận trên bài
   - Nên thấy: "Bình luận chờ duyệt"
```

#### 👨‍💼 Test với Admin

```
1. Đăng nhập Admin:
   - Email: admin@andi.com
   - Password: 123456

2. Dashboard:
   - Truy cập: /admin/dashboard
   - Nên thấy: Thống kê, bài viết gần đây, bình luận chờ

3. Quản Lý Bài Viết:
   - /admin/blog-list
   - Nên thấy: Danh sách bài từ tất cả users

4. Duyệt Bài User:
   - /admin/blog-list?status=draft
   - Tìm bài của user
   - Nhấp "📤" để xuất bản
   - Nên thấy: Bài chuyển sang "Xuất Bản"

5. Duyệt Bình Luận:
   - /admin/blog-comments?status=pending
   - Nhấp "✅ Duyệt"
   - Nên thấy: Bình luận xuất hiện trên bài viết
```

---

### ✅ Phase 6: Troubleshooting

#### ❌ Lỗi: "Access Denied" khi vào Admin Panel

**Nguyên nhân:** User không phải admin

**Giải pháp:**
```sql
-- Cập nhật user thành admin
UPDATE users SET role = 'admin' WHERE userid = 1;

-- Xác minh
SELECT userid, email, role FROM users WHERE userid = 1;
```

---

#### ❌ Lỗi: "Blog Post not found" 

**Nguyên nhân:** Slug sai hoặc bài viết chưa được xuất bản

**Giải pháp:**
```sql
-- Kiểm tra bài viết
SELECT postid, title, slug, status, publishedat FROM blogposts LIMIT 5;

-- Cập nhật status
UPDATE blogposts SET status = 'published', publishedat = NOW() WHERE postid = 1;
```

---

#### ❌ Lỗi: Database connection error

**Nguyên nhân:** Database không được cập nhật

**Giải pháp:**
```bash
# Kiểm tra kết nối MySQL
mysql -u root -p -e "SHOW DATABASES;"

# Kiểm tra import đã thành công
mysql -u root -p andiflashcarddb -e "SHOW TABLES;"

# Nên hiển thị các table mới: blogcomments, blogposts
```

---

#### ❌ Lỗi: Slug trùng lặp

**Nguyên nhân:** Tiêu đề tương tự bài cũ

**Giải pháp:**
```sql
-- Xóa dữ liệu cũ
DELETE FROM blogcomments;
DELETE FROM blogposts;

-- Reset auto-increment
ALTER TABLE blogposts AUTO_INCREMENT = 1;
ALTER TABLE blogcomments AUTO_INCREMENT = 1;
```

---

### ✅ Phase 7: Tối Ưu Hóa

#### Cache Tối Ưu
```php
// Thêm vào ActionIndex BlogController
$this->view->cacheControl = 'public, max-age=3600';
```

#### Index Database
```sql
-- Đã tạo trong setup.sql, kiểm tra:
SHOW INDEXES FROM blogposts;
SHOW INDEXES FROM blogcomments;
```

---

### ✅ Phase 8: Bảo Mật

#### CSRF Protection
- ✓ Mặc định bật trong Yii2
- Tất cả form đã có CSRF token

#### XSS Protection
- ✓ Sử dụng `Html::encode()` cho output
- ✓ `nl2br()` cho newlines

#### SQL Injection Protection
- ✓ Sử dụng Query Builder (ORM)
- ✓ Không viết SQL raw queries

#### Access Control
- ✓ AdminController bảo vệ bằng accessControl
- ✓ Kiểm tra quyền trước mỗi action

---

## 🚀 Bước Tiếp Theo (Optional)

### Bạn có thể mở rộng thêm:

1. **Rich Text Editor**
   ```bash
   composer require ckeditor/ckeditor:^4.0
   ```
   - Thêm CKEditor vào content textarea

2. **Search Blog**
   ```php
   // BlogController::actionSearch()
   $query = BlogPost::find()
       ->where(['like', 'title', $searchTerm])
       ->orWhere(['like', 'content', $searchTerm]);
   ```

3. **Email Notifications**
   ```php
   // Gửi email khi có bình luận mới
   Yii::$app->mailer->compose()
       ->setFrom('blog@andi.com')
       ->setTo($post->author->email)
       ->setSubject('Bài viết của bạn có bình luận mới!')
       ->send();
   ```

4. **SEO Optimization**
   - Thêm meta tags
   - Sitemap.xml
   - Robots.txt

---

## 📞 Hỗ Trợ

Nếu gặp vấn đề:
1. Kiểm tra `runtime/logs/app.log`
2. Xem `BLOG_ADMIN_GUIDE.md` để hiểu rõ tính năng
3. Kiểm tra Database schema có đúng không

---

**Installation Guide - v1.0**
**Last Updated: May 16, 2026**
