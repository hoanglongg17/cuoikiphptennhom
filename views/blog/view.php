<?php
/** @var yii\web\View $this */
/** @var app\models\BlogPost $post */

use yii\helpers\Url;
use yii\helpers\Html;

// Register CSS
$this->registerCssFile('@web/css/blog-view.css', ['depends' => [\yii\bootstrap5\BootstrapAsset::class]]);

// Set page title and breadcrumbs
$this->title = $post->title;
$this->params['breadcrumbs'][] = ['label' => 'Blog', 'url' => ['blog/index']];
$this->params['breadcrumbs'][] = $post->title;
?>

<div class="blog-view-container">
    <div class="blog-main-content">
        <!-- Main Content -->
        <div class="post-card">
            <!-- Post Header with Gradient -->
            <div class="post-header-section">
                <div class="post-header-content">
                    <h1 class="post-title"><?= Html::encode($post->title) ?></h1>
                    
                    <div class="post-meta-header">
                        <div class="meta-item">
                            <span>✍️</span>
                            <span><?= Html::encode($post->author->displayname) ?></span>
                        </div>
                        <div class="meta-item">
                            <span>📅</span>
                            <span><?= Yii::$app->formatter->asDate($post->publishedat, 'php:d/m/Y H:i') ?></span>
                        </div>
                        <div class="meta-item">
                            <span>👁️</span>
                            <span><?= $post->views ?> lượt xem</span>
                        </div>
                    </div>

                    <?php if ($post->sharedeckid): ?>
                        <div class="deck-info-box">
                            <div class="deck-info-title">🎴 Bộ Thẻ Được Chia Sẻ</div>
                            <div class="deck-info-name"><?= Html::encode($post->sharedDeck->name) ?></div>
                            <div class="deck-info-desc"><?= Html::encode($post->sharedDeck->description) ?></div>
                            <div class="deck-actions">
                                <a href="<?= Url::to(['site/vocabset', 'id' => $post->sharedDeck->deckid]) ?>" class="btn-deck-view">
                                    📚 Xem Bộ Thẻ
                                </a>
                                <button type="button" class="btn-deck-copy" id="copy-deck-code" data-deck-id="<?= $post->sharedDeck->deckid ?>">
                                    📋 Sao Chép Mã
                                </button>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Post Body -->
            <div class="post-body-section">
                <?= $post->content ?>
            </div>

            <!-- Rating Section -->
            <div class="rating-section">
                <?= $this->render('_rating-widget', ['post' => $post]) ?>
            </div>

            <!-- Post Actions (Edit/Delete) -->
            <?php
            /** @var \app\models\User|null $user */
            $user = Yii::$app->user->identity;
            $isPostOwner = $user && Yii::$app->user->id === $post->userid;
            $isAdminUser = $user && method_exists($user, 'isAdmin') && $user->isAdmin();
            if ($user && ($isPostOwner || $isAdminUser)): ?>
                <div class="post-actions">
                    <a href="<?= Url::to(['blog/edit', 'id' => $post->postid]) ?>" class="btn-edit">
                        ✏️ Chỉnh sửa
                    </a>
                    <?= Html::beginForm(['blog/delete', 'id' => $post->postid], 'post', ['style' => 'display: inline;']) ?>
                        <?= Html::submitButton('🗑️ Xóa', ['class' => 'btn-delete', 'onclick' => 'return confirm("Bạn chắc chắn muốn xóa bài viết này?");']) ?>
                    <?= Html::endForm() ?>
                </div>
            <?php endif; ?>

            <!-- Comments Section -->
            <div class="comments-section">
                <h4 class="comments-title">💬 Bình Luận</h4>
                <?php
                // Fetch top-level comments
                $topLevelComments = \app\models\BlogNestedComment::find()
                    ->where(['postid' => $post->postid, 'parentcommentid' => null])
                    ->with('user', 'replies')
                    ->orderBy(['createdat' => SORT_DESC])
                    ->all();
                ?>
                <?= $this->render('_nested-comments', ['post' => $post, 'comments' => $topLevelComments]) ?>
            </div>
        </div>

        <!-- Sidebar -->
        <aside class="blog-sidebar">
            <!-- Author Info Widget -->
            <div class="sidebar-widget author-info-widget">
                <img src="<?= Html::encode($post->author->avatarurl ?? 'https://via.placeholder.com/80') ?>" alt="Author" class="author-avatar">
                <div class="author-name"><?= Html::encode($post->author->displayname) ?></div>
                <div class="author-role">Người viết</div>
            </div>

            <!-- Related Posts Widget -->
            <div class="sidebar-widget">
                <h5>📄 Bài Viết Khác</h5>
                <ul class="related-posts">
                    <?php 
                    $otherPosts = \app\models\BlogPost::find()
                        ->where(['status' => 'published'])
                        ->andWhere(['!=', 'postid', $post->postid])
                        ->orderBy(['publishedat' => SORT_DESC])
                        ->limit(5)
                        ->all();
                        
                    if (count($otherPosts) > 0):
                        foreach ($otherPosts as $relatedPost):
                    ?>
                        <li>
                            <a href="<?= Url::to(['blog/view', 'slug' => $relatedPost->slug]) ?>">
                                <?= Html::encode($relatedPost->title) ?>
                            </a>
                        </li>
                    <?php 
                        endforeach;
                    else:
                    ?>
                        <li style="text-align: center; color: #999; font-style: italic;">Chưa có bài viết khác</li>
                    <?php endif; ?>
                </ul>
            </div>

            <!-- Tags Widget -->
            <?php if (!empty($post->tags)): ?>
            <div class="sidebar-widget">
                <h5>🏷️ Nhãn</h5>
                <div class="tags-widget">
                    <?php 
                    $tags = explode(',', $post->tags);
                    foreach ($tags as $tag):
                        $tag = trim($tag);
                        if (!empty($tag)):
                    ?>
                        <span class="tag-badge"><?= Html::encode($tag) ?></span>
                    <?php 
                        endif;
                    endforeach; 
                    ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Category Badge -->
            <?php if ($post->categoryid): ?>
            <div class="sidebar-widget">
                <h5>📁 Danh Mục</h5>
                <?php 
                $category = \app\models\BlogCategory::findOne($post->categoryid);
                if ($category):
                ?>
                    <span class="category-badge" style="background: linear-gradient(135deg, <?= $category->color ?> 0%, <?= $category->color ?> 100%);">
                        <?= Html::encode($category->name) ?>
                    </span>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </aside>
    </div>
