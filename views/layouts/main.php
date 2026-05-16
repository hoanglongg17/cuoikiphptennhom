<?php

/** @var yii\web\View $this */
/** @var string $content */

use app\assets\AppAsset;
use app\widgets\Alert;
use yii\bootstrap5\Breadcrumbs;
use yii\bootstrap5\Html;
use yii\bootstrap5\Nav;
use yii\bootstrap5\NavBar;
use yii\helpers\Url;

AppAsset::register($this);

$this->registerCsrfMetaTags();
$this->registerMetaTag(['charset' => Yii::$app->charset], 'charset');
$this->registerMetaTag(['name' => 'viewport', 'content' => 'width=device-width, initial-scale=1, shrink-to-fit=no']);
$this->registerMetaTag(['name' => 'description', 'content' => $this->params['meta_description'] ?? '']);
$this->registerMetaTag(['name' => 'keywords', 'content' => $this->params['meta_keywords'] ?? '']);
$this->registerLinkTag(['rel' => 'icon', 'type' => 'image/x-icon', 'href' => Yii::getAlias('@web/favicon.ico')]);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" class="h-100">
<head>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
    <style>
        /* ==================== HEADER STYLES ==================== */
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 5%;
            background: #fff;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            height: 70px;
            box-sizing: border-box;
        }
        
        header .logo {
            display: flex;
            align-items: center;
            text-decoration: none;
        }
        
        header .logo img {
            height: 40px;
            margin-right: 10px;
        }
        
        header .logo span {
            font-size: 24px;
            font-weight: 800;
            color: #ff4081;
        }
        
        header nav {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        header nav a {
            text-decoration: none;
            font-weight: 600;
            font-size: 15px;
            color: #333;
        }
        
        header nav a:hover {
            color: #ff4081;
        }
        
        header .btn-login {
            color: #333;
        }
        
        header .btn-signup {
            background: #ff4081;
            color: #fff !important;
            padding: 8px 20px;
            border-radius: 20px;
            transition: 0.3s;
            display: inline-block;
        }
        
        header .btn-signup:hover {
            background: #e73370;
        }
        
        header .user-nav-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        header .nav-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #ff4081;
        }
        
        header .display-name {
            font-weight: 700;
            color: #333;
            text-decoration: none;
        }
        
        header .btn-logout-nav {
            background: #f1f2f6;
            border: none;
            padding: 6px 15px;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 600;
            color: #ff4757;
            transition: 0.3s;
        }
        
        header .btn-logout-nav:hover {
            background: #ff4757;
            color: #fff;
        }
        
        /* Adjust body and dashboard to account for fixed header */
        body {
            padding-top: 0 !important;
        }
        
        .dashboard {
            margin-top: 70px !important;
        }
    </style>
</head>
<body class="d-flex flex-column h-100">
<?php $this->beginBody() ?>

<header>
    <div class="logo">
        <a href="<?= Url::to(['site/index']) ?>" style="display: flex; align-items: center; text-decoration: none;">
            <img src="<?= Yii::getAlias('@web') ?>/images/andilogo.png" alt="Andi Logo" onerror="this.src='https://via.placeholder.com/40'">
            <span>Andi</span>
        </a>
    </div>
    
    <nav>
        <?php if (Yii::$app->user->isGuest): ?>
            <a href="<?= Url::to(['site/login']) ?>" class="btn-login">Đăng nhập</a>
            <a href="<?= Url::to(['site/signup']) ?>" class="btn-signup">Đăng ký</a>
        <?php else: ?>
            <div class="user-nav-info">
                <img src="<?= Yii::$app->user->identity->avatarurl ? Yii::$app->user->identity->avatarurl : Yii::getAlias('@web/images/andi-avatar.png') ?>" 
                     alt="Avatar" class="nav-avatar" onerror="this.src='https://via.placeholder.com/35'">
                
                <a href="<?= Url::to(['site/dashboard']) ?>" class="display-name">
                    Hi, <?= Html::encode(Yii::$app->user->identity->displayname) ?>
                </a>
                
                <?= Html::beginForm(['/site/logout'], 'post', ['style' => 'display: inline;']) ?>
                <?= Html::submitButton('Thoát', ['class' => 'btn-logout-nav']) ?>
                <?= Html::endForm() ?>
            </div>
        <?php endif; ?>
    </nav>
</header>

<div class="dashboard">
    <?= $this->render('sidebar') ?>
    <main class="main">
        <?= $content ?>
    </main>
</div>

<footer id="footer" class="mt-auto py-3 bg-light">

</footer>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
