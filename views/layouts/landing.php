<?php

/** @var yii\web\View $this */
/** @var string $content */

use app\assets\AppAsset;
use yii\bootstrap5\Html;
use yii\helpers\Url;

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
    <link rel="stylesheet" href="<?= Yii::getAlias('@web') ?>/css/style.css">
    <style>
        /* CSS cho Header để xử lý hiển thị */
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 5%;
            background: #fff;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            position: fixed;
            top: 0; left: 0; right: 0;
            z-index: 1000;
            height: 70px;
        }
        .logo a { display: flex; align-items: center; text-decoration: none; }
        .logo img { height: 40px; margin-right: 10px; }
        .logo span { font-size: 24px; font-weight: 800; color: #ff4081; }
        
        nav { display: flex; align-items: center; gap: 15px; }
        nav a { text-decoration: none; font-weight: 600; font-size: 15px; }
        .btn-login { color: #333; }
        .btn-signup { 
            background: #ff4081; color: #fff !important; 
            padding: 8px 20px; border-radius: 20px; 
            transition: 0.3s;
        }
        .btn-signup:hover { background: #e73370; }
        
        /* Hiển thị User khi đã đăng nhập */
        .user-nav-info { display: flex; align-items: center; gap: 12px; }
        .nav-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #ff4081;
        }
        .display-name { font-weight: 700; color: #333; text-decoration: none; }
        .btn-logout-nav {
            background: #f1f2f6; border: none; padding: 6px 15px;
            border-radius: 12px; cursor: pointer; font-weight: 600;
            color: #ff4757; transition: 0.3s;
        }
        .btn-logout-nav:hover { background: #ff4757; color: #fff; }

        body { padding-top: 70px; } /* Để nội dung không bị Header đè lên */
    </style>
</head>
<body>
<?php $this->beginBody() ?>

<header>
    <div class="logo">
        <a href="<?= Url::to(['site/index']) ?>">
            <img src="<?= Yii::getAlias('@web') ?>/images/andilogo.png" alt="Andi Logo" onerror="this.src='https://via.placeholder.com/40'">
            <span>Andi</span>
        </a>
    </div>
    
    <nav>
        <?php if (Yii::$app->user->isGuest): ?>
            <!-- CHƯA ĐĂNG NHẬP -->
            <a href="<?= Url::to(['site/login']) ?>" class="btn-login">Đăng nhập</a>
            <!-- ĐÃ SỬA: Thay site/login bằng site/signup -->
            <a href="<?= Url::to(['site/signup']) ?>" class="btn-signup">Đăng ký</a>
        <?php else: ?>
            <!-- ĐÃ ĐĂNG NHẬP -->
            <div class="user-nav-info">
                <!-- Hiển thị Avatar (giống trang Profile) -->
                <img src="<?= Yii::$app->user->identity->avatarurl ? Yii::$app->user->identity->avatarurl : Yii::getAlias('@web/images/andi-avatar.png') ?>" 
                     alt="Avatar" class="nav-avatar" onerror="this.src='https://via.placeholder.com/35'">
                
                <a href="<?= Url::to(['site/dashboard']) ?>" class="display-name">
                    Hi, <?= Html::encode(Yii::$app->user->identity->displayname) ?>
                </a>
                
                <?= Html::beginForm(['/site/logout'], 'post', ['class' => 'd-inline']) ?>
                <?= Html::submitButton('Thoát', ['class' => 'btn-logout-nav']) ?>
                <?= Html::endForm() ?>
            </div>
        <?php endif; ?>
    </nav>
</header>

<main>
    <?= $content ?>
</main>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>