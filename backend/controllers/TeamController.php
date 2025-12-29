<?php

namespace backend\controllers;

use common\models\Team;
use backend\models\TeamSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * TeamController
 *
 * Implements CRUD actions for the Team model.
 */
class TeamController extends Controller
{
    /**
     * Defines controller behaviors.
     * Restricts delete action to POST requests only.
     *
     * @inheritDoc
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::className(),
                    'actions' => [
                        'delete' => ['POST'], // delete allowed only via POST
                    ],
                ],
            ]
        );
    }

    /**
     * Lists all Team models with search and pagination.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel  = new TeamSearch(); // search model
        $dataProvider = $searchModel->search(
            $this->request->queryParams // GET params
        );

        // Set pagination size
        $dataProvider->pagination->pageSize = 5;

        return $this->render('index', [
            'searchModel'  => $searchModel,   // for filters
            'dataProvider'=> $dataProvider,  // for grid/list
        ]);
    }

    /**
     * Displays a single Team model.
     *
     * @param int $id Team ID
     * @return string
     * @throws NotFoundHttpException if team not found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id), // load team
        ]);
    }

    /**
     * Creates a new Team model.
     * On success, redirects to the view page.
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
     * Updates an existing Team model.
     * On success, redirects to the view page.
     *
     * @param int $id Team ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if team not found
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
     * Deletes an existing Team model.
     * Redirects to index after deletion.
     *
     * @param int $id Team ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if team not found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete(); // delete team
        return $this->redirect(['index']);
    }

    /**
     * Finds the Team model by primary key.
     *
     * @param int $id Team ID
     * @return Team
     * @throws NotFoundHttpException if model not found
     */
    protected function findModel($id)
    {
        if (($model = Team::findOne(['id' => $id])) !== null) {
            return $model; // return found team
        }

        throw new NotFoundHttpException(
            'The requested page does not exist.'
        );
    }
}
