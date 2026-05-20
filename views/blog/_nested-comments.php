<?php
/** @var yii\web\View $this */
/** @var app\models\BlogPost $post */
/** @var app\models\BlogNestedComment[] $comments */

use yii\helpers\Url;
use yii\helpers\Html;

/**
 * Render nested comments tree recursively
 * @param array $comments
 * @param \app\models\BlogPost $post
 * @return void
 */
function renderCommentTree($comments, $post) {
    foreach ($comments as $comment):
        ?>
        <div class="comment-item">
            <div class="comment-header">
                <span class="comment-author">
                    <strong><?= Html::encode($comment->user->displayname) ?></strong>
                </span>
                <span class="comment-date">
                    <?= Yii::$app->formatter->asRelativeTime($comment->createdat) ?>
                </span>
            </div>

            <div class="comment-content">
                <?= Html::encode($comment->content) ?>
            </div>

            <div class="comment-actions">
                <button class="btn-reply" onclick="replyToComment(<?= $comment->commentid ?>)" title="Trả lời">
                    💬 Trả lời
                </button>
                <?php
                /** @var \app\models\User|null $currentUser */
                $currentUser = Yii::$app->user->identity;
                $canDelete = $comment->userid == Yii::$app->user->id || ($currentUser && method_exists($currentUser, 'isAdmin') && $currentUser->isAdmin());
                if ($canDelete): ?>
                    <?= Html::beginForm(['blog/delete-comment', 'id' => $comment->commentid], 'post', ['style' => 'display:inline;']) ?>
                        <?= Html::submitButton('🗑️ Xóa', [
                            'class' => 'btn-delete',
                            'onclick' => 'return confirm("Xác nhận xóa bình luận?");'
                        ]) ?>
                    <?= Html::endForm() ?>
                <?php endif; ?>
            </div>

            <!-- Reply form -->
            <div class="reply-form-container" id="reply-form-<?= $comment->commentid ?>" style="display: none;">
                <form action="<?= Url::to(['blog/add-comment', 'postid' => $post->postid]) ?>" method="post" class="nested-comment-form">
                    <?= Html::hiddenInput('BlogNestedComment[parentcommentid]', $comment->commentid) ?>
                    <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>
                    <textarea name="BlogNestedComment[content]" placeholder="Viết trả lời..." required></textarea>
                    <div class="form-actions">
                        <button type="submit" class="btn-submit">Gửi</button>
                        <button type="button" class="btn-cancel" onclick="cancelReply(<?= $comment->commentid ?>)">Hủy</button>
                    </div>
                </form>
            </div>

            <!-- Nested replies -->
            <?php if (!empty($comment->replies)): ?>
                <div class="nested-comments">
                    <?php renderCommentTree($comment->replies, $post) ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
    endforeach;
}

/**
 * Check if current user is admin
 * @return bool
 */
function isAdmin(): bool {
    /** @var \app\models\User|null $user */
    $user = Yii::$app->user->identity;
    return $user && method_exists($user, 'isAdmin') && $user->isAdmin();
}
?>

<div class="blog-comments-section">
    <?php $totalComments = \app\models\BlogNestedComment::find()
        ->where(['postid' => $post->postid, 'status' => \app\models\BlogNestedComment::STATUS_APPROVED])
        ->count(); ?>
    <h3>💬 Bình luận (<?= $totalComments ?>)</h3>

    <!-- Comment form -->
    <?php if (!Yii::$app->user->isGuest): ?>
        <div class="comment-form-wrapper">
            <h4>Để lại bình luận</h4>
            <form action="<?= Url::to(['blog/add-comment', 'postid' => $post->postid]) ?>" method="post" class="comment-form">
                <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>
                <textarea name="BlogNestedComment[content]" placeholder="Bình luận của bạn..." required></textarea>
                <button type="submit" class="btn-submit">Gửi bình luận</button>
            </form>
        </div>
    <?php else: ?>
        <div class="alert alert-info">
            <p><a href="<?= Url::to(['/site/login']) ?>">Đăng nhập</a> để bình luận.</p>
        </div>
    <?php endif; ?>

    <!-- Comments list -->
    <div class="comments-list">
        <?php if (empty($comments)): ?>
            <p class="no-comments">Chưa có bình luận nào. Hãy là người đầu tiên bình luận!</p>
        <?php else: ?>
            <?php renderCommentTree($comments, $post) ?>
        <?php endif; ?>
    </div>
