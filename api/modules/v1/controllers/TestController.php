<?php

namespace api\modules\v1\controllers;

use yii\rest\ActiveController;

/**
 * Country Controller API
 *
 * @author Budi Irawan <deerawan@gmail.com>
 */
class TestController extends ActiveController
{
    public $modelClass = 'api\modules\v1\models\Country'; 
    public function actionFoo()
    {
        return [
            'status' => true,
            'message' => 'API working fine'
        ];
    }
}
