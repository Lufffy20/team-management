<?php

namespace frontend\controllers;

use Yii;
use common\models\Task;
use frontend\models\TaskSearchFrontend;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

/**
 * TaskUserController implements the CRUD actions for Task model.
 */
class TaskUserController extends Controller
{
    /**
     * @inheritDoc
     */
     public function behaviors()
    {
        return [
            'access' => [
                'class' => \yii\filters\AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => \yii\filters\VerbFilter::class,
                'actions' => [
                    'delete' => ['POST', 'GET'],
                ],
            ],
        ];
    }




    /**
     * Lists all Task models.
     *
     * @return string
     */
public function actionIndex()
{
     if (Yii::$app->user->isGuest) {
        Yii::$app->response->statusCode = 302;
        return Yii::$app->response->redirect(['/site/login']);
    }

    $searchModel = new TaskSearchFrontend();
    $dataProvider = $searchModel->search($this->request->queryParams);

    $userId = Yii::$app->user->id;

    $teamIds = \common\models\TeamMembers::find()
        ->select('team_id')
        ->where(['user_id' => $userId])
        ->column();

    $boardIds = \common\models\Board::find()
        ->select('id')
        ->where(['team_id' => $teamIds])
        ->column();

    $dataProvider->query->andWhere(['board_id' => $boardIds]);

    return $this->render('index', compact('searchModel', 'dataProvider'));
}



    /**
     * Displays a single Task model.
     * @param int $id
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
     * Creates a new Task model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Task();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Task model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
{
    $model = $this->findModel($id);
    $model->scenario = 'update';

    if ($this->request->isPost && $model->load($this->request->post()) && $model->save(false)) {
        return $this->redirect(['view', 'id' => $model->id]);
    }

    return $this->render('update', [
        'model' => $model,
    ]);
}


    /**
     * Deletes an existing Task model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Task model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id
     * @return Task the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
{
    $userId = Yii::$app->user->id;

    // user ke team ids
    $teamIds = \common\models\TeamMembers::find()
        ->select('team_id')
        ->where(['user_id' => $userId])
        ->column();

    // un teams ke board ids
    $boardIds = \common\models\Board::find()
        ->select('id')
        ->where(['team_id' => $teamIds])
        ->column();

    $model = Task::find()
        ->where(['id' => $id])
        ->andWhere(['board_id' => $boardIds])
        ->one();

    if ($model === null) {
        throw new \yii\web\NotFoundHttpException();
    }

    return $model;
}

}
