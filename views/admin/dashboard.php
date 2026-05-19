<?php
/** @var yii\web\View $this */
/** @var integer $totalPosts */
/** @var integer $publishedPosts */
/** @var integer $draftPosts */

/** @var appịmodelsịBlogPost[] $recentPosts */

use yii\helpers\Url;
use yii\helpers\Html;

$this->title = 'Admin Panel - Trang Chủ';
$this->params['breadcrumbs'][] = 'Admin';
?>

<div class="admin-dashboard">
    <div class="dashboard-header">
        <h1>🏛️ Admin Panel</h1>
        <p class="subtitle">Quản lý Blog và Nội Dung</p>
    </div>

    <!-- Statistics -->
    <div class="statistics-grid">
        <div class="stat-card">
            <div class="stat-icon">📝</div>
            <div class="stat-content">
                <h3><?= $totalPosts ?></h3>
                <p>Tổng Bài Viết</p>
            </div>
            <div class="stat-detail">
                <small><?= $publishedPosts ?> đã đăng</small>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">✅</div>
            <div class="stat-content">
                <h3><?= $publishedPosts ?></h3>
                <p>Bài Viết Xuất Bản</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">📋</div>
            <div class="stat-content">
                <h3><?= $draftPosts ?></h3>
                <p>Bản Nháp</p>
            </div>
        </div>
    </div>

    <!-- Admin Actions -->
    <div class="admin-actions">
        <h2>Tác Vụ Quản Lý</h2>
        <div class="action-buttons">
            <a href="<?= Url::to(['admin/blog-list']) ?>" class="action-btn">
                <span class="icon">📚</span>
                <span class="label">Quản Lý Bài Viết</span>
            </a>
            <a href="<?= Url::to(['admin/blog-create']) ?>" class="action-btn">
                <span class="icon">✍️</span>
                <span class="label">Tạo Bài Mới</span>
            </a>
        </div>
    </div>

    <!-- Recent Posts -->
    <section class="dashboard-section">
        <h2>📖 Bài Viết Gần Đây</h2>
        <div class="recent-posts">
            <?php if (empty($recentPosts)): ?>
                <p class="text-muted">Chưa có bài viết nào</p>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Tiêu Đề</th>
                            <th>Tác Giả</th>
                            <th>Trạng Thái</th>
                            <th>Ngày Tạo</th>
                            <th>Hành Động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentPosts as $post): ?>
                            <tr>
                                <td><?= Html::encode($post->title) ?></td>
                                <td><?= Html::encode($post->author->displayname) ?></td>
                                <td>
                                    <?php 
                                    $statusLabels = [
                                        'draft' => '<span class="badge badge-warning">Nháp</span>',
                                        'published' => '<span class="badge badge-success">Đã Đăng</span>',
                                        'archived' => '<span class="badge badge-secondary">Lưu Trữ</span>',
                                    ];
                                    echo isset($statusLabels[$post->status]) ? $statusLabels[$post->status] : $post->status;
                                    ?>
                                </td>
                                <td><?= Yii::$app->formatter->asDate($post->createdat, 'php:d/m/Y H:i') ?></td>
                                <td>
                                    <a href="<?= Url::to(['admin/blog-edit', 'id' => $post->postid]) ?>" class="btn btn-sm btn-warning">
                                        ✏️
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </section>
</div>

<style>
.admin-dashboard {
    max-width: 1400px;
    margin: 0 auto;
    padding: 20px;
}

.dashboard-header {
    margin-bottom: 40px;
}

.dashboard-header h1 {
    font-size: 2.5em;
    margin: 0;
    color: #333;
}

.dashboard-header .subtitle {
    color: #888;
    font-size: 1.1em;
    margin-top: 5px;
}

.statistics-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 40px;
}

.stat-card {
    background: linear-gradient(135deg, #1abc9c 0%, #ff4081 100%);
    color: white;
    padding: 25px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    gap: 20px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-5px);
}

.stat-card:nth-child(2) {
    background: linear-gradient(135deg, #1abc9c 0%, #ff4081 100%);
}

.stat-card:nth-child(3) {
    background: linear-gradient(135deg, #1abc9c 0%, #ff4081 100%);
}

.stat-card:nth-child(4) {
    background: linear-gradient(135deg, #1abc9c 0%, #ff4081 100%);
}

.stat-card:nth-child(5) {
    background: linear-gradient(135deg, #1abc9c 0%, #ff4081 100%);
}

.stat-icon {
    font-size: 2.5em;
    opacity: 0.8;
}

.stat-content h3 {
    margin: 0 0 5px;
    font-size: 2em;
}

.stat-content p {
    margin: 0;
    opacity: 0.9;
    font-size: 0.95em;
}

.stat-detail small {
    display: block;
    font-size: 0.8em;
    margin-top: 5px;
    opacity: 0.7;
}

.admin-actions {
    background: #fff;
    padding: 30px;
    border-radius: 8px;
    margin-bottom: 40px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.admin-actions h2 {
    margin-top: 0;
}

.action-buttons {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-top: 15px;
}

.action-btn {
    background: linear-gradient(135deg, #1abc9c 0%, #ff4081 100%);
    color: white;
    padding: 20px;
    border-radius: 8px;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 15px;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    border: 2px solid transparent;
}

.action-btn:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    color: #333;
    text-decoration: none;
}

.action-btn .icon {
    font-size: 2em;
}

.action-btn .label {
    font-weight: 500;
}

.dashboard-section {
    background: #fff;
    padding: 30px;
    border-radius: 8px;
    margin-bottom: 30px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.dashboard-section h2 {
    margin-top: 0;
    margin-bottom: 20px;
}

.table {
    width: 100%;
    border-collapse: collapse;
}

.table th {
    background: #f5f5f5;
    padding: 12px;
    text-align: left;
    font-weight: 600;
    border-bottom: 2px solid #ddd;
}

.table td {
    padding: 12px;
    border-bottom: 1px solid #eee;
}

.table tbody tr:hover {
    background: #f9f9f9;
}

.badge {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.85em;
}

.badge-warning {
    background: #fff3cd;
    color: #856404;
}

.badge-success {
    background: #d4edda;
    color: #155724;
}

.badge-secondary {
    background: #e2e3e5;
    color: #383d41;
}

.alert-section {
    background: #fff5f5;
    border-left: 4px solid #ff6b6b;
}

.pending-comments {
    display: grid;
    gap: 15px;
}

.comment-card {
    background: #fff;
    border: 1px solid #ffe0e0;
    border-radius: 6px;
    padding: 15px;
}

.comment-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
}

.comment-post {
    font-size: 0.9em;
    color: #666;
    margin-bottom: 8px;
}

.comment-content {
    color: #555;
    margin-bottom: 10px;
    line-height: 1.5;
}

.comment-actions {
    display: flex;
    gap: 8px;
}

.btn-sm {
    padding: 6px 12px;
    font-size: 0.85em;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
}

.btn-success {
    background: #d4edda;
    color: #155724;
}

.btn-warning {
    background: #fff3cd;
    color: #856404;
}

@media (max-width: 768px) {
    .statistics-grid {
        grid-template-columns: 1fr;
    }

    .action-buttons {
        grid-template-columns: 1fr;
    }
}
</style>
