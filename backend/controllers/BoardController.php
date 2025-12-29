<?php

namespace backend\controllers;

use Yii;
use common\models\Board;
use backend\models\BoardSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * BoardController
 *
 * Implements CRUD actions for the Board model.
 */
class BoardController extends Controller
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
                        'delete' => ['POST'], // allow delete only via POST
                    ],
                ],
            ]
        );
    }

    /**
     * Lists all Board models with search and filter support.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel  = new BoardSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel'  => $searchModel,
            'dataProvider'=> $dataProvider,
        ]);
    }

    /**
     * Displays a single Board model.
     *
     * @param int $id Board ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Board model.
     * On success, redirects to the view page.
     *
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Board();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
        } else {
            // load default values for new record
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Board model.
     * On success, redirects to the view page.
     *
     * @param int $id Board ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if (
            $this->request->isPost &&
            $model->load($this->request->post()) &&
            $model->save()
        ) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Board model.
     * Redirects to the index page after deletion.
     *
     * @param int $id Board ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Board model by its primary key.
     *
     * @param int $id Board ID
     * @return Board
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Board::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
