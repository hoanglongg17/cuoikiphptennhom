# Hướng Dẫn Sử Dụng Blog & Admin Panel

## 📋 Mục Đích
Tính năng Blog & Admin Panel cho phép:
- **Người dùng**: Chia sẻ bộ thẻ flashcard, viết bài viết, và bình luận
- **Admin**: Quản lý toàn bộ nội dung blog, duyệt bài viết và bình luận

---

## 🔐 Hệ Thống Phân Quyền

### Vai Trò Người Dùng
1. **User (Người Dùng Thường)**
   - Xem danh sách bài viết blog công khai
   - Viết bài viết blog (trạng thái nháp, chờ admin duyệt)
   - Chỉnh sửa/xóa bài viết của chính mình
   - Bình luận trên bài viết (chờ duyệt)
   - Xem bộ thẻ được chia sẻ trong bài viết

2. **Admin (Quản Trị Viên)**
   - Tất cả quyền của User
   - Truy cập Admin Panel
   - Tạo/sửa/xóa bài viết mà không cần duyệt
   - Duyệt hoặc từ chối bài viết của người dùng
   - Duyệt hoặc từ chối bình luận
   - Xem thống kê blog

---

## 🌐 URL Routes

### Blog Routes (Công Khai)
```
/blog                           - Danh sách bài viết
/blog/view?slug=bai-viet        - Xem chi tiết bài viết
/blog/create                    - Tạo bài viết mới (user)
/blog/edit?id=1                 - Chỉnh sửa bài viết
/blog/delete?id=1               - Xóa bài viết
/blog/my-posts                  - Xem bài viết của tôi
```

### Admin Routes (Chỉ Admin)
```
/admin/dashboard                - Trang chủ Admin
/admin/blog-list                - Danh sách bài viết (quản lý)
/admin/blog-list?status=draft   - Lọc bài viết nháp
/admin/blog-create              - Tạo bài viết mới (Admin)
/admin/blog-edit?id=1           - Chỉnh sửa bài viết (Admin)
/admin/blog-delete?id=1         - Xóa bài viết (Admin)
/admin/blog-comments            - Quản lý bình luận
/admin/approve-comment?id=1     - Duyệt bình luận
/admin/reject-comment?id=1      - Từ chối bình luận
/admin/delete-comment?id=1      - Xóa bình luận
```

---

## 🚀 Hướng Dẫn Sử Dụng

### 1️⃣ Cho Người Dùng Thường

#### Viết Bài Blog
1. Đăng nhập vào tài khoản
2. Ở dashboard, nhấp vào "Blog" hoặc "Viết Bài Mới"
3. Điền tiêu đề, tóm tắt, nội dung
4. (Tùy chọn) Chọn bộ thẻ flashcard để chia sẻ
5. Nhấp "Viết Bài Mới" - Bài viết sẽ ở trạng thái "Nháp"
6. Chờ admin duyệt để xuất bản

#### Bình Luận Trên Bài Viết
1. Xem bài viết từ danh sách blog
2. Cuộn xuống phần "Bình Luận"
3. Viết bình luận trong hộp nhập
4. Nhấp "Gửi Bình Luận"
5. Bình luận sẽ hiển thị sau khi admin duyệt

#### Quản Lý Bài Viết Của Tôi
1. Từ dashboard hoặc trang blog, nhấp "Bài Viết Của Tôi"
2. Xem danh sách tất cả bài viết
3. Biểu tượng:
   - 👁️ - Xem bài viết (chỉ có nếu đã xuất bản)
   - ✏️ - Chỉnh sửa
   - 🗑️ - Xóa

---

### 2️⃣ Cho Admin

#### Truy Cập Admin Panel
1. Đăng nhập bằng tài khoản Admin
2. Ở dashboard, nhấp "🏛️ Admin Panel"
3. Xem thống kê tổng quát:
   - Tổng bài viết
   - Bài viết xuất bản
   - Bài viết nháp
   - Bình luận chờ duyệt

#### Quản Lý Bài Viết
1. **Quản Lý Bài Viết**
   - Nhấp "📚 Quản Lý Bài Viết"
   - Lọc theo trạng thái: Tất cả, Đã Đăng, Bản Nháp, Lưu Trữ
   - Xem, chỉnh sửa, hoặc xóa bài viết
   - Công bố bài viết nháp (nút "📤")

2. **Tạo Bài Viết Mới**
   - Nhấp "✍️ Tạo Bài Mới"
   - Điền thông tin bài viết
   - Chọn trạng thái: Nháp, Xuất Bản, hoặc Lưu Trữ
   - Nhấp "Tạo Bài Viết"

#### Duyệt Bình Luận
1. **Xem Bình Luận Chờ Duyệt**
   - Ở dashboard, hoặc nhấp "💬 Duyệt Bình Luận"
   - Mỗi bình luận hiển thị:
     - Tác giả
     - Nội dung bình luận
     - Bài viết liên quan
     - Thời gian tạo

2. **Duyệt Bình Luận**
   - Nhấp "✅ Duyệt" - Bình luận sẽ hiển thị công khai
   - Nhấp "❌ Từ Chối" - Ẩn bình luận
   - Nhấp "🗑️ Xóa" - Xóa vĩnh viễn

---

## 📊 Cấu Trúc Database

### Bảng `users`
```sql
- userid (PRIMARY KEY)
- email
- passwordhash
- displayname
- role (user | admin)  -- NEW
- createdat
- updatedat            -- NEW
```

### Bảng `blogposts` (NEW)
```sql
- postid (PRIMARY KEY)
- userid (FOREIGN KEY)
- title
- slug (UNIQUE)
- content
- excerpt
- status (draft | published | archived)
- views
- sharedeckid (FOREIGN KEY - tùy chọn)
- createdat
- updatedat
- publishedat
```

### Bảng `blogcomments` (NEW)
```sql
- commentid (PRIMARY KEY)
- postid (FOREIGN KEY)
- userid (FOREIGN KEY)
- content
- status (pending | approved | rejected)
- createdat
- updatedat
```

---

## 🔧 Cập Nhật Database

Để áp dụng các thay đổi, chạy file migration:

```bash
# Yii2 migration (nếu có sẵn)
php yii migrate

# Hoặc import SQL trực tiếp
mysql -u root -p andiflashcarddb < setup.sql
```

---

## 🛡️ Kiểm Soát Truy Cập

### AccessControl Filter
- Routes admin được bảo vệ bằng `AccessControl`
- Chỉ Admin mới có quyền truy cập
- User thường chỉ có thể:
  - Xem bài viết công khai
  - Tạo bài viết riêng
  - Chỉnh sửa/xóa bài viết của chính mình

---

## 📝 Tính Năng Futuristic

### Có thể mở rộng thêm:
1. **Tìm kiếm bài viết** - Tìm kiếm full-text trên tiêu đề/nội dung
2. **Danh mục blog** - Phân loại bài viết theo chủ đề
3. **Like/Rating** - Người dùng có thể đánh giá bài viết
4. **Chia sẻ lên mạng** - Chia sẻ bài viết lên Facebook, Twitter
5. **Email notification** - Thông báo khi có bình luận mới
6. **Rich text editor** - Trình soạn thảo WYSIWYG (như Quill.js)

---

## ⚠️ Lưu Ý Quan Trọng

1. **Slug tự động sinh** - Tiêu đề sẽ tự động tạo slug (loại bỏ dấu UTF-8)
2. **Kiểm soát quyền** - User chỉ có thể sửa/xóa bài của chính mình
3. **Bình luận chờ duyệt** - Tất cả bình luận phải được admin duyệt trước
4. **Lượt xem** - Mỗi lần xem bài sẽ tăng counter

---

## 🐛 Xử Lý Lỗi Thường Gặp

| Lỗi | Nguyên Nhân | Giải Pháp |
|-----|-----------|---------|
| "Bạn không có quyền truy cập" | Không phải Admin | Đăng nhập bằng tài khoản Admin |
| "Bài viết không tồn tại" | Slug sai hoặc bài viết bị xóa | Kiểm tra URL hoặc quay lại trang Blog |
| Bài viết không công khai | Trạng thái là "Nháp" hoặc "Lưu Trữ" | Admin xổi xuất bản bài viết |
| Slug trùng lặp | Tiêu đề tương tự bài cũ | Đổi tiêu đề bài viết |

---

## 📞 Hỗ Trợ & Liên Hệ

Nếu gặp vấn đề, vui lòng:
1. Kiểm tra logs: `runtime/logs/app.log`
2. Liên hệ Admin team
3. Báo cáo lỗi với thông tin chi tiết

---

**Cập nhật lần cuối**: May 16, 2026
