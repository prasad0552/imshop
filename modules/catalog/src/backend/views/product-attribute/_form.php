<?php

use im\catalog\Module;
use im\eav\components\AttributeTypes;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model im\catalog\models\ProductAttribute */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="product-attribute-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'presentation')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'type')->dropDownList(AttributeTypes::getChoices(), ['prompt' => '']) ?>

    <?= $form->field($model, 'predefined_values')->checkbox() ?>

    <?= $form->field($model, 'fieldConfig[fieldType]')->dropDownList(AttributeTypes::getFieldTypeChoices(), ['prompt' => '']) ?>

    <?= $form->field($model, 'rulesConfig')->dropDownList(AttributeTypes::getValidatorChoices(), ['multiple' => true, 'prompt' => '']) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? Module::t('module', 'Create') : Module::t('module', 'Update'),
            ['class' => $model->isNewRecord ? 'btn btn-primary' : 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
