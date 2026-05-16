# 🚀 Blog Features - Quick Start (Bắt Đầu Nhanh)

## ⚡ 3 Bước Để Bắt Đầu

### 1️⃣ Chạy Migration (2-3 giây)
```bash
cd d:\Xampp\htdocs\Andi-FlashcardMaster
php yii migrate m260516_BlogFeatures
```

**Output:**
```
Migrated up successfully.
✓ blogcategories table created
✓ blogtags table created  
✓ post_tags table created
✓ blogratings table created
✓ blog_nested_comments table created
✓ email_notifications table created
```

### 2️⃣ Tạo Danh Mục (Categories)
Dùng Admin Panel hoặc SQL:
```sql
INSERT INTO blogcategories (name, slug, description, color) VALUES 
('PHP', 'php', 'Bài viết về PHP', '#ff6b6b'),
('JavaScript', 'javascript', 'Bài viết về JavaScript', '#4ecdc4'),
('Yii2', 'yii2', 'Framework Yii2', '#ffe66d');
```

### 3️⃣ Tạo Bài Viết
1. Đăng nhập
2. Tạo bài viết
3. **Chọn danh mục** trong form
4. **Thêm tag** (ví dụ: php, tutorial)
5. Publish

---

## ✅ Tính Năng Sẵn Sàng Dùng

| Tính Năng | URL | Hành Động |
|-----------|-----|----------|
| 🔍 Tìm Kiếm | `/blog/search?q=php` | Tìm kiếm bài viết |
| 📂 Danh Mục | `/blog/category/php` | Xem theo danh mục |
| 📋 Nhãn | `/blog/tag/tutorial` | Xem theo tag |
| ⭐ Thích | Nút ❤️ | Click để thích |
| 💬 Bình Luận | Phần comment | Viết & trả lời |
| 📧 Email | `php yii send-notifications` | Gửi thông báo |

---

## 🎯 Test Functionality

### Test 1: Tìm Kiếm
```
1. Vào /blog/search?q=php
2. Nên thấy bài viết có từ "php"
```

### Test 2: Danh Mục
```
1. Vào /blog/category/php
2. Nên thấy 3 bài viết PHP
```

### Test 3: Thích Bài Viết
```
1. Xem bài viết
2. Click ❤️ icon
3. Nên thấy ❤️ đỏ + count tăng
```

### Test 4: Bình Luận
```
1. Xem bài viết
2. Đăng nhập
3. Viết bình luận
4. Vào /admin/comments
5. Duyệt bình luận
```

### Test 5: Email (Optional)
```bash
# Setup debug mode trước (optional)
# config/web.php: 'useFileTransport' => true

php yii send-notifications

# Email sẽ save vào runtime/mail/ (debug)
# Hoặc gửi qua SMTP
```

---

## 📁 File Mới + Modified

**16 File Mới:**
✅ 6 Models (Category, Tag, PostTag, Rating, NestedComment, Notification)
✅ 5 Views (search, category, tag, rating-widget, nested-comments)
✅ 2 Components/Commands (EmailService, SendNotificationsCommand)
✅ 1 Admin View (comments)
✅ 2 Documentation Files

**6 File Modified:**
✅ controllers/BlogController.php
✅ controllers/AdminController.php
✅ models/BlogPost.php
✅ views/blog/view.php
✅ views/blog/form.php
✅ views/site/dashboard.php

**Database:**
✅ 1 Migration (m260516_BlogFeatures.php)
✅ 6 New Tables
✅ 1 New Column (blogposts.categoryid)

---

## 🔗 URL Map

```
Blog Public:
├── /blog                           → Danh sách
├── /blog/{slug}                    → Xem bài viết
├── /blog/search?q=keyword          → Tìm kiếm ⭐ NEW
├── /blog/category/{slug}           → Danh mục ⭐ NEW
├── /blog/tag/{slug}                → Nhãn ⭐ NEW
├── /blog/create                    → Tạo (auth)
├── /blog/{id}/edit                 → Chỉnh sửa (auth)
└── /blog/{id}/delete               → Xóa (auth)

Blog AJAX:
├── /blog/like/{id}                 → Thích ⭐ NEW

Blog Admin:
└── /admin/comments                 → Duyệt bình luận ⭐ NEW
    ├── /approve-comment/{id}
    ├── /reject-comment/{id}
    ├── /mark-spam/{id}
    └── /delete-comment/{id}

Console:
├── php yii send-notifications          → Gửi email
├── php yii send-notifications/stats    → Thống kê
└── php yii send-notifications/cleanup  → Xóa cũ
```

---

## 🛠️ Email Configuration (Optional)

Nếu muốn gửi email thật, chỉnh sửa `config/web.php`:

```php
'mailer' => [
    'class' => 'yii\swiftmailer\Mailer',
    'useFileTransport' => false, // true = debug (save vào file)
    'transport' => [
        'class' => 'Swift_SmtpTransport',
        'host' => 'smtp.gmail.com',
        'port' => 587,
        'encryption' => 'tls',
        'username' => 'your-email@gmail.com', // Gmail address
        'password' => 'your-app-password', // App-specific password
    ],
],
```

**Gmail Setup:**
1. Enable 2FA: https://myaccount.google.com/security
2. Generate App Password: https://myaccount.google.com/apppasswords
3. Use app password (không phải Gmail password)

---

## 📝 Database Tables

```sql
-- 6 Bảng Mới
blogcategories          -- Danh mục
blogtags               -- Nhãn
post_tags              -- M:M Post-Tags
blogratings            -- Đánh giá
blog_nested_comments   -- Bình luận
email_notifications    -- Email queue

-- 1 Cột Mới
ALTER TABLE blogposts ADD COLUMN categoryid INT;
```

---

## 🚨 Troubleshooting

### Migration Fails
```bash
# Check if exists
SHOW TABLES LIKE 'blog%';

# Rollback & retry
php yii migrate/down
php yii migrate m260516_BlogFeatures
```

### Comments Not Show
- Check status = 'approved'
- Admin approve tại /admin/comments

### Email Not Working
```bash
# Debug mode - save to file
# config/web.php: 'useFileTransport' => true

php yii send-notifications/stats
cat runtime/logs/app.log
```

---

## ✨ Features Checklist

- [x] 🔍 Search Blog - Tìm kiếm bài viết
- [x] 📂 Categories - Phân loại bài viết
- [x] ⭐ Rating/Like - Đánh giá/thích
- [x] 📧 Email Notifications - Thông báo email
- [x] 📋 Tags - Nhãn bài viết
- [x] 💬 Nested Comments - Trả lời bình luận

---

## 📚 Documentation

- **BLOG_FEATURES_GUIDE.md** (150+ dòng) - Hướng dẫn chi tiết
- **BLOG_FEATURES_IMPLEMENTATION.md** (400+ dòng) - Nội dung triển khai
- **BLOG_FEATURES_QUICK_START.md** (This file) - Bắt đầu nhanh

---

## 🎉 Ready to Go!

```bash
# 1. Run migration
php yii migrate m260516_BlogFeatures

# 2. Create categories (SQL or Admin)
# 3. Create blog posts with category & tags
# 4. Test features at URLs above
# 5. Setup email (optional)
```

**Everything is ready!** 🚀

---

**Version:** 1.0
**Updated:** 2026-05-16
**Status:** ✅ COMPLETE