</div>

<script>
// Handle Copy Deck Code button
document.addEventListener('DOMContentLoaded', function() {
    const copyButton = document.getElementById('copy-deck-code');
    if (copyButton) {
        copyButton.addEventListener('click', function() {
            const deckId = this.getAttribute('data-deck-id');
            
            // Copy deckId to clipboard
            navigator.clipboard.writeText(deckId).then(function() {
                // Show success message
                const originalText = copyButton.textContent;
                copyButton.textContent = '✓ Đã Sao Chép!';
                copyButton.style.background = '#28a745';
                
                // Reset button after 2 seconds
                setTimeout(function() {
                    copyButton.textContent = originalText;
                    copyButton.style.background = '';
                }, 2000);
            }).catch(function(err) {
                alert('Lỗi khi sao chép: ' + err);
            });
        });
    }
});

// Handle Like/Rating functionality
function toggleLike(button, postId) {
    const url = '<?= Url::to(['blog/like'], true) ?>' + '&id=' + postId;
    const csrfToken = '<?= Yii::$app->request->csrfToken ?>';

    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-CSRF-Token': csrfToken
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.text();
    })
    .then(text => {
        try {
            const data = JSON.parse(text);
            if (data.success) {
                button.classList.toggle('liked', data.liked);
                button.querySelector('.like-icon').textContent = data.liked ? '❤️' : '🤍';
                button.querySelector('.like-count').textContent = data.likeCount;
                button.title = data.liked ? 'Bỏ thích' : 'Thích';
            } else {
                alert(data.message || 'Có lỗi xảy ra!');
            }
        } catch (e) {
            console.error('JSON parse error:', text);
            alert('Lỗi server: ' + text);
        }
    })
    .catch(error => {
        console.error('Fetch error:', error);
        alert('Lỗi kết nối máy chủ: ' + error.message);
    });
}
</script>
