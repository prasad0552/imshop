<?php

namespace im\base\widgets;

use yii\db\ActiveQuery;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\jui\Widget;

class RelationWidget extends Widget
{
    /**
     * @var array
     */
    public $options = [];

    /**
     * @var ActiveQuery
     */
    public $relation;

    /**
     * @var string
     */
    public $modelClass;

    /**
     * @var string
     */
    public $modelView;

    /**
     * @var string
     */
    public $itemView = '@im/base/widgets/views/relation_widget_item';

    /**
     * @var \yii\widgets\ActiveForm|\im\forms\components\DynamicActiveForm
     */
    public $form;

    /**
     * @var bool
     */
    public $sortable = false;

    public $addLabel;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->initOptions();
        echo Html::beginTag('div', $this->options) . "\n";
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $id = $this->options['id'];
        echo $this->render('relation_widget', ['widget' => $this]);
        echo Html::endTag('div') . "\n";
        $this->registerWidget('sortable', $id);
        $options = Json::encode($this->clientOptions);
        $view = $this->getView();
        RelationWidgetAsset::register($view);
        $view->registerJs("jQuery('#$id').relationWidget($options);");
    }

    /**
     * Initializes the widget options.
     * This method sets the default values for various options.
     */
    protected function initOptions()
    {
        $this->clientOptions = array_merge([
            'sortable' => $this->sortable,
            'addUrl' => Url::to(['/base/relation/add']),
            'form' => $this->form,
            'modelClass' => $this->modelClass,
            'modelView' => $this->modelView,
            'itemView' => $this->itemView,
            // Sortable
            'items' => '.list-item',
            'placeholder' => 'sortable-placeholder',
            'axis' => 'y',
            'forcePlaceholderSize' => true
        ], $this->clientOptions);
    }
}