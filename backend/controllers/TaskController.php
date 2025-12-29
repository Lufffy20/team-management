<?php

namespace backend\controllers;

use common\models\Task;
use common\models\TaskSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * TaskController
 *
 * Implements CRUD actions for the Task model.
 */
class TaskController extends Controller
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
     * Lists all Task models with search and filters.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel  = new TaskSearch(); // search model
        $dataProvider = $searchModel->search(
            $this->request->queryParams // GET params
        );

        return $this->render('index', [
            'searchModel'  => $searchModel,   // for filters
            'dataProvider'=> $dataProvider,  // for grid/list
        ]);
    }

    /**
     * Displays a single Task model.
     *
     * @param int $id Task ID
     * @return string
     * @throws NotFoundHttpException if task not found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id), // load task
        ]);
    }

    /**
     * Creates a new Task model.
     * On success, redirects to the view page.
     *
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Task(); // new task instance

        // Fetch users list for assignment dropdown
        $users = \common\models\User::find()
            ->select(["CONCAT(first_name, ' ', last_name) AS name", 'id'])
            ->indexBy('id') // id as key
            ->column();     // name as value

        if ($this->request->isPost) {
            // Load POST data and save
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect([
                    'view',
                    'id' => $model->id,
                ]);
            }
        } else {
            // Set default values for new record
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
            'users' => $users, // required for user dropdown
        ]);
    }

    /**
     * Updates an existing Task model.
     * On success, redirects to the view page.
     *
     * @param int $id Task ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if task not found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id); // existing task

        // Fetch users list again for update form
        $users = \common\models\User::find()
            ->select(["CONCAT(first_name, ' ', last_name) AS name", 'id'])
            ->indexBy('id')
            ->column();

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
            'users' => $users, // required for user dropdown
        ]);
    }

    /**
     * Deletes an existing Task model.
     * Redirects to index after deletion.
     *
     * @param int $id Task ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if task not found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete(); // delete task
        return $this->redirect(['index']);
    }

    /**
     * Finds the Task model by primary key.
     *
     * @param int $id Task ID
     * @return Task
     * @throws NotFoundHttpException if model not found
     */
    protected function findModel($id)
    {
        if (($model = Task::findOne(['id' => $id])) !== null) {
            return $model; // return found task
        }

        throw new NotFoundHttpException(
            'The requested page does not exist.'
        );
    }
}