</div>

<style>
.blog-comments-section {
    margin-top: 40px;
    padding-top: 30px;
    border-top: 2px solid #f0f0f0;
}

.blog-comments-section h3 {
    font-size: 1.5em;
    margin-bottom: 20px;
    color: #333;
}

.comment-form-wrapper {
    background: #f9f9f9;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 30px;
}

.comment-form-wrapper h4 {
    margin-top: 0;
    color: #333;
}

.comment-form,
.nested-comment-form {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.comment-form textarea,
.nested-comment-form textarea {
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-family: inherit;
    font-size: 0.95em;
    resize: vertical;
    min-height: 80px;
}

.comment-form textarea:focus,
.nested-comment-form textarea:focus {
    outline: none;
    border-color: #0066cc;
    box-shadow: 0 0 0 2px rgba(0, 102, 204, 0.1);
}

.form-actions {
    display: flex;
    gap: 10px;
}

.btn-submit {
    padding: 10px 20px;
    background: #0066cc;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-weight: 500;
}

.btn-submit:hover {
    background: #0052a3;
}

.btn-cancel {
    padding: 10px 20px;
    background: #f0f0f0;
    color: #333;
    border: 1px solid #ddd;
    border-radius: 4px;
    cursor: pointer;
}

.btn-cancel:hover {
    background: #e8e8e8;
}

.comments-list {
    margin-top: 20px;
}

/* Force comments and nested containers to be block-level and full-width
   to avoid global styles (e.g. .comment-item { display:flex }) producing
   horizontal wrapping when replies are many. */
.comments-list,
.nested-comments {
    display: block;
    width: 100%;
}

.no-comments {
    color: #999;
    text-align: center;
    padding: 30px 0;
    font-style: italic;
}

.comment-item {
    display: block;
    box-sizing: border-box;
    width: 100%;
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 15px;
    transition: box-shadow 0.2s ease;
}

.comment-item:hover {
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.comment-header {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 10px;
    flex-wrap: wrap;
    font-size: 0.95em;
}

.comment-author {
    color: #333;
}

.comment-date {
    color: #999;
    font-size: 0.9em;
}

.comment-status {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 0.85em;
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
    margin-bottom: 10px;
    word-wrap: break-word;
}

.comment-actions {
    display: flex;
    gap: 10px;
    font-size: 0.9em;
}

.btn-reply,
.btn-delete {
    background: none;
    border: none;
    color: #0066cc;
    cursor: pointer;
    text-decoration: none;
    padding: 0;
    font-size: inherit;
}

.btn-reply:hover,
.btn-delete:hover {
    text-decoration: underline;
}

.btn-delete {
    color: #dc3545;
}

.reply-form-container {
    margin-top: 15px;
    padding: 15px;
    background: #f9f9f9;
    border-radius: 4px;
}

.nested-comments {
    margin-top: 15px;
    margin-left: 20px;
    padding-left: 15px;
    border-left: 2px solid #e0e0e0;
}

.nested-comments .comment-item {
    margin-bottom: 10px;
}

.alert {
    padding: 12px 15px;
    border-radius: 4px;
    margin-bottom: 20px;
}

.alert-info {
    background: #d1ecf1;
    color: #0c5460;
    border: 1px solid #bee5eb;
}

.alert-info a {
    color: #0c5460;
    font-weight: 600;
}

@media (max-width: 768px) {
    .comment-header {
        flex-direction: column;
        gap: 5px;
        align-items: flex-start;
    }

    .comment-form,
    .nested-comment-form {
        gap: 8px;
    }

    .form-actions {
        flex-direction: column;
    }

    .btn-submit,
    .btn-cancel {
        width: 100%;
    }

    .nested-comments {
        margin-left: 10px;
        padding-left: 10px;
    }
}
</style>

<script>
function replyToComment(commentId) {
    const form = document.getElementById('reply-form-' + commentId);
    if (form.style.display === 'none') {
        form.style.display = 'block';
        form.querySelector('textarea').focus();
    } else {
        form.style.display = 'none';
    }
}

function cancelReply(commentId) {
    document.getElementById('reply-form-' + commentId).style.display = 'none';
}
</script>
