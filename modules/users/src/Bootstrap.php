<?php

namespace im\users;

use Yii;
use yii\base\Application;
use yii\base\BootstrapInterface;
use yii\console\Application as ConsoleApplication;

class Bootstrap implements BootstrapInterface
{
    /**
     * @inheritdoc
     */
    public function bootstrap($app)
    {
        Yii::setAlias('@im/users', __DIR__);

//        /** @var $module Module */
//        $module = $app->getModule('users');
//
//
//
//        if ($module) {
////            $this->registerTranslations();
////            if ($app instanceof ConsoleApplication) {
////                $module->controllerNamespace = 'im\user\commands';
////            } else {
////
////                $configUrlRule = [
////                    'prefix' => $module->urlPrefix,
////                    'rules'  => $module->urlRules
////                ];
////
////                if ($module->urlPrefix != 'user') {
////                    $configUrlRule['routePrefix'] = 'user';
////                }
////
////                $app->get('urlManager')->rules[] = new GroupUrlRule($configUrlRule);
////
////                if (!$app->has('authClientCollection')) {
////                    $app->set('authClientCollection', [
////                        'class' => Collection::className(),
////                    ]);
////                }
////            }
//        }
    }

//    /**
//     * Register module translations.
//     */
//    public function registerTranslations()
//    {
//        Yii::$app->i18n->translations['modules/users/*'] = [
//            'class' => 'yii\i18n\PhpMessageSource',
//            'sourceLanguage' => 'en-US',
//            'basePath' => '@im/users/src/messages',
//            'fileMap' => [
//                'modules/users/module' => 'module.php',
//                'modules/users/user' => 'user.php'
//            ]
//        ];
//    }

    /**
     * Adds module rules.
     *
     * @param Application $app
     */
    public function addRules($app)
    {
        $app->getUrlManager()->addRules([

        ], false);
    }
}