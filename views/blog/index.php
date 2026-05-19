<?php
/** @var yii\web\View $this */
/** @var app\models\BlogPost[] $posts */
/** @var app\models\BlogPost[] $featuredPosts */
/** @var yii\data\Pagination $pagination */
/** @var string $keyword */

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\LinkPager;

$this->title = 'Blog - Chia Sẻ Bộ Thẻ & Kinh Nghiệm';
$this->params['breadcrumbs'][] = 'Blog';
?>

<div class="blog-container">
    <div class="blog-header">
        <h1>📝 Blog Andi - Chia Sẻ Kinh Nghiệm Học Tập</h1>
        <p>Khám phá các bộ thẻ hay, mẹo học tập và kinh nghiệm từ cộng đồng</p>
        
        <!-- Search Form -->
        <div class="blog-search">
            <?php $form = \yii\widgets\ActiveForm::begin([
                'method' => 'get',
                'action' => ['blog/index'],
                'options' => ['class' => 'search-form'],
                'fieldConfig' => ['options' => ['tag' => false]],
            ]); ?>
                <div class="input-group">
                    <input type="text" name="q" class="form-control" placeholder="🔍 Tìm kiếm bài viết..." 
                           value="<?= Html::encode($keyword) ?>" />
                    <button type="submit" class="btn btn-primary">Tìm</button>
                    <?php if (!empty($keyword)): ?>
                        <a href="<?= Url::to(['blog/index']) ?>" class="btn btn-secondary">Xóa</a>
                    <?php endif; ?>
                </div>
            <?php $form->end(); ?>
        </div>

        <?php if (Yii::$app->user->isGuest): ?>
            <p class="text-muted">
                <a href="<?= Url::to(['site/login']) ?>">Đăng nhập</a> để tạo bài viết hoặc 
                <a href="<?= Url::to(['site/signup']) ?>">Đăng ký</a> tài khoản mới
            </p>
        <?php else: ?>
            <div class="blog-actions">
                <a href="<?= Url::to(['blog/create']) ?>" class="btn btn-primary">
                    ✍️ Viết Bài Mới
                </a>
                <a href="<?= Url::to(['blog/my-posts']) ?>" class="btn btn-secondary">
                    📚 Bài Viết Của Tôi
                </a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Featured Posts Section (only show when not searching) -->
    <?php if (empty($keyword) && !empty($featuredPosts)): ?>
        <div class="featured-posts-section">
            <h2>⭐ Bài Viết Nổi Bật</h2>
            <p class="featured-subtitle">Những bài viết được yêu thích nhất</p>
            <div class="featured-posts-grid">
                <?php foreach ($featuredPosts as $post): 
                    $likeCount = $post->getLikeCount();
                ?>
                    <article class="featured-post-card">
                        <div class="featured-star">★</div>
                        <div class="featured-header">
                            <h3>
                                <a href="<?= Url::to(['blog/view', 'slug' => $post->slug]) ?>">
                                    <?= Html::encode($post->title) ?>
                                </a>
                            </h3>
                        </div>

                        <div class="featured-meta">
                            <span class="author">👤 <?= Html::encode($post->author->displayname) ?></span>
                            <span class="likes">❤️ <?= $likeCount ?> tim</span>
                        </div>

                        <div class="featured-excerpt">
                            <p><?= Html::encode($post->excerpt ?: substr(strip_tags($post->content), 0, 120)) ?></p>
                        </div>

                        <a href="<?= Url::to(['blog/view', 'slug' => $post->slug]) ?>" class="featured-link">
                            Đọc tiếp →
                        </a>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="blog-posts">
        <?php if (empty($posts)): ?>
            <div class="alert alert-info">
                <p>
                    <?php if (!empty($keyword)): ?>
                        Không tìm thấy bài viết nào với từ khóa "<?= Html::encode($keyword) ?>"
                    <?php else: ?>
                        Chưa có bài viết nào. Hãy là người đầu tiên chia sẻ kinh nghiệm của bạn!
                    <?php endif; ?>
                </p>
            </div>
        <?php else: ?>
            <?php foreach ($posts as $post): ?>
                <article class="blog-post-card">
                    <div class="post-header">
                        <h2>
                            <a href="<?= Url::to(['blog/view', 'slug' => $post->slug]) ?>">
                                <?= Html::encode($post->title) ?>
                            </a>
                        </h2>
                    </div>

                    <div class="post-meta">
                        <span class="author">
                            👤 <strong><?= Html::encode($post->author->displayname) ?></strong>
                        </span>
                        <span class="date">
                            📅 <?= Yii::$app->formatter->asDate($post->publishedat, 'php:d/m/Y') ?>
                        </span>
                        <span class="views">
                            👁️ <?= $post->views ?> lượt xem
                        </span>
                    </div>

                    <div class="post-excerpt">
                        <p><?= Html::encode($post->excerpt ?: substr(strip_tags($post->content), 0, 200)) ?></p>
                    </div>

                    <?php if ($post->sharedeckid): ?>
                        <div class="post-deck-info">
                            <span class="badge badge-info">
                                🎴 Chia sẻ bộ thẻ: <strong><?= Html::encode($post->sharedDeck->name) ?></strong>
                            </span>
                        </div>
                    <?php endif; ?>

                    <div class="post-footer">
                        <a href="<?= Url::to(['blog/view', 'slug' => $post->slug]) ?>" class="btn btn-link">
                            Đọc tiếp →
                        </a>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <div class="pagination-wrap">
        <?= LinkPager::widget([
            'pagination' => $pagination,
            'options' => ['class' => 'pagination justify-content-center'],
            'linkOptions' => ['class' => 'page-link'],
            'activePageCssClass' => 'active',
            'disabledPageCssClass' => 'disabled',
        ]) ?>
    </div>
