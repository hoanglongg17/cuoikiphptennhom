<?php





/** @var yii\web\View $this */
/** @var app\models\BlogCategory $category */
/** @var app\models\BlogPost[] $posts */
/** @var yii\data\Pagination $pagination */

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\LinkPager;

$this->title = $category->name;
$this->params['breadcrumbs'][] = ['label' => 'Blog', 'url' => ['blog/index']];
$this->params['breadcrumbs'][] = $category->name;
?>

<div class="blog-category-container">
    <div class="category-header">
        <div class="category-badge" style="background-color: <?= Html::encode($category->color) ?>;">
            📁
        </div>
        <h1><?= Html::encode($category->name) ?></h1>
        <?php if ($category->description): ?>
            <p class="category-description">
                <?= Html::encode($category->description) ?>
            </p>
        <?php endif; ?>
        <p class="post-count">
            <?= $pagination->totalCount ?> bài viết trong danh mục này
        </p>
    </div>

    <div class="category-sidebar">
        <div class="all-categories">
            <h3>📂 Tất cả danh mục</h3>
            <ul>
                <li>
                    <a href="<?= Url::to(['blog/index']) ?>">
                        ← Xem tất cả bài viết
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <div class="blog-posts">
        <?php if (empty($posts)): ?>
            <div class="alert alert-info">
                <p>Chưa có bài viết nào trong danh mục này.</p>
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
.blog-category-container {
    max-width: 1000px;
    margin: 0 auto;
    padding: 20px;
}

.category-header {
    text-align: center;
    padding: 30px 0;
    border-bottom: 3px solid #f0f0f0;
    margin-bottom: 40px;
}

.category-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 80px;
    height: 80px;
    border-radius: 50%;
    font-size: 40px;
    margin-bottom: 15px;
    opacity: 0.9;
}

.category-header h1 {
    font-size: 2.2em;
    margin: 10px 0;
    color: #333;
}

.category-description {
    font-size: 1.05em;
    color: #666;
    margin: 15px 0;
    max-width: 700px;
    margin-left: auto;
    margin-right: auto;
}

.post-count {
    color: #999;
    font-size: 0.95em;
    margin-top: 10px;
}

.category-sidebar {
    margin-bottom: 30px;
}

.all-categories {
    background: #f9f9f9;
    padding: 15px;
    border-radius: 6px;
    margin-bottom: 20px;
}

.all-categories h3 {
    margin-top: 0;
    font-size: 1em;
    color: #333;
}

.all-categories ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.all-categories li {
    padding: 8px 0;
}

.all-categories a {
    color: #0066cc;
    text-decoration: none;
}

.all-categories a:hover {
    text-decoration: underline;
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
    .category-header {
        padding: 20px 0;
    }

    .category-header h1 {
        font-size: 1.8em;
    }

    .post-meta {
        flex-direction: column;
        gap: 8px;
    }
}
</style>
