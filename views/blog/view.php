<?php



/** @var yii\web\View $this */
/** @var app\models\BlogPost $post */

use yii\helpers\Url;
use yii\helpers\Html;


$this->registerCssFile('@web/css/dashboard.css', ['depends' => [\app\assets\AppAsset::class]]);
$this->registerCssFile('@web/css/blog-view.css', ['depends' => [\yii\bootstrap5\BootstrapAsset::class]]);


$this->title = $post->title;
$this->params['breadcrumbs'][] = ['label' => 'Blog', 'url' => ['blog/index']];
$this->params['breadcrumbs'][] = $post->title;
?>

<div class="blog-view-container">
    <div class="blog-main-content">
        
        <div class="post-card">
            
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

                    <?php if ($post->sharedeckid && $post->sharedDeck): ?>
                        <div class="deck-info-box">
                            <div class="deck-info-title">🎴 Bộ Thẻ Được Chia Sẻ</div>
                            <div class="deck-info-name"><?= Html::encode($post->sharedDeck->name) ?></div>
                            <div class="deck-info-desc"><?= Html::encode($post->sharedDeck->description) ?></div>
                            <div class="deck-actions">
                                <button type="button" class="btn-deck-view" onclick="openDeckModal(<?= $post->sharedDeck->deckid ?>)">
                                    📚 Xem Bộ Thẻ
                                </button>
                                <button type="button" class="btn-deck-save" id="save-deck-button" data-deck-id="<?= $post->sharedDeck->deckid ?>">
                                    💾 Lưu Bộ Thẻ
                                </button>
                            </div>
                        </div>
                    <?php elseif ($post->sharedeckid && !$post->sharedDeck): ?>
                        <div class="deck-info-box" style="background-color: #fee; border-left: 4px solid #f44; padding: 15px;">
                            <div class="deck-info-title" style="color: #c33;">⚠️ Bộ Thẻ Không Còn Tồn Tại</div>
                            <p style="margin: 10px 0 0 0; color: #666; font-size: 14px;">Bộ thẻ mà bài viết này chia sẻ đã bị xóa bởi chủ sở hữu.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            
            <div class="post-body-section">
                <?= $post->content ?>
            </div>

            
            <div class="rating-section">
                <?= $this->render('_rating-widget', ['post' => $post]) ?>
            </div>

            
            <?php
            
            $user = Yii::$app->user->identity;
            $isPostOwner = $user && Yii::$app->user->id === $post->userid;
            $isAdminUser = $user && method_exists($user, 'isAdmin') && $user->isAdmin();
            if ($user && ($isPostOwner || $isAdminUser)): ?>
                <div class="post-actions">
                    <?= Html::beginForm(['blog/delete', 'id' => $post->postid], 'post', ['style' => 'width: 100%; display: flex; justify-content: center;']) ?>
                        <?= Html::submitButton('🗑️ Xóa', [
                            'class' => 'btn-delete',
                            'style' => 'width: 90%; max-width: 420px; justify-content: center;',
                            'onclick' => 'return confirm("Bạn chắc chắn muốn xóa bài viết này?");'
                        ]) ?>
                    <?= Html::endForm() ?>
                </div>
            <?php endif; ?>

            
            <div class="comments-section">
                <h4 class="comments-title">💬 Bình Luận</h4>
                <?php
                
                $topLevelComments = \app\models\BlogNestedComment::find()
                    ->where(['postid' => $post->postid, 'parentcommentid' => null])
                    ->with('user', 'replies')
                    ->orderBy(['createdat' => SORT_DESC])
                    ->all();
                ?>
                <?= $this->render('_nested-comments', ['post' => $post, 'comments' => $topLevelComments]) ?>
            </div>
        </div>

        
        <aside class="blog-sidebar">
            
            <div class="sidebar-widget author-info-widget">
                <img src="<?= Html::encode($post->author->avatarurl ?? 'https://via.placeholder.com/80') ?>" alt="Author" class="author-avatar">
                <div class="author-name"><?= Html::encode($post->author->displayname) ?></div>
                <div class="author-role">Người viết</div>
            </div>

            
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

