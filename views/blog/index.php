<?php
/** @var yii\web\View $this */
/** @var app\models\BlogPost[] $posts */
/** @var yii\data\Pagination $pagination */

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

    <div class="blog-posts">
        <?php if (empty($posts)): ?>
            <div class="alert alert-info">
                <p>Chưa có bài viết nào. Hãy là người đầu tiên chia sẻ kinh nghiệm của bạn!</p>
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

.blog-actions {
    margin-top: 15px;
}

.blog-actions .btn {
    margin: 0 10px;
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
