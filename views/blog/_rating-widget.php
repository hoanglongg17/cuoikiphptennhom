<?php
/** @var yii\web\View $this */
/** @var app\models\BlogPost $post */

use yii\helpers\Url;
use yii\helpers\Html;

$likeCount = $post->getLikeCount();
$isLikedByUser = Yii::$app->user->isGuest ? false : $post->isLikedByUser(Yii::$app->user->id);
?>

<div class="blog-rating-widget">
    <div class="like-button-container">
        <?php if (!Yii::$app->user->isGuest): ?>
            <button class="btn-like <?= $isLikedByUser ? 'liked' : '' ?>" 
                    data-post-id="<?= $post->postid ?>"
                    onclick="toggleLike(this, <?= $post->postid ?>)"
                    title="<?= $isLikedByUser ? 'Bỏ thích' : 'Thích' ?>">
                <span class="like-icon"><?= $isLikedByUser ? '❤️' : '🤍' ?></span>
                <span class="like-count"><?= $likeCount ?></span>
            </button>
        <?php else: ?>
            <a href="<?= Url::to(['/site/login']) ?>" class="btn-like disabled" title="Đăng nhập để thích">
                <span class="like-icon">🤍</span>
                <span class="like-count"><?= $likeCount ?></span>
            </a>
        <?php endif; ?>
    </div>
</div>
