<?php
namespace frontend\modules\v1;

use Yii;

class Module extends \yii\base\Module
{
    public $controllerNamespace = 'frontend\modules\v1\controllers';

    public function init()
    {
        parent::init();
        Yii::$app->request->enableCsrfValidation = false;//off csrf

        Yii::$app->request->parsers = [
            'application/json' => 'yii\web\JsonParser',
        ];

        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;//json format
    }
}
