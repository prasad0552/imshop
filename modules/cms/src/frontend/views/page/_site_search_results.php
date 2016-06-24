<?php

use yii\widgets\ListView;

/* @var $this yii\web\View */
/* @var $context im\search\models\SearchPage */
/* @var $dataProvider im\search\components\search\SearchDataProvider */
/* @var $searchableType im\search\components\searchable\SearchableInterface */

echo ListView::widget([
    'dataProvider' => $dataProvider,
    'itemView' => '@im/cms/frontend/views/page/_site_search_results_item',
    'layout' => "{summary}\n<div class='row'>{items}</div>\n{pager}",
    'itemOptions' => ['class' => 'col-xs-12 search-results-item search-results-page']
]);