<?php if ($post->sharedeckid): ?>
<div id="modalDeckView-<?= $post->sharedDeck->deckid ?>" class="modal-overlay" onclick="closeDeckModal(this)">
    <div class="modal-content" onclick="event.stopPropagation()">
        <div class="modal-header">
            <h2>Chi tiết bộ thẻ (ID: <?= $post->sharedDeck->deckid ?>)</h2>
            <button class="btn-close-modal" onclick="closeDeckModal(document.getElementById('modalDeckView-<?= $post->sharedDeck->deckid ?>'))">&times;</button>
        </div>
        
        <div class="deck-top-info">
            <h3 style="margin: 0 0 10px 0; font-size: 22px; color: #2b6cb0;">
                <span class="deck-info-label">Tên bộ thẻ:</span> <?= Html::encode($post->sharedDeck->name) ?>
            </h3>
            <p style="margin: 0; color: #4a5568; font-size: 16px; line-height: 1.6;">
                <span class="deck-info-label">Mô tả:</span> <?= Html::encode($post->sharedDeck->description) ?: 'Chưa có mô tả cho bộ thẻ này.' ?>
            </p>
        </div>

        <hr class="deck-divider">

        <div class="cards-area">
            <h4 style="margin-top: 0; margin-bottom: 20px; color: #2d3748; font-size: 18px;">
                Danh sách thẻ từ vựng (<span id="cardCount-<?= $post->sharedDeck->deckid ?>"><?= count($post->sharedDeck->cards) ?></span> thẻ)
            </h4>
            
            <?php if (empty($post->sharedDeck->cards)): ?>
                <p style="text-align: center; color: #a0aec0; padding: 20px;">Không có từ vựng nào trong bộ thẻ này.</p>
            <?php else: ?>
                <?php foreach($post->sharedDeck->cards as $card): ?>
                    <div class="card-row-display" id="card-row-<?= $card->cardid ?>">
                        <div class="card-main-content">
                            <div class="content-part"><label>Mặt trước</label><div class="content-text" style="color:#3182ce;"><?= Html::encode($card->frontcontent) ?></div></div>
                            <div class="content-part"><label>Mặt sau</label><div class="content-text"><?= Html::encode($card->backcontent) ?></div></div>
                        </div>
                        <div class="card-meta-info">
                            <div class="meta-item"><strong>Phiên âm:</strong> <?= Html::encode($card->pronunciation) ?: 'N/A' ?></div>
                            <div style="width:100%; margin-top:5px;"><strong>Ví dụ:</strong> <em style="color: #718096;">"<?= Html::encode($card->examplesentence) ?: 'Chưa có ví dụ' ?>"</em></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<script>

document.addEventListener('DOMContentLoaded', function() {
    const saveButton = document.getElementById('save-deck-button');
    if (saveButton) {
        saveButton.addEventListener('click', function() {
            const deckId = this.getAttribute('data-deck-id');
            const csrfToken = '<?= Yii::$app->request->csrfToken ?>';
            
            // Check if user is logged in
            if (<?= Yii::$app->user->isGuest ? 'true' : 'false' ?>) {
                alert('Vui lòng đăng nhập để lưu bộ thẻ!');
                return;
            }
            
            // Disable button during processing
            this.disabled = true;
            const originalText = this.textContent;
            this.textContent = '⏳ Đang xử lý...';
            
            // Make AJAX call to duplicate the deck
            fetch('<?= Url::to(['site/ajax-import-deck'], true) ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-CSRF-Token': csrfToken
                },
                body: new URLSearchParams({ deckId: deckId })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    this.textContent = '✓ Đã Lưu!';
                    this.style.background = '#28a745';
                    alert(data.message || 'Bộ thẻ đã được lưu vào tài khoản của bạn!');
                    
                    // Reset button after 3 seconds
                    setTimeout(() => {
                        this.textContent = originalText;
                        this.style.background = '';
                        this.disabled = false;
                    }, 3000);
                } else {
                    // Handle specific error messages from server
                    let errorMsg = data.message || 'Không thể lưu bộ thẻ';
                    
                    if (errorMsg.includes('Không tìm thấy bộ bài')) {
                        errorMsg = '❌ Bộ thẻ này đã bị xóa bởi chủ sở hữu và không thể lưu.';
                    } else if (errorMsg) {
                        errorMsg = '❌ Lỗi: ' + errorMsg;
                    } else {
                        errorMsg = '❌ Không thể lưu bộ thẻ. Vui lòng thử lại.';
                    }
                    
                    alert(errorMsg);
                    this.textContent = originalText;
                    this.style.background = '';
                    this.disabled = false;
                }
            })
            .catch(error => {
                alert('❌ Lỗi kết nối: ' + error.message);
                this.textContent = originalText;
                this.style.background = '';
                this.disabled = false;
            });
        });
    }
});


function openDeckModal(deckId) {
    const modal = document.getElementById('modalDeckView-' + deckId);
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        const mainContent = document.querySelector('.blog-main-content');
        const sidebar = document.querySelector('.blog-sidebar');
        if (mainContent) mainContent.classList.add('blurred');
        if (sidebar) sidebar.classList.add('blurred');
    }
}

function closeDeckModal(element) {
    if (element.classList && element.classList.contains('modal-overlay')) {
        element.style.display = 'none';
        document.body.style.overflow = 'auto';
        const mainContent = document.querySelector('.blog-main-content');
        const sidebar = document.querySelector('.blog-sidebar');
        if (mainContent) mainContent.classList.remove('blurred');
        if (sidebar) sidebar.classList.remove('blurred');
    }
}


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
