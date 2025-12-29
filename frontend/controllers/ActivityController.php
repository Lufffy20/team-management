<?php

namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use common\models\Task;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;

class ActivityController extends Controller
{

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['index'],
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'], // logged-in users only
                    ],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $userId = Yii::$app->user->id;

        // User ke teams ka data lao
        $teamIds = \common\models\TeamMembers::find()
            ->select('team_id')
            ->where(['user_id' => $userId])
            ->column();

        // Un teams ki boards lao
        $teamBoards = \common\models\Board::find()
            ->select('id')
            ->where(['team_id' => $teamIds])
            ->column();

        // Recent activities query
        $query = Task::find()
            ->where([
                'or',
                ['created_by' => $userId],
                ['board_id' => $teamBoards]
            ])
            ->orderBy(['updated_at' => SORT_DESC]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 20],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider
        ]);
    }
}
