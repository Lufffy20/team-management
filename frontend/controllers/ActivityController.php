<?php

namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use common\models\Task;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;

/**
 * ActivityController
 *
 * Shows recent task-related activities for the logged-in user.
 */
class ActivityController extends Controller
{
    /**
     * Access control configuration.
     * Only authenticated users can access the index action.
     */
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

    /**
     * Displays recent activities for the current user.
     *
     * Logic:
     * 1) Get teams where the user is a member
     * 2) Get boards belonging to those teams
     * 3) Fetch tasks either created by the user
     *    or belonging to those boards
     */
    public function actionIndex()
    {
        $userId = Yii::$app->user->id; // current user ID

        /* ================= USER TEAMS ================= */
        $teamIds = \common\models\TeamMembers::find()
            ->select('team_id')
            ->where(['user_id' => $userId]) // teams of this user
            ->column();

        /* ================= TEAM BOARDS ================= */
        $teamBoards = \common\models\Board::find()
            ->select('id')
            ->where(['team_id' => $teamIds]) // boards under user teams
            ->column();

        /* ================= ACTIVITY QUERY ================= */
        $query = Task::find()
            ->where([
                'or',
                ['created_by' => $userId],     // tasks created by user
                ['board_id'   => $teamBoards], // tasks in user's team boards
            ])
            ->orderBy(['updated_at' => SORT_DESC]); // latest first

        /* ================= DATA PROVIDER ================= */
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20, // records per page
            ],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }
}
