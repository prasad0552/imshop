<?php

namespace im\blog\frontend\controllers;

use im\base\context\ModelContextInterface;
use im\blog\models\NewsListPage;
use im\blog\models\NewsSearch;
use Yii;
use yii\base\Model;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * Class SearchPageController
 * @package im\search\controllers
 */
class NewsListPageController extends Controller implements ModelContextInterface
{
    /**
     * @var NewsListPage
     */
    private $_model;

    /**
     * Display news list page.
     *
     * @param string $path
     * @param NewsListPage|null $model
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionView($path, $model = null)
    {
        $model = $model ?: $this->findModel($path);
        $this->setModel($model);
        $searchModel = new NewsSearch();
        $searchModel->category_id = $model->category_id;
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->pagination->route = '/cms/page/view';

        return $this->render('view', [
            'model' => $model,
            'dataProvider' => $dataProvider
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getModel()
    {
        return $this->_model;
    }

    /**
     * @inheritdoc
     */
    public function setModel(Model $model)
    {
        $this->_model = $model;
    }

    /**
     * Find page by path.
     *
     * @param string $path
     * @return NewsListPage
     * @throws NotFoundHttpException
     */
    protected function findModel($path)
    {
        /** @var \im\cms\components\PageFinder $finder */
        $finder = Yii::$app->get('pageFinder');
        if (($model = $finder->findByPath($path)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}