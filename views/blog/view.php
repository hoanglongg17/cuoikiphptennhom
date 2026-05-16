<?php
/** @var yii\web\View $this */
/** @var app\models\BlogPost $post */
/** @var app\models\BlogComment[] $comments */
/** @var app\models\BlogComment $commentModel */

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = $post->title;
$this->params['breadcrumbs'][] = ['label' => 'Blog', 'url' => ['blog/index']];
$this->params['breadcrumbs'][] = $post->title;
?>

<div class="blog-post-view">
    <div class="post-container">
        <article class="post-content">
            <header class="post-header">
                <h1><?= Html::encode($post->title) ?></h1>
                
                <div class="post-meta">
                    <div class="author-info">
                        <strong>✍️ <?= Html::encode($post->author->displayname) ?></strong>
                    </div>
                    <div class="publish-info">
                        📅 Đăng ngày: <?= Yii::$app->formatter->asDate($post->publishedat, 'php:d/m/Y H:i') ?>
                    </div>
                    <div class="view-info">
                        👁️ <?= $post->views ?> lượt xem
                    </div>
                </div>

                <?php if ($post->sharedeckid): ?>
                    <div class="deck-share-info">
                        <h5>🎴 Bộ Thẻ Được Chia Sẻ</h5>
                        <p>
                            <strong><?= Html::encode($post->sharedDeck->name) ?></strong>
                        </p>
                        <p><?= Html::encode($post->sharedDeck->description) ?></p>
                        <div class="deck-actions">
                            <a href="<?= Url::to(['site/vocabset', 'id' => $post->sharedDeck->deckid]) ?>" class="btn btn-sm btn-primary">
                                Xem Bộ Thẻ
                            </a>
                            <button type="button" class="btn btn-sm btn-secondary" id="copy-deck-code" data-deck-id="<?= $post->sharedDeck->deckid ?>">
                                Sao Chép Mã Bộ Thẻ
                            </button>
                        </div>
                    </div>
                <?php endif; ?>
            </header>

            <div class="post-body">
                <?= $post->content ?>
            </div>

            <!-- Rating Widget -->
            <div class="post-rating-section">
                <?= $this->render('_rating-widget', ['post' => $post]) ?>
            </div>

            <?php
            /** @var \app\models\User|null $user */
            $user = Yii::$app->user->identity;
            $isPostOwner = Yii::$app->user->id === $post->userid;
            $isAdminUser = $user && method_exists($user, 'isAdmin') && $user->isAdmin();
            if ($user && ($isPostOwner || $isAdminUser)): ?>
                <div class="post-actions">
                    <a href="<?= Url::to(['blog/edit', 'id' => $post->postid]) ?>" class="btn btn-warning">
                        ✏️ Chỉnh sửa
                    </a>
                    <?= Html::beginForm(['blog/delete', 'id' => $post->postid], 'post', ['style' => 'display: inline;']) ?>
                        <?= Html::submitButton('🗑️ Xóa', ['class' => 'btn btn-danger', 'onclick' => 'return confirm("Bạn chắc chắn muốn xóa bài viết này?");']) ?>
                    <?= Html::endForm() ?>
                </div>
            <?php endif; ?>
        </article>

        <!-- Nested Comments Section -->
        <?php 
        $topLevelComments = \app\models\BlogNestedComment::find()
            ->where(['postid' => $post->postid, 'parentcommentid' => null])
            ->with('user', 'replies')
            ->orderBy(['createdat' => SORT_DESC])
            ->all();
        ?>
        <?= $this->render('_nested-comments', ['post' => $post, 'comments' => $topLevelComments]) ?>
    </div>

    <!-- Sidebar -->
    <aside class="post-sidebar">
        <div class="sidebar-widget">
            <h4>Bài Viết Khác</h4>
            <ul class="related-posts">
                <?php 
                $otherPosts = \app\models\BlogPost::find()
                    ->where(['status' => 'published'])
                    ->andWhere(['!=', 'postid', $post->postid])
                    ->orderBy(['publishedat' => SORT_DESC])
                    ->limit(5)
                    ->all();
                    
                foreach ($otherPosts as $relatedPost):
                ?>
                    <li>
                        <a href="<?= Url::to(['blog/view', 'slug' => $relatedPost->slug]) ?>">
                            <?= Html::encode($relatedPost->title) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </aside>
