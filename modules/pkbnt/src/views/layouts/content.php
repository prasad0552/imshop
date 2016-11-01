<?php

use im\adminlte\widgets\FlashMessages;
use yii\widgets\Breadcrumbs;

/* @var $this \yii\web\View */
/* @var $content string */

?>
<section class="content">
    <?= Breadcrumbs::widget([
        'homeLink' => ['label' => '<i class="fa fa-home"></i> ' . 'Главная', 'url' => ['/']],
        'encodeLabels' => false,
        'tag' => 'ol',
        'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : []
    ]) ?>
    <?php if ($this->title) { ?>
    <h1><?= $this->title ?></h1>
    <?php } ?>
    <?= FlashMessages::widget(); ?>
    <?= $content ?>
</section>