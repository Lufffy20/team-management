<?php

namespace backend\controllers;


use Yii;
use common\models\TeamMembers;
use backend\models\TeamMembersSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * TeammembersController implements the CRUD actions for TeamMembers model.
 */
class TeammembersController extends Controller
{
    /**
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
                        'delete' => ['POST'],
                    ],
                ],
            ]
        );
    }

    /**
     * Lists all TeamMembers models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new TeamMembersSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        $dataProvider->pagination->pageSize = 5;

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single TeamMembers model.
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
     * Creates a new TeamMembers model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
{
    $model = new TeamMembers();

    if ($model->load(Yii::$app->request->post())) {

        // Find user by email
        $user = \common\models\User::find()->where(['email' => $model->email])->one();

        if ($user) {
            $model->user_id = $user->id; // Set user_id automatically

            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Team member added.');
                return $this->redirect(['index']);
            }
        }
    }

    return $this->render('create', ['model' => $model]);
}


    /**
     * Updates an existing TeamMembers model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing TeamMembers model.
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
     * Finds the TeamMembers model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id
     * @return TeamMembers the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = TeamMembers::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionSearchUser($q = null)
{
    Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

    $users = \common\models\User::find()
        ->where(['like', 'email', $q])
        ->limit(20)
        ->all();

    $results = [];

    foreach ($users as $user) {
        $results[] = ['id' => $user->id, 'text' => $user->email];
    }

    return ['results' => $results];
}

}