</div>

<style>

/* ==================== POST CONTAINER ==================== */
.post-container {
    display: flex;
    flex-direction: column;
    gap: 0;
}

/* ==================== POST HEADER ==================== */
.post-header {
    background: linear-gradient(135deg, #f5f7fa 0%, #ffffff 100%);
    padding: 40px 50px;
    border-radius: 12px;
    margin-bottom: 30px;
    border-left: 5px solid #0066cc;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.post-header h1 {
    margin: 0 0 25px 0;
    font-size: 2.4em;
    font-weight: 700;
    color: #1a1a1a;
    line-height: 1.2;
}

.post-meta {
    display: flex;
    align-items: center;
    gap: 25px;
    font-size: 0.95em;
    color: #666;
    margin-bottom: 0;
    flex-wrap: wrap;
    padding-bottom: 20px;
    border-bottom: 1px solid rgba(0, 102, 204, 0.1);
}

.author-info {
    display: flex;
    align-items: center;
    gap: 8px;
}

.author-info strong {
    color: #1a1a1a;
    font-weight: 600;
}

.publish-info,
.view-info {
    display: flex;
    align-items: center;
    gap: 5px;
}

/* ==================== DECK SHARE INFO ==================== */
.deck-share-info {
    background: linear-gradient(135deg, #fff8f0 0%, #fffbf5 100%);
    border: 2px solid #ffa500;
    border-radius: 8px;
    padding: 20px;
    margin-top: 25px;
    margin-bottom: 0;
    border-left: none;
    box-sizing: border-box;
}

.deck-share-info h5 {
    margin: 0 0 12px 0;
    color: #ff8c00;
    font-size: 1em;
    font-weight: 600;
}

.deck-share-info p {
    margin: 8px 0;
    color: #555;
    font-size: 0.95em;
}

.deck-share-info .deck-actions {
    display: flex;
    gap: 10px;
    margin-top: 15px;
    flex-wrap: wrap;
}

.deck-share-info .btn {
    padding: 8px 16px;
    background: #ff8c00;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
    font-weight: 500;
    transition: background 0.3s;
    font-size: 0.9em;
}

.deck-share-info .btn-primary {
    background: #0066cc;
}

.deck-share-info .btn-primary:hover {
    background: #0052a3;
}

.deck-share-info .btn-secondary {
    background: #666;
}

.deck-share-info .btn-secondary:hover {
    background: #555;
}

.deck-share-info .btn-sm:hover {
    opacity: 0.9;
}

/* ==================== POST BODY ==================== */
.post-body {
    background: #ffffff;
    padding: 45px 50px;
    border-radius: 12px;
    font-size: 1.05em;
    line-height: 1.85;
    color: #445;
    margin-bottom: 30px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
}

.post-body h2,
.post-body h3 {
    margin-top: 30px;
    margin-bottom: 15px;
    color: #1a1a1a;
    font-weight: 700;
}

.post-body h2 {
    font-size: 1.8em;
    margin-top: 40px;
}

.post-body h3 {
    font-size: 1.4em;
}

.post-body p {
    margin-bottom: 18px;
}

.post-body ul,
.post-body ol {
    margin-bottom: 18px;
    padding-left: 30px;
}

.post-body li {
    margin-bottom: 8px;
}

.post-body pre {
    background: #2d2d2d;
    color: #f8f8f2;
    padding: 20px;
    border-radius: 6px;
    overflow-x: auto;
    margin-bottom: 18px;
    font-size: 0.9em;
}

.post-body code {
    background: #f4f4f4;
    padding: 2px 6px;
    border-radius: 3px;
    font-size: 0.9em;
}

.post-body blockquote {
    border-left: 4px solid #0066cc;
    padding-left: 20px;
    margin: 20px 0;
    color: #666;
    font-style: italic;
}

/* ==================== RATING WIDGET (in post view) ==================== */
.post-rating-section {
    background: linear-gradient(135deg, #f0f7ff 0%, #ffffff 100%);
    padding: 30px 50px;
    border-radius: 12px;
    margin-bottom: 30px;
    border: 2px solid rgba(0, 102, 204, 0.1);
    box-shadow: 0 2px 8px rgba(0, 102, 204, 0.05);
}

/* ==================== POST ACTIONS ==================== */
.post-actions {
    display: flex;
    gap: 12px;
    padding: 0;
    border-top: none;
    margin-bottom: 30px;
}

.post-actions .btn {
    padding: 12px 24px;
    font-size: 0.95em;
    border-radius: 6px;
    border: none;
    cursor: pointer;
    font-weight: 500;
    transition: all 0.3s;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.post-actions .btn-warning {
    background: #ffa500;
    color: white;
}

.post-actions .btn-warning:hover {
    background: #ff8c00;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(255, 165, 0, 0.3);
}

.post-actions .btn-danger {
    background: #e74c3c;
    color: white;
}

.post-actions .btn-danger:hover {
    background: #c0392b;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(231, 76, 60, 0.3);
}

/* ==================== COMMENTS SECTION ==================== */
.blog-comments-section {
    background: #ffffff;
    padding: 45px 50px;
    border-radius: 12px;
    margin-top: 0;
    border-top: none;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
}

.blog-comments-section h3 {
    margin-top: 0;
    margin-bottom: 30px;
    font-size: 1.6em;
    color: #1a1a1a;
    border-bottom: 2px solid #0066cc;
    padding-bottom: 15px;
}

.comment-form-wrapper {
    background: #f9fafb;
    padding: 25px;
    border-radius: 8px;
    margin-bottom: 30px;
    border: 2px solid #f0f0f0;
}

.comment-form-wrapper h4 {
    margin-top: 0;
    margin-bottom: 15px;
    color: #1a1a1a;
    font-size: 1.1em;
}

.comment-form-wrapper textarea {
    width: 100%;
    padding: 12px;
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    font-family: inherit;
    font-size: 0.95em;
    resize: vertical;
    min-height: 100px;
    transition: border-color 0.3s;
}

.comment-form-wrapper textarea:focus {
    outline: none;
    border-color: #0066cc;
    box-shadow: 0 0 0 3px rgba(0, 102, 204, 0.1);
}

.comment-form button {
    background: #0066cc;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 500;
    margin-top: 12px;
    transition: background 0.3s;
}

.comment-form button:hover {
    background: #0052a3;
}

.comments-list {
    margin-top: 20px;
}

.no-comments {
    text-align: center;
    color: #999;
    padding: 40px 20px;
    font-style: italic;
}

.comment-item {
    background: #f9fafb;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 15px;
    transition: box-shadow 0.3s;
}

.comment-item:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.comment-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 12px;
    flex-wrap: wrap;
    font-size: 0.95em;
}

.comment-author {
    font-weight: 600;
    color: #1a1a1a;
}

.comment-date {
    color: #999;
    font-size: 0.85em;
}

.comment-status {
    font-size: 0.75em;
    padding: 4px 8px;
    border-radius: 4px;
    font-weight: 500;
}

.comment-status.pending {
    background: #fff3cd;
    color: #856404;
}

.comment-status.spam {
    background: #f8d7da;
    color: #721c24;
}

.comment-content {
    color: #555;
    line-height: 1.6;
    margin: 12px 0;
}

.comment-actions {
    display: flex;
    gap: 10px;
    margin-top: 12px;
    flex-wrap: wrap;
}

.btn-reply,
.btn-delete {
    padding: 6px 12px;
    font-size: 0.85em;
    border: 1px solid #ddd;
    border-radius: 4px;
    background: white;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-reply {
    color: #0066cc;
    border-color: #0066cc;
}

.btn-reply:hover {
    background: #f0f7ff;
}

.btn-delete {
    color: #e74c3c;
    border-color: #e74c3c;
}

.btn-delete:hover {
    background: #fadbd8;
}

.nested-comments {
    margin-left: 30px;
    margin-top: 15px;
    padding-top: 15px;
    border-top: 1px solid #e0e0e0;
}

.reply-form-container {
    background: white;
    padding: 15px;
    border-radius: 6px;
    margin-top: 15px;
    border: 1px solid #f0f0f0;
}

.nested-comment-form {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.nested-comment-form textarea {
    padding: 10px;
    border: 1px solid #e0e0e0;
    border-radius: 4px;
    font-family: inherit;
    font-size: 0.9em;
    min-height: 70px;
}

.form-actions {
    display: flex;
    gap: 8px;
}

.btn-submit,
.btn-cancel {
    padding: 8px 16px;
    font-size: 0.85em;
    border-radius: 4px;
    border: none;
    cursor: pointer;
    font-weight: 500;
    transition: all 0.2s;
}

.btn-submit {
    background: #0066cc;
    color: white;
}

.btn-submit:hover {
    background: #0052a3;
}

.btn-cancel {
    background: #f0f0f0;
    color: #333;
}

.btn-cancel:hover {
    background: #e0e0e0;
}

/* ==================== SIDEBAR ==================== */
.post-sidebar {
    position: sticky;
    top: 30px;
    height: fit-content;
}

.sidebar-widget {
    background: white;
    padding: 25px;
    border-radius: 12px;
    border: 1px solid #e0e0e0;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.sidebar-widget h4 {
    margin-top: 0;
    margin-bottom: 20px;
    font-size: 1.2em;
    color: #1a1a1a;
    border-bottom: 2px solid #0066cc;
    padding-bottom: 12px;
    font-weight: 700;
}

.related-posts {
    list-style: none;
    padding: 0;
    margin: 0;
}

.related-posts li {
    margin-bottom: 12px;
}

.related-posts a {
    display: block;
    padding: 12px 15px;
    color: #0066cc;
    text-decoration: none;
    border-radius: 6px;
    border-left: 3px solid transparent;
    transition: all 0.3s;
    font-weight: 500;
    line-height: 1.5;
}

.related-posts a:hover {
    background: #f0f7ff;
    border-left-color: #0066cc;
    padding-left: 18px;
}

/* ==================== RESPONSIVE ==================== */
@media (max-width: 1024px) {
    .blog-post-view {
        grid-template-columns: 1fr;
        gap: 30px;
    }

    .post-sidebar {
        position: static;
    }
}

@media (max-width: 768px) {
    .blog-post-view {
        padding: 20px 15px;
    }

    .post-header {
        padding: 25px 20px;
    }

    .post-header h1 {
        font-size: 1.8em;
    }

    .post-meta {
        gap: 15px;
        font-size: 0.9em;
    }

    .post-body {
        padding: 25px 20px;
    }

    .blog-comments-section {
        padding: 25px 20px;
    }

    .comment-form-wrapper,
    .nested-comments {
        margin-left: 0;
    }

    .post-actions {
        flex-direction: column;
    }

    .post-actions .btn {
        width: 100%;
        justify-content: center;
    }
}

@media (max-width: 480px) {
    .post-header {
        padding: 20px 15px;
    }

    .post-header h1 {
        font-size: 1.4em;
    }

    .post-meta {
        flex-direction: column;
        gap: 8px;
    }

    .post-body,
    .blog-comments-section {
        padding: 20px 15px;
    }

    .blog-comments-section h3 {
        font-size: 1.3em;
    }
}
</style>

<script>
// Handle Copy Deck Code button
document.addEventListener('DOMContentLoaded', function() {
    const copyButton = document.getElementById('copy-deck-code');
    if (copyButton) {
        copyButton.addEventListener('click', function() {
            const deckId = this.getAttribute('data-deck-id');
            const deckCode = 'DECK-' + deckId + '-' + new Date().getTime();
            
            // Copy to clipboard
            navigator.clipboard.writeText(deckCode).then(function() {
                // Show success message
                const originalText = copyButton.textContent;
                copyButton.textContent = '✓ Đã Sao Chép!';
                copyButton.style.background = '#28a745';
                
                // Reset button after 2 seconds
                setTimeout(function() {
                    copyButton.textContent = originalText;
                    copyButton.style.background = '#666';
                }, 2000);
            }).catch(function(err) {
                alert('Lỗi khi sao chép: ' + err);
            });
        });
    }
});
</script>
