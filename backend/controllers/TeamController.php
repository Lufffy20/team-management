<?php

namespace backend\controllers;

use Yii;
use common\models\Team;
use backend\models\TeamSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * TeamController
 *
 * Handles backend CRUD operations for Team model.
 */
class TeamController extends Controller
{
    /**
     * Controller behaviors
     *
     * - Restricts delete action to POST requests only
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::className(),
                    'actions' => [
                        'delete' => ['POST'], // security: POST only
                    ],
                ],
            ]
        );
    }

    /**
     * LIST ALL TEAMS
     * --------------------------------------------------
     * Displays teams with search & pagination support.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel  = new TeamSearch(); // search/filter model
        $dataProvider = $searchModel->search(
            $this->request->queryParams // GET params
        );

        return $this->render('index', [
            'searchModel'   => $searchModel,   // filter form
            'dataProvider' => $dataProvider,  // grid/list
        ]);
    }

    /**
     * VIEW SINGLE TEAM
     *
     * @param int $id Team ID
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id), // load team safely
        ]);
    }

    /**
     * CREATE TEAM
     * --------------------------------------------------
     * Creates a new team record.
     *
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Team(); // new team instance

        if ($this->request->isPost) {

            // Load POST data and save
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect([
                    'view',
                    'id' => $model->id,
                ]);
            }

        } else {
            // Load default values for new record
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * UPDATE TEAM
     *
     * @param int $id Team ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id); // existing team

        if (
            $this->request->isPost &&
            $model->load($this->request->post()) &&
            $model->save()
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
     * MY TEAMS
     * --------------------------------------------------
     * Shows teams created by the logged-in admin user.
     */
    public function actionMyTeam()
    {
        $userId = Yii::$app->user->id;

        $teams = Team::find()
            ->where(['created_by' => $userId])
            ->all();

        return $this->render('my-team', [
            'teams' => $teams,
        ]);
    }

    /**
     * DELETE TEAM
     * --------------------------------------------------
     * Deletes team and related team members
     * inside a database transaction.
     *
     * @param int $id Team ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionDelete($id)
    {
        $transaction = Yii::$app->db->beginTransaction();

        try {
            // Delete team members first
            \common\models\TeamMembers::deleteAll([
                'team_id' => $id
            ]);

            // Delete team
            $this->findModel($id)->delete();

            $transaction->commit();

        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

        return $this->redirect(['index']);
    }

    /**
     * FIND TEAM MODEL
     * --------------------------------------------------
     * Fetches Team by primary key.
     *
     * @param int $id Team ID
     * @return Team
     * @throws NotFoundHttpException
     */
    protected function findModel($id)
    {
        if (($model = Team::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(
            'The requested page does not exist.'
        );
    }
}
