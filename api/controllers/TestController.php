<?php
namespace api\controllers;

use yii\rest\Controller;

class TestController extends Controller
{
    public function actionIndex()
    {
        return [
            'status' => true,
            'message' => 'API working fine'
        ];
    }
}
