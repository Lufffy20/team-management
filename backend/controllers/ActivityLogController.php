<?php
namespace backend\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use common\models\ActivityLog;

class ActivityLogController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $logs = ActivityLog::find()
            ->with(['user', 'team', 'board'])
            ->orderBy(['id' => SORT_DESC])
            ->limit(200)
            ->all();

        return $this->render('index', [
            'logs' => $logs
        ]);
    }
}
