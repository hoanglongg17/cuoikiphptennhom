<?php
/** @var yii\web\View $this */
/** @var app\models\Deck[] $decks */
/** @var array $deckStats */

use yii\helpers\Url;
use yii\helpers\Html;

$this->title = 'Luyện tập - Andi';

$this->registerCssFile('@web/css/dashboard.css', ['depends' => [\app\assets\AppAsset::class]]);
$this->registerCssFile('@web/css/practice.css', ['depends' => [\app\assets\AppAsset::class]]);
?>

<div class="practice-container">
    <div class="practice-header">
        <h1>🎯 Luyện tập</h1>
        <p class="subtitle">Chọn một bộ thẻ để bắt đầu học</p>
    </div>

    <div class="practice-stats-row">
        <div class="stat-card">
            <span class="stat-label">Bộ thẻ</span>
            <span class="stat-value"><?= count($decks) ?></span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Từ mới</span>
            <span class="stat-value" style="color: #2196F3;">
                <?= array_sum(array_column($deckStats, 'new')) ?>
            </span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Đang học</span>
            <span class="stat-value" style="color: #FF9800;">
                <?= array_sum(array_column($deckStats, 'learning')) ?>
            </span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Cần ôn</span>
            <span class="stat-value" style="color: #4CAF50;">
                <?= array_sum(array_column($deckStats, 'review')) ?>
            </span>
        </div>
    </div>

    <div class="decks-grid">
        <div class="decks-table-header">
            <div class="col-name">Bộ thẻ</div>
            <div class="col-stat">Từ mới</div>
            <div class="col-stat">Đang học</div>
            <div class="col-stat">Cần ôn</div>
            <div class="col-action">Hành động</div>
        </div>

        <?php foreach ($decks as $deck): ?>
            <?php $stats = $deckStats[$deck->deckid] ?? ['new' => 0, 'learning' => 0, 'review' => 0]; ?>
            <div class="deck-study-card">
                <div class="col-name">
                    <div class="deck-name"><?= Html::encode($deck->name) ?></div>
                    <div class="deck-info">
                        <span class="badge-total"><?= $stats['total'] ?> thẻ</span>
                    </div>
                </div>
                <div class="col-stat stat-new"><?= $stats['new'] ?></div>
                <div class="col-stat stat-learning"><?= $stats['learning'] ?></div>
                <div class="col-stat stat-review"><?= $stats['review'] ?></div>
                <div class="col-action">
                    <a href="<?= Url::to(['site/study-deck', 'deckid' => $deck->deckid]) ?>" 
                       class="btn-start-study">
                        Học ngay
                    </a>
                </div>
            </div>
        <?php endforeach; ?>

        <?php if (empty($decks)): ?>
            <div class="empty-state">
                <h3>📚 Bạn chưa có bộ thẻ nào</h3>
                <p>Hãy tạo bộ thẻ để bắt đầu luyện tập!</p>
                <a href="<?= Url::to(['site/vocabset']) ?>" class="btn btn-primary">Tạo bộ thẻ</a>
            </div>
        <?php endif; ?>
    </div>
</div>
