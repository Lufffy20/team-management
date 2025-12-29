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
     */
    public function behaviors()
    {
        return [
            // Only logged-in users allowed
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],

            // Delete allowed via POST / GET
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST', 'GET'],
                ],
            ],
        ];
    }

    /**
     * Lists all Task models visible to logged-in user.
     *
     * Logic:
     * - User must be logged in
     * - Fetch teams where user is member
     * - Fetch boards of those teams
     * - Show only tasks belonging to those boards
     */
    public function actionIndex()
    {
        // Safety check (extra, even though AccessControl exists)
        if (Yii::$app->user->isGuest) {
            Yii::$app->response->statusCode = 302;
            return Yii::$app->response->redirect(['/site/login']);
        }

        // Search model for filters
        $searchModel  = new TaskSearchFrontend();
        $dataProvider = $searchModel->search($this->request->queryParams);

        $userId = Yii::$app->user->id;

        /* ===============================
           USER TEAM IDS
           =============================== */
        $teamIds = \common\models\TeamMembers::find()
            ->select('team_id')
            ->where(['user_id' => $userId])
            ->column();

        /* ===============================
           TEAM BOARD IDS
           =============================== */
        $boardIds = \common\models\Board::find()
            ->select('id')
            ->where(['team_id' => $teamIds])
            ->column();

        // Restrict tasks to allowed boards only
        $dataProvider->query->andWhere([
            'board_id' => $boardIds
        ]);

        return $this->render('index', compact(
            'searchModel',
            'dataProvider'
        ));
    }

    /**
     * Displays a single Task model.
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
     * Creates a new Task model.
     * On success redirects to view page.
     */
    public function actionCreate()
    {
        $model = new Task();

        if ($this->request->isPost) {

            // Load + save
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect([
                    'view',
                    'id' => $model->id
                ]);
            }

        } else {
            // Load default DB values
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Task model.
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
            $model->save(false)          // validation intentionally skipped
        ) {
            return $this->redirect([
                'view',
                'id' => $model->id
            ]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Task model.
     *
     * @param int $id Task ID
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Task model with ACCESS CHECK.
     *
     * Logic:
     * - Fetch teams of logged-in user
     * - Fetch boards of those teams
     * - Allow task access only if it belongs to those boards
     *
     * @param int $id Task ID
     * @return Task
     * @throws NotFoundHttpException
     */
    protected function findModel($id)
    {
        $userId = Yii::$app->user->id;

        /* ===============================
           USER TEAM IDS
           =============================== */
        $teamIds = \common\models\TeamMembers::find()
            ->select('team_id')
            ->where(['user_id' => $userId])
            ->column();

        /* ===============================
           TEAM BOARD IDS
           =============================== */
        $boardIds = \common\models\Board::find()
            ->select('id')
            ->where(['team_id' => $teamIds])
            ->column();

        /* ===============================
           TASK FETCH WITH SECURITY
           =============================== */
        $model = Task::find()
            ->where(['id' => $id])
            ->andWhere(['board_id' => $boardIds])
            ->one();

        if ($model === null) {
            throw new NotFoundHttpException();
        }

        return $model;
    }
}
