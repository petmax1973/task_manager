<?php

/** @var yii\web\View $this */
/** @var string $content */

use app\assets\AppAsset;
use app\widgets\Alert;
use yii\bootstrap4\Breadcrumbs;
use yii\bootstrap4\Html;
use yii\bootstrap4\Nav;
use yii\bootstrap4\NavBar;

AppAsset::register($this);

// Get current theme from cookie, default to 'light'
$currentTheme = Yii::$app->request->cookies->getValue('theme', 'light');
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" class="h-100">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <?php $this->registerCsrfMetaTags() ?>
    <title>Task Manager</title>
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <?php $this->head() ?>
</head>
<body class="d-flex flex-column h-100 theme-<?= $currentTheme ?>">
<?php $this->beginBody() ?>

<header>
    <?php
    NavBar::begin([
        'brandLabel' => Yii::$app->name,
        'brandUrl' => Yii::$app->homeUrl,
        'options' => [
            'class' => 'navbar navbar-expand-md navbar-dark bg-dark fixed-top',
        ],
    ]);
    echo Nav::widget([
        'options' => ['class' => 'navbar-nav ml-auto'],
        'items' => [
            [
                'label' => '<i class="fas fa-' . ($currentTheme === 'dark' ? 'sun' : 'moon') . '"></i>',
                'url' => ['/site/theme', 'theme' => $currentTheme === 'dark' ? 'light' : 'dark'],
                'encode' => false,
                'options' => [
                    'class' => 'theme-toggle',
                    'title' => $currentTheme === 'dark' ? 'Switch to Light Theme' : 'Switch to Dark Theme',
                ],
            ],
            [
                'label' => 'Language',
                'items' => [
                    ['label' => 'Italiano', 'url' => ['/site/language', 'lang' => 'it-IT']],
                    ['label' => 'English', 'url' => ['/site/language', 'lang' => 'en-US']],
                ],
            ],
        ],
    ]);
    NavBar::end();
    ?>
</header>

<main role="main" class="flex-shrink-0">
    <div class="container-fluid" style="padding-left: 5%; padding-right: 5%;">
        <?= Breadcrumbs::widget([
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
        <?= Alert::widget() ?>
        <?= $content ?>
    </div>
</main>

<footer class="footer mt-auto py-3 text-muted">
    <div class="container">
        <p class="float-left">&copy; My Company <?= date('Y') ?></p>
        <p class="float-right"><?= Yii::powered() ?></p>
    </div>
</footer>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
