<?php

namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use common\models\Task;
use frontend\models\TaskSearchFrontend;

/**
 * TaskUserController
 *
 * Handles task listing and basic CRUD
 * for normal (frontend) users.
 */
class TaskUserController extends Controller
{
    /**
     * Access control + HTTP verb rules
     *
     * - Only logged-in users can access
     * - Delete allowed via POST and GET
     */
    public function behaviors()
    {
        return [
            // 🔐 Access control
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'], // logged-in users only
                    ],
                ],
            ],

            // 🔁 HTTP verb restrictions
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST', 'GET'],
                ],
            ],
        ];
    }

    /**
     * LIST TASKS
     * --------------------------------------------------
     * Shows all tasks visible to the logged-in user.
     *
     * Flow:
     * 1) Ensure user is logged in
     * 2) Get teams where user is a member
     * 3) Get boards of those teams
     * 4) Show tasks only from allowed boards
     */
    public function actionIndex()
    {
        // Extra safety check (even though AccessControl exists)
        if (Yii::$app->user->isGuest) {
            Yii::$app->response->statusCode = 302;
            return Yii::$app->response->redirect(['/site/login']);
        }

        // Search model (filters + sorting)
        $searchModel  = new TaskSearchFrontend();
        $dataProvider = $searchModel->search($this->request->queryParams);

        $userId = Yii::$app->user->id;

        /* ===============================
         * USER TEAM IDS
         * =============================== */
        $teamIds = \common\models\TeamMembers::find()
            ->select('team_id')
            ->where(['user_id' => $userId])
            ->column();

        /* ===============================
         * TEAM BOARD IDS
         * =============================== */
        $boardIds = \common\models\Board::find()
            ->select('id')
            ->where(['team_id' => $teamIds])
            ->column();

        /* ===============================
         * TASK ACCESS RESTRICTION
         * =============================== */
        $dataProvider->query->andWhere([
            'board_id' => $boardIds, // only allowed boards
        ]);

        return $this->render('index', compact(
            'searchModel',
            'dataProvider'
        ));
    }

    /**
     * VIEW SINGLE TASK
     *
     * @param int $id Task ID
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * CREATE TASK
     * --------------------------------------------------
     * Creates a new task for the logged-in user.
     * On success → redirects to task view page.
     */
    public function actionCreate()
    {
        $model = new Task();
        $model->created_by = Yii::$app->user->id; // task creator

        // Boards where user is a MEMBER
        $boards = \common\models\Board::find()
            ->innerJoin('board_members bm', 'bm.board_id = board.id')
            ->where(['bm.user_id' => Yii::$app->user->id])
            ->all();

        if (Yii::$app->request->isPost) {
            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                return $this->redirect([
                    'view',
                    'id' => $model->id
                ]);
            }
        }

        return $this->render('create', [
            'model'  => $model,
            'boards' => $boards, // passed to dropdown
        ]);
    }

    /**
     * UPDATE TASK
     *
     * @param int $id Task ID
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        // Explicit update scenario
        $model->scenario = 'update';

        if (
            $this->request->isPost &&
            $model->load($this->request->post()) &&
            $model->save(false) // validation intentionally skipped
        ) {
            return $this->redirect([
                'view',
                'id' => $model->id,
            ]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * DELETE TASK
     *
     * @param int $id Task ID
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * FIND TASK WITH ACCESS CHECK
     * --------------------------------------------------
     * Ensures user can access the task.
     *
     * Logic:
     * 1) Fetch teams of logged-in user
     * 2) Fetch boards of those teams
     * 3) Allow task only if it belongs to those boards
     *
     * @param int $id Task ID
     * @return Task
     * @throws NotFoundHttpException
     */
    protected function findModel($id)
    {
        $userId = Yii::$app->user->id;

        /* ===============================
         * USER TEAM IDS
         * =============================== */
        $teamIds = \common\models\TeamMembers::find()
            ->select('team_id')
            ->where(['user_id' => $userId])
            ->column();

        /* ===============================
         * TEAM BOARD IDS
         * =============================== */
        $boardIds = \common\models\Board::find()
            ->select('id')
            ->where(['team_id' => $teamIds])
            ->column();

        /* ===============================
         * TASK FETCH WITH SECURITY
         * =============================== */
        $model = Task::find()
            ->where(['id' => $id])
            ->andWhere(['board_id' => $boardIds])
            ->one();

        if ($model === null) {
            throw new NotFoundHttpException('Task not found or access denied.');
        }

        return $model;
    }
}
