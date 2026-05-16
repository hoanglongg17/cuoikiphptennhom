<?php
/** @var yii\web\View $this */
/** @var app\models\BlogPost $post */

use yii\helpers\Url;
use yii\helpers\Html;

$likeCount = $post->getLikeCount();
$averageRating = $post->getAverageRating();
$isLikedByUser = Yii::$app->user->isGuest ? false : $post->isLikedByUser(Yii::$app->user->id);
?>

<div class="blog-rating-widget">
    <div class="rating-stats">
        <div class="rating-display">
            <span class="star-rating">
                <?php
                $rating = round($averageRating);
                for ($i = 1; $i <= 5; $i++):
                    if ($i <= $rating):
                        echo '⭐';
                    else:
                        echo '☆';
                    endif;
                endfor;
                ?>
            </span>
            <span class="rating-value">
                <?= round($averageRating, 1) ?>/5 
                <span class="rating-count">(<?= $likeCount ?> đánh giá)</span>
            </span>
        </div>

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
</div>

<style>
/* ==================== RATING WIDGET ==================== */
.blog-rating-widget {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.rating-stats {
    display: flex;
    align-items: center;
    gap: 40px;
    flex-wrap: wrap;
    width: 100%;
}

.rating-display {
    display: flex;
    align-items: center;
    gap: 16px;
}

.star-rating {
    font-size: 24px;
    letter-spacing: 4px;
    display: flex;
    gap: 2px;
}

.rating-value {
    font-weight: 700;
    color: #1a1a1a;
    font-size: 1.1em;
}

.rating-count {
    font-size: 0.85em;
    color: #999;
    font-weight: 500;
    margin-left: 6px;
}

.like-button-container {
    display: flex;
    gap: 12px;
}

.btn-like {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 18px;
    background: white;
    border: 2px solid #ddd;
    border-radius: 20px;
    cursor: pointer;
    font-size: 0.95em;
    font-weight: 600;
    transition: all 0.3s ease;
    text-decoration: none;
    color: #666;
}

.btn-like:hover {
    background: #ffe5e5;
    border-color: #ff6b6b;
    color: #ff6b6b;
    box-shadow: 0 4px 12px rgba(255, 107, 107, 0.2);
    transform: translateY(-2px);
}

.btn-like.liked {
    background: linear-gradient(135deg, #ffe5e5, #fff0f0);
    border-color: #ff6b6b;
    color: #ff6b6b;
}

.btn-like.liked:hover {
    background: linear-gradient(135deg, #ffcccc, #ffe5e5);
}

.btn-like.disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.btn-like.disabled:hover {
    background: white;
    border-color: #ddd;
    color: #666;
    box-shadow: none;
    transform: none;
}

.like-icon {
    font-size: 18px;
    line-height: 1;
}

.like-count {
    min-width: 35px;
    text-align: center;
}

@media (max-width: 768px) {
    .rating-stats {
        gap: 20px;
        padding: 0;
    }

    .rating-display,
    .like-button-container {
        width: 100%;
        justify-content: space-between;
    }

    .star-rating {
        font-size: 20px;
        letter-spacing: 2px;
    }

    .rating-value {
        font-size: 1em;
    }

    .btn-like {
        flex: 1;
        justify-content: center;
    }
}

@media (max-width: 480px) {
    .rating-stats {
        flex-direction: column;
        gap: 15px;
    }

    .rating-display {
        width: 100%;
        justify-content: center;
        gap: 12px;
    }

    .like-button-container {
        width: 100%;
    }

    .btn-like {
        flex: 1;
    }
}
</style>

    .like-button-container {
        width: 100%;
        justify-content: center;
    }
}
</style>

<script>
function toggleLike(button, postId) {
    const url = '<?= Url::to(['blog/like']) ?>';
    const csrfToken = '<?= Yii::$app->request->csrfToken ?>';

    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-CSRF-Token': csrfToken
        },
        body: 'id=' + postId
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            button.classList.toggle('liked', data.liked);
            button.querySelector('.like-icon').textContent = data.liked ? '❤️' : '🤍';
            button.querySelector('.like-count').textContent = data.likeCount;
            button.title = data.liked ? 'Bỏ thích' : 'Thích';
        }
    })
    .catch(error => console.error('Error:', error));
}
</script>
