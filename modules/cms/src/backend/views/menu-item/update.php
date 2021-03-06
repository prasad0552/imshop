<?php

use im\cms\Module;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model im\cms\models\MenuItem */

$this->title = Module::t('menu-item', 'Menu items');
$this->params['subtitle'] = Module::t('menu-item', 'Menu item updating');
$this->params['breadcrumbs'] = [['label' => $this->title, 'url' => ['index']], $this->params['subtitle']];
?>

<div class="box box-success">
    <div class="box-header with-border">
        <h3 class="box-title"><?= $this->params['subtitle'] ?></h3>
        <div class="box-tools pull-right">
            <?= Html::a('<i class="fa fa-trash-o"></i>', ['delete', 'id' => $model->id], ['class' => 'btn btn-sm btn-default', 'title' => Module::t('module', 'Delete'), 'data-confirm' => Module::t('module', 'Are you sure you want to delete this item?'), 'data-method' => 'delete']) ?>
        </div>
    </div>
    <div class="box-body">
        <?= $this->render('_form', [
            'model' => $model
        ]) ?>
    </div>
</div>
