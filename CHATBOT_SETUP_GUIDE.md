# 🚀 Hướng Dẫn Cấu Hình Gemini API cho Chatbot ANDI

## 📋 Thông Tin Về Chatbot

Chatbot của ANDI là một trợ lý hỗ trợ AI được cung cấp để:
- Giải thích về các loại thẻ (Thẻ Thường, Thẻ Đảo Ngược, Thẻ Nhập Liệu)
- Hướng dẫn quản lý Bộ Thẻ & Từ Vựng
- Giải thích 6 tính năng Blog mạnh mẽ (Tìm kiếm, Danh mục, Nhãn, Đánh giá, Bình luận, Email)
- Giải thích Mode Luyện Tập & Spaced Repetition
- Hỗ trợ các câu hỏi về Dashboard, Thống Kê & User Management
- Cung cấp mẹo học từ vựng hiệu quả
- Giới thiệu toàn bộ tính năng của nền tảng ANDI

## ⚙️ Cấu Hình API Key

API key đã được cấu hình trong file `config/params.php`. Bạn có thể kiểm tra:

```php
'geminiApiKey' => getenv('GEMINI_API_KEY') ?: 'YOUR_API_KEY',
'geminiModel' => getenv('GEMINI_MODEL') ?: 'gemini-2.5-flash',
```
Lấy Google Gemini API Key:
   - Truy cập [Google AI Studio](https://aistudio.google.com)
   - Tạo API key mới
   - Dán vào `params.php`

## ✅ Test Chatbot

1. Khởi động Xampp/Apache
2. Truy cập: `http://localhost/CuoiKiPHPTenNhom/index.php?r=chatbot/index`
3. Hoặc click vào **"📚 Chatbot"** trong sidebar menu
4. Gõ một câu hỏi và bấm gửi

### 🧪 Test Messages (Tin Nhắn Test):

**Về ba loại thẻ cơ bản:**
```
- "Ba loại thẻ trên ANDI là gì?"
- "Thẻ Thường và Thẻ Đảo Ngược khác nhau như thế nào?"
- "Mặt trước thẻ Thường là tiếng gì?"
- "Thẻ Nhập Liệu hoạt động ra sao?"
- "Tôi nên dùng loại thẻ nào để bắt đầu?"
- "Thẻ Nhập Liệu có khó không?"
- "Các loại thẻ có độ khó khác nhau không?"
```

**Về Bộ Thẻ & Từ Vựng:**
```
- "Bộ Thẻ (Deck) là gì?"
- "Cách tạo bộ thẻ mới?"
- "Cách thêm từ vựng vào bộ thẻ?"
- "Tôi có thể chia sẻ bộ thẻ không?"
- "Bộ thẻ công khai là gì?"
- "Cách tìm kiếm bộ thẻ?"
```

**Về các Mode Luyện Tập:**
```
- "ANDI có bao nhiêu mode luyện tập?"
- "Cách chọn mode luyện tập?"
- "Mode Kiểm Tra là gì?"
- "Tôi nên luyện tập bao lâu?"
- "Spaced Repetition là gì?"
```

**Về 6 Tính Năng Blog:**
```
Tìm kiếm:
- "Cách tìm kiếm bài blog trên ANDI?"
- "Tôi có thể tìm kiếm theo tác giả không?"

Danh mục & Nhãn:
- "Danh mục là gì?"
- "Nhãn (Tags) là gì?"
- "Sự khác nhau giữa Danh mục và Nhãn?"

Đánh giá & Bình luận:
- "Cách thích (❤️) bài viết?"
- "Cách viết bình luận dưới bài?"
- "Cách trả lời bình luận của người khác?"

Thông báo Email:
- "ANDI gửi email thông báo gì?"
- "Khi nào tôi nhận được email?"
```

**Về cách đăng Blog:**
```
- "Tôi muốn đăng bài blog, cách nào?"
- "Bài blog cần chọn danh mục & nhãn gì?"
- "Tôi có thể chỉnh sửa bài blog sau khi đăng không?"
- "Bài blog phải tuân theo tiêu chuẩn gì?"
- "Format bài blog hỗ trợ Markdown không?"
```

**Về Dashboard & Thống Kê:**
```
- "Dashboard là gì?"
- "Cách xem tiến độ học của tôi?"
- "Tôi có thể xem thống kê từng bộ thẻ không?"
- "Cách xem lịch sử luyện tập?"
```

**Về User & Quyền Truy Cập:**
```
- "Tôi cần đăng nhập để sử dụng ANDI không?"
- "Cách đăng ký tài khoản?"
- "Có bao nhiêu loại tài khoản?"
- "Sự khác nhau giữa User thường và Admin?"
- "Tôi có thể xem bài blog công khai mà không đăng nhập không?"
```

**Mẹo học tập:**
```
- "Làm sao để học từ vựng hiệu quả?"
- "Tôi hay quên từ vựng, có cách nào giúp?"
- "Cách tạo bộ thẻ tốt?"
- "Cách ghi nhớ từ vựng lâu dài?"
- "Tôi nên kết hợp 3 loại thẻ như thế nào?"
```

**Giới thiệu ANDI:**
```
- "ANDI là gì?"
- "ANDI có những tính năng nào?"
- "Tại sao nên dùng ANDI?"
- "ANDI phù hợp cho ai?"
- "Tôi mới tham gia ANDI, bắt đầu từ đâu?"
```

## 📝 Các Mô Hình Gemini Có Sẵn

truy cập trang để xem danh sách các model: https://ai.google.dev/gemini-api/docs/models?hl=vi

Hiện tại sử dụng:

| Model | Tốc Độ | Chất Lượng | Ghi Chú |
|-------|--------|-----------|--------|
| `gemini-2.5-flash` | ⚡⚡ Rất nhanh | ⭐⭐⭐⭐ | Mới nhất, khuyến nghị |
| `gemini-2.0-flash` | ⚡ Nhanh | ⭐⭐⭐⭐ | Ổn định, nhanh | (outdate)               
| `gemini-1.5-pro` | 🐢 Chậm | ⭐⭐⭐⭐⭐ | Chất lượng cao nhất | (outdate)

## 🔐 Bảo Mật

- API key được lưu trữ an toàn trong `config/params.php`
- Không expose API key công khai
- Nếu sợ lộ key, hãy tạo key mới trong Google AI Studio

## 💡 Tips

- Chatbot hiểu được tất cả các câu hỏi về ANDI (tổng cộng 10 phần)
- Có thể hỏi về mẹo học từ vựng, blog features, admin panel, practice modes, vv.
- Nếu câu hỏi ngoài phạm vi, chatbot sẽ gợi ý quay lại chủ đề chính
- Response thường mất 1-3 giây
- Context của chatbot bao gồm 10 phần thông tin chi tiết về toàn bộ nền tảng ANDI

## 📊 Các Phần Context

1. ✅ Ba Loại Thẻ (Thẻ Thường, Thẻ Đảo Ngược, Thẻ Nhập Liệu)
2. ✅ Bộ Thẻ & Từ Vựng (Deck & Vocabulary Card)
3. ✅ Tính Năng Luyện Tập (4 mode)
4. ✅ 6 Tính Năng Blog (Tìm kiếm, Danh mục, Nhãn, Đánh giá, Bình luận, Email)
5. ✅ User Features (Bộ Thẻ, Blog, Bình luận)
6. ✅ Mode Luyện Tập Chi Tiết
7. ✅ Hệ Thống Spaced Repetition
8. ✅ Giới Thiệu ANDI (Tính năng nổi bật)

---

**📞 Hỗ trợ:** Nếu chatbot không hoạt động, kiểm tra logs trong `runtime/logs/app.log`