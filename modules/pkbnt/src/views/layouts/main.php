<?php

/**
 * Theme main layout.
 *
 * @var \yii\web\View $this View
 * @var string $content Content
 */

use im\cms\widgets\WidgetArea;
use im\pkbnt\components\assets\MainAsset;
use im\pkbnt\widgets\FlashMessages;

MainAsset::register($this);

?>
<?php $this->beginPage(); ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <?= $this->render('//layouts/head') . "\n" ?>
</head>
<body class="layout-main">
<?php $this->beginBody(); ?>
<?= FlashMessages::widget(); ?>
<?= $this->render('//layouts/header') ?>
<?= $this->render('//layouts/top-menu') ?>
<div class="widget-area-top">
<?= WidgetArea::widget([
    'code' => 'top',
    'layout' => 'main',
    'context' => $this->context
]) ?>
</div>
<div class="container">
    <div class="row">
        <div class="col-md-9">
            <div class="widget-area-before-content">
            <?= WidgetArea::widget([
                'code' => 'beforeContent',
                'layout' => 'main',
                'context' => $this->context
            ]) ?>
            </div>
            <div class="clearfix"></div>
            <?= $this->render('//layouts/content', ['content' => $content]) ?>
        </div>
        <div class="col-md-3">
            <aside class="widget-area-sidebar">
            <?= WidgetArea::widget([
                'code' => 'sidebar',
                'layout' => 'main',
                'context' => $this->context
            ]) ?>
            </aside>
        </div>
    </div>
    <div class="widget-area-after-content row">
        <?= WidgetArea::widget([
            'code' => 'afterContent',
            'layout' => 'main',
            'context' => $this->context,
            'containerOptions' => [
                'tag' => 'div',
                'class' => 'col-md-6'
            ]
        ]) ?>
    </div>
</div>

<footer class="footer">
    <div class="container">
        <p class="pull-left">&copy; <?= date('Y') ?> Вишневый сад. Все права защищены.</p>
        <p class="pull-right">&nbsp;</p>
    </div>
</footer>
<?php $this->endBody(); ?>
</body>
</html>
<?php $this->endPage(); ?>