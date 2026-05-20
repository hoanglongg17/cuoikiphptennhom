<?php





/** @var yii\web\View $this */
/** @var string $keyword */
/** @var app\models\BlogPost[] $posts */
/** @var yii\data\Pagination $pagination */

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\LinkPager;

$this->title = 'Tìm kiếm: ' . Html::encode($keyword);
$this->params['breadcrumbs'][] = ['label' => 'Blog', 'url' => ['blog/index']];
$this->params['breadcrumbs'][] = 'Tìm kiếm';
?>

<div class="blog-search-container">
    <div class="search-header">
        <h1>🔍 Tìm kiếm</h1>
        <p>Kết quả tìm kiếm cho: <strong><?= Html::encode($keyword) ?></strong></p>
        
        <div class="search-form">
            <form method="get" action="<?= Url::to(['blog/search']) ?>">
                <input type="text" name="q" placeholder="Tìm kiếm bài viết..." 
                       value="<?= Html::encode($keyword) ?>" required>
                <button type="submit">Tìm Kiếm</button>
                <a href="<?= Url::to(['blog/index']) ?>" class="btn-cancel">← Quay Lại</a>
            </form>
        </div>
    </div>

    <div class="search-results">
        <?php if (empty($posts)): ?>
            <div class="alert alert-info">
                <p>Không tìm thấy bài viết nào với từ khóa <strong><?= Html::encode($keyword) ?></strong></p>
            </div>
        <?php else: ?>
            <p class="result-count">Tìm thấy <?= $pagination->totalCount ?> kết quả</p>
            
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
.blog-search-container {
    max-width: 900px;
    margin: 0 auto;
    padding: 20px;
}

.search-header {
    text-align: center;
    margin-bottom: 40px;
    padding-bottom: 20px;
    border-bottom: 2px solid #f0f0f0;
}

.search-header h1 {
    font-size: 2.5em;
    margin-bottom: 10px;
    color: #333;
}

.search-header p {
    font-size: 1.1em;
    color: #666;
    margin-bottom: 20px;
}

.search-form {
    display: flex;
    gap: 10px;
    justify-content: center;
    flex-wrap: wrap;
}

.search-form form {
    display: flex;
    gap: 8px;
    max-width: 600px;
}

.search-form input {
    flex: 1;
    padding: 10px 15px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 1em;
}

.search-form button {
    padding: 10px 20px;
    background: #0066cc;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-weight: 500;
}

.search-form button:hover {
    background: #0052a3;
}

.btn-cancel {
    padding: 10px 15px;
    background: #f0f0f0;
    color: #333;
    border: 1px solid #ddd;
    border-radius: 4px;
    text-decoration: none;
}

.btn-cancel:hover {
    background: #e8e8e8;
}

.search-results {
    margin-bottom: 30px;
}

.result-count {
    color: #666;
    margin-bottom: 20px;
    font-size: 0.95em;
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

@media (max-width: 768px) {
    .search-form form {
        flex-direction: column;
        width: 100%;
    }

    .search-form input {
        width: 100%;
    }

    .search-form button,
    .btn-cancel {
        width: 100%;
    }

    .post-meta {
        flex-direction: column;
        gap: 8px;
    }
}
</style>
