<?php
/** @var yii\web\View $this */
/** @var app\models\BlogTag $tag */
/** @var app\models\BlogPost[] $posts */
/** @var yii\data\Pagination $pagination */

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\LinkPager;

$this->title = 'Tag: ' . $tag->name;
$this->params['breadcrumbs'][] = ['label' => 'Blog', 'url' => ['blog/index']];
$this->params['breadcrumbs'][] = 'Tags';
$this->params['breadcrumbs'][] = $tag->name;
?>

<div class="blog-tag-container">
    <div class="tag-header">
        <div class="tag-icon">
            📋
        </div>
        <h1>Tag: <?= Html::encode($tag->name) ?></h1>
        <p class="tag-stats">
            🏷️ <?= $pagination->totalCount ?> bài viết | 👥 <?= $tag->usagecount ?> lượt sử dụng
        </p>
    </div>

    <div class="blog-posts">
        <?php if (empty($posts)): ?>
            <div class="alert alert-info">
                <p>Chưa có bài viết nào với tag này.</p>
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
                            👤 <?= Html::encode($post->author->displayname) ?>
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

                    <?php if (!empty($post->tags)): ?>
                        <div class="post-tags">
                            <?php foreach ($post->tags as $tag): ?>
                                <a href="<?= Url::to(['blog/tag', 'slug' => $tag->slug]) ?>" class="tag-badge">
                                    <?= Html::encode($tag->name) ?>
                                </a>
                            <?php endforeach; ?>
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
    <?php if ($pagination && $pagination->pageCount > 1): ?>
        <div class="pagination-wrap">
            <?= LinkPager::widget([
                'pagination' => $pagination,
                'options' => ['class' => 'pagination justify-content-center'],
                'linkOptions' => ['class' => 'page-link'],
                'activePageCssClass' => 'active',
                'disabledPageCssClass' => 'disabled',
            ]) ?>
        </div>
    <?php endif; ?>
</div>

<style>
.blog-tag-container {
    max-width: 900px;
    margin: 0 auto;
    padding: 20px;
}

.tag-header {
    text-align: center;
    padding: 30px 0;
    border-bottom: 2px solid #f0f0f0;
    margin-bottom: 40px;
}

.tag-icon {
    font-size: 50px;
    margin-bottom: 10px;
}

.tag-header h1 {
    font-size: 2.2em;
    margin: 10px 0;
    color: #333;
}

.tag-stats {
    color: #999;
    font-size: 0.95em;
    margin-top: 10px;
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

.post-tags {
    margin-bottom: 15px;
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.tag-badge {
    display: inline-block;
    background: #f0f0f0;
    color: #0066cc;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 0.85em;
    text-decoration: none;
}

.tag-badge:hover {
    background: #e0e0e0;
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

.alert {
    padding: 15px;
    border-radius: 4px;
    margin-bottom: 20px;
}

.alert-info {
    background: #d1ecf1;
    color: #0c5460;
    border: 1px solid #bee5eb;
}

@media (max-width: 768px) {
    .tag-header {
        padding: 20px 0;
    }

    .tag-header h1 {
        font-size: 1.8em;
    }

    .post-meta {
        flex-direction: column;
        gap: 8px;
    }
}
</style>