</div>

<style>
.blog-container {
    max-width: 900px;
    margin: 0 auto;
    padding: 20px;
}

.blog-header {
    text-align: center;
    margin-bottom: 40px;
    padding-bottom: 20px;
    border-bottom: 2px solid #f0f0f0;
}

.blog-header h1 {
    font-size: 2.5em;
    margin-bottom: 10px;
    color: #333;
}

.blog-header p {
    font-size: 1.1em;
    color: #666;
    margin-bottom: 15px;
}

.blog-search {
    margin: 20px 0;
}

.search-form {
    display: flex;
    justify-content: center;
    gap: 10px;
}

.search-form .input-group {
    display: flex;
    gap: 10px;
    width: 100%;
    max-width: 500px;
}

.search-form .input-group input {
    flex: 1;
    padding: 10px 15px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 1em;
}

.search-form .input-group input:focus {
    outline: none;
    border-color: #0066cc;
    box-shadow: 0 0 5px rgba(0, 102, 204, 0.3);
}

.search-form .btn {
    padding: 10px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-weight: 500;
    transition: background-color 0.3s ease;
}

.search-form .btn-primary {
    background-color: #0066cc;
    color: white;
}

.search-form .btn-primary:hover {
    background-color: #0052a3;
}

.search-form .btn-secondary {
    background-color: #6c757d;
    color: white;
}

.search-form .btn-secondary:hover {
    background-color: #5a6268;
}

.blog-actions {
    margin-top: 15px;
}

.blog-actions .btn {
    margin: 0 10px;
}

/* Featured Posts Section */
.featured-posts-section {
    background: linear-gradient(135deg, #fff5e1 0%, #fffde7 100%);
    border-radius: 12px;
    padding: 30px;
    margin-bottom: 40px;
    border-left: 5px solid #ffc107;
}

.featured-posts-section h2 {
    font-size: 2em;
    color: #333;
    margin-bottom: 5px;
    text-align: center;
}

.featured-subtitle {
    text-align: center;
    color: #666;
    margin-bottom: 25px;
    font-size: 0.95em;
}

.featured-posts-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.featured-post-card {
    background: white;
    border-radius: 10px;
    padding: 20px;
    position: relative;
    border: 2px solid #ffc107;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    display: flex;
    flex-direction: column;
}

.featured-post-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(255, 193, 7, 0.3);
}

.featured-star {
    position: absolute;
    top: -10px;
    left: 10px;
    font-size: 2em;
    background: #ffc107;
    color: white;
    width: 45px;
    height: 45px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.featured-header h3 {
    margin: 15px 0 10px 0;
    font-size: 1.2em;
    line-height: 1.4;
}

.featured-header a {
    color: #0066cc;
    text-decoration: none;
    font-weight: 600;
}

.featured-header a:hover {
    text-decoration: underline;
}

.featured-meta {
    font-size: 0.85em;
    color: #666;
    margin-bottom: 12px;
    display: flex;
    gap: 15px;
}

.featured-excerpt {
    color: #555;
    font-size: 0.9em;
    line-height: 1.5;
    margin-bottom: 12px;
    flex-grow: 1;
}

.featured-excerpt p {
    margin: 0;
}

.featured-link {
    color: #ffc107;
    text-decoration: none;
    font-weight: 600;
    align-self: flex-start;
    transition: color 0.3s ease;
}

.featured-link:hover {
    color: #ff9800;
}

.blog-posts {
    margin-bottom: 30px;
}

.blog-post-card {
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
    transition: box-shadow 0.3s ease;
}

.blog-post-card:hover {
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.post-header h2 {
    margin: 0 0 15px 0;
    font-size: 1.5em;
}

.post-header a {
    color: #0066cc;
    text-decoration: none;
}

.post-header a:hover {
    text-decoration: underline;
}

.post-meta {
    font-size: 0.95em;
    color: #888;
    margin-bottom: 15px;
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
}

.post-excerpt {
    color: #555;
    line-height: 1.6;
    margin-bottom: 15px;
}

.post-deck-info {
    margin: 15px 0;
}

.badge {
    display: inline-block;
    padding: 5px 10px;
    border-radius: 4px;
    font-size: 0.9em;
}

.badge-info {
    background-color: #e3f2fd;
    color: #1976d2;
}

.post-footer {
    text-align: right;
}

.btn-link {
    color: #0066cc;
    text-decoration: none;
    font-weight: 500;
}

.btn-link:hover {
    text-decoration: underline;
}

.pagination-wrap {
    text-align: center;
    margin-top: 30px;
}
</style>
