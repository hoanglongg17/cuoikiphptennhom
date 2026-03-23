<?php
// Thêm dòng này vào để sửa lỗi "Class Url not found"
use yii\helpers\Url;

$this->title = 'Andi - Học từ vựng';
?>

<!-- Hero section -->
<section class="hero">
  <div class="hero-content">
    <div class="hero-text">
      <h1>
        Nuôi dưỡng <span class="highlight">vốn từ vựng</span> mỗi ngày<br>
        như bạn nuôi chính <span class="highlight">cơ thể bạn</span>
      </h1>
      <p class="subtitle">
        <span class="highlight">Phương pháp Flashcard</span> thông minh giúp bạn ghi nhớ <span class="highlight">gấp 3 lần</span> phương pháp học tập thông thường
      </p>
      <div class="hero-buttons">
        <!-- Nút bắt đầu học trỏ đến trang đăng ký -->
        <a href="<?= Url::to(['site/signup']) ?>" class="btn-primary" style="text-decoration: none; display: inline-block;">Bắt đầu học miễn phí →</a>
        <!-- Link "Tôi đã có tài khoản" trỏ đến trang đăng nhập -->
        <a href="<?= Url::to(['site/login']) ?>" class="btn-secondary" style="text-decoration: none; display: inline-block;">Tôi đã có tài khoản</a>
      </div>
    </div>
    <div class="hero-image">
      <img src="<?= Yii::getAlias('@web') ?>/images/studybanner.png" alt="Học tập minh họa">
    </div>
  </div>
</section>

<!-- Features -->
<section class="features animate">
    <div class="features-header">
        <h2>Tính năng nổi bật</h2>
        <p class="subtitle">Mọi thứ bạn cần để chinh phục từ vựng</p>
        <p class="sub-subtitle">Được thiết kế dựa trên khoa học về trí nhớ</p>
    </div>

    <div class="feature-list">
        <div class="feature">
            <img src="<?= Yii::getAlias('@web') ?>/icons/flashcard.png" alt="Flashcard Icon">
            <h3>Flashcards thông minh</h3>
        </div>
        <div class="feature">
            <img src="<?= Yii::getAlias('@web') ?>/icons/graduation.png" alt="Graduation Icon">
            <h3>Lộ trình cá nhân hóa</h3>
        </div>
        <div class="feature">
            <img src="<?= Yii::getAlias('@web') ?>/icons/statistics.png" alt="Statistics Icon">
            <h3>Thống kê chi tiết</h3>
        </div>
        <div class="feature">
            <img src="<?= Yii::getAlias('@web') ?>/icons/vocabulary.png" alt="Vocabulary Icon">
            <h3>Từ vựng phong phú</h3>
        </div>
    </div>
</section>

<!-- Why choose -->
<section class="why">
    <div class="why-header">
        <h2 class="highlight">Tại sao chọn Andi</h2>
        <p class="subtitle">Học từ vựng hiệu quả hơn bao giờ hết</p>
    </div>

    <div class="benefits">
        <div class="benefit">
            <img src="<?= Yii::getAlias('@web') ?>/icons/free.png" alt="Miễn phí Icon">
            <h3>Miễn phí</h3>
        </div>
        <div class="benefit">
            <img src="<?= Yii::getAlias('@web') ?>/icons/anywhere.png" alt="Học mọi lúc mọi nơi Icon">
            <h3>Học mọi lúc mọi nơi</h3>
        </div>
        <div class="benefit">
            <img src="<?= Yii::getAlias('@web') ?>/icons/science.png" alt="Phương pháp khoa học Icon">
            <h3>Phương pháp khoa học</h3>
        </div>
        <div class="benefit">
            <img src="<?= Yii::getAlias('@web') ?>/icons/needs.png" alt="Theo nhu cầu Icon">
            <h3>Học từ vựng theo từng nhu cầu</h3>
        </div>
    </div>
</section>

<!-- FAQ -->
<section class="faq">
    <div class="faq-header">
        <h2>Câu hỏi thường gặp</h2>
    </div>

    <div class="question">
        <h3>SM-2 (SuperMemo 2) là gì?</h3>
        <p>Thuật toán hoạt động dựa trên phản hồi của người dùng sau mỗi lần ôn tập.</p>
    </div>

    <div class="question">
        <h3>FSRS (Free Spaced Repetition Scheduler) là gì?</h3>
        <p>Thuật toán sử dụng dữ liệu lịch sử học tập để dự đoán thời điểm tối ưu cho việc ôn lại, giúp cải thiện khả năng ghi nhớ lâu dài.</p>
    </div>

    <div class="question">
        <h3>Học từ vựng theo từng nhu cầu là như thế nào?</h3>
        <p>Bạn muốn trau dồi vốn từ ở bất kì lĩnh vực hay mức độ học vấn nào hoàn toàn được chọn lọc bởi chính bạn.</p>
    </div>
</section>

<!-- CTA -->
<section class="cta">
    <div class="cta-content">
        <h2>Bắt đầu hành trình học ngoại ngữ của bạn ngay hôm nay</h2>
        <!-- Nút CTA cuối trang trỏ về trang đăng ký -->
        <a href="<?= Url::to(['site/signup']) ?>" class="btn-primary" style="text-decoration: none; display: inline-block;">Đăng ký miễn phí ngay →</a>
    </div>
</section>