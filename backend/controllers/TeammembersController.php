<?php

namespace backend\controllers;

use Yii;
use common\models\TeamMembers;
use backend\models\TeamMembersSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * TeammembersController
 *
 * Implements CRUD actions for the TeamMembers model.
 */
class TeammembersController extends Controller
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
     * Lists all TeamMembers models with search and pagination.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel  = new TeamMembersSearch(); // search model
        $dataProvider = $searchModel->search(
            $this->request->queryParams // GET params
        );

        // Set pagination size
        $dataProvider->pagination->pageSize = 5;

        return $this->render('index', [
            'searchModel'  => $searchModel,   // filter form
            'dataProvider'=> $dataProvider,  // grid/list data
        ]);
    }

    /**
     * Displays a single TeamMembers model.
     *
     * @param int $id Team member ID
     * @return string
     * @throws NotFoundHttpException if model not found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id), // load team member
        ]);
    }

    /**
     * Creates a new TeamMembers model.
     * User is automatically linked using email.
     *
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new TeamMembers(); // new team member

        if ($model->load(Yii::$app->request->post())) {

            // Find user by entered email
            $user = \common\models\User::find()
                ->where(['email' => $model->email])
                ->one();

            if ($user) {
                // Automatically assign user_id
                $model->user_id = $user->id;

                if ($model->save()) {
                    Yii::$app->session->setFlash(
                        'success',
                        'Team member added.'
                    );
                    return $this->redirect(['index']);
                }
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing TeamMembers model.
     *
     * @param int $id Team member ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if model not found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id); // existing team member

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
     * Deletes an existing TeamMembers model.
     * Redirects to index after deletion.
     *
     * @param int $id Team member ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if model not found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete(); // delete team member
        return $this->redirect(['index']);
    }

    /**
     * Finds the TeamMembers model by primary key.
     *
     * @param int $id Team member ID
     * @return TeamMembers
     * @throws NotFoundHttpException if model not found
     */
    protected function findModel($id)
    {
        if (($model = TeamMembers::findOne(['id' => $id])) !== null) {
            return $model; // return found record
        }

        throw new NotFoundHttpException(
            'The requested page does not exist.'
        );
    }

    /**
     * AJAX user search by email.
     * Used for autocomplete/select dropdowns.
     *
     * @param string|null $q Search query
     * @return array JSON response
     */
    public function actionSearchUser($q = null)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        // Find users matching email
        $users = \common\models\User::find()
            ->where(['like', 'email', $q])
            ->limit(20)
            ->all();

        $results = [];

        foreach ($users as $user) {
            $results[] = [
                'id'   => $user->id,
                'text' => $user->email,
            ];
        }

        return ['results' => $results];
    }
}
