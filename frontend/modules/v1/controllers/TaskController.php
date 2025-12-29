<?php
namespace frontend\modules\v1\controllers;

use Yii;
use yii\rest\Controller;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use common\models\Task;
use common\models\User;

class TaskController extends Controller
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        // Bearer Token Auth
        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::class,
        ];

        // HTTP Verb Protection
        $behaviors['verbs'] = [
            'class' => VerbFilter::class,
            'actions' => [
                'create' => ['POST'],
                'index'  => ['GET'],
                'view'   => ['GET'],
                'update' => ['PUT', 'PATCH'],
                'delete' => ['DELETE'],
                'status' => ['PUT', 'PATCH'],
                'assign' => ['POST','PUT'],
            ],
        ];

        return $behaviors;
    }

    /* CREATE TASK */
    public function actionCreate()
    {
        $task = new Task();
        $task->load(Yii::$app->request->getBodyParams(), '');
        $task->created_by = Yii::$app->user->id;

        if (!$task->validate()) {
            return ['status' => false, 'errors' => $task->getErrors()];
        }

        if (!$task->save()) {
            return ['status' => false, 'errors' => $task->getErrors()];
        }

        return [
            'status' => true,
            'data' => $task
        ];
    }

    /* GET ALL TASKS */
    public function actionIndex()
    {
        $tasks = Task::find()
            ->where(['created_by' => Yii::$app->user->id])
            ->orderBy(['id' => SORT_DESC])
            ->all();

        return [
            'status' => true,
            'data' => $tasks
        ];
    }

    /* GET SINGLE TASK */
    public function actionView($id)
    {
        return [
            'status' => true,
            'data' => $this->findTask($id)
        ];
    }

    /* UPDATE TASK */
    public function actionUpdate($id)
    {
        $task = $this->findTask($id);
        $task->load(Yii::$app->request->getBodyParams(), '');

        if (!$task->validate()) {
            return ['status' => false, 'errors' => $task->getErrors()];
        }

        if (!$task->save()) {
            return ['status' => false, 'errors' => $task->getErrors()];
        }

        return [
            'status' => true,
            'data' => $task
        ];
    }

    /* DELETE TASK */
    public function actionDelete($id)
    {
        $this->findTask($id)->delete();

        return [
            'status' => true,
            'message' => 'Task deleted successfully'
        ];
    }

    /* UPDATE STATUS */
    public function actionStatus($id)
    {
        $task = $this->findTask($id);
        $task->status = Yii::$app->request->getBodyParam('status');

        if (!$task->validate(['status'])) {
            return ['status' => false, 'errors' => $task->getErrors()];
        }

        if (!$task->save(false)) {
            return ['status' => false, 'errors' => $task->getErrors()];
        }

        return [
            'status' => true,
            'data' => $task
        ];
    }

    /* ASSIGN TASK */

public function actionAssign($id)
{
    $task = $this->findTask($id);
    $userId = Yii::$app->request->getBodyParam('user_id');

    // ❗ REQUIRED CHECK
    if (!User::find()->where(['id' => $userId])->exists()) {
        return [
            'status' => false,
            'message' => 'User does not exist'
        ];
    }

    $task->assigned_to = $userId;

    if (!$task->validate(['assigned_to'])) {
        return ['status' => false, 'errors' => $task->getErrors()];
    }

    if (!$task->save(false)) {
        return ['status' => false, 'errors' => $task->getErrors()];
    }

    return [
        'status' => true,
        'data' => $task
    ];
}


    /* FIND TASK WITH OWNERSHIP CHECK */
    protected function findTask($id)
    {
        $task = Task::findOne([
            'id' => $id,
            'created_by' => Yii::$app->user->id
        ]);

        if (!$task) {
            throw new NotFoundHttpException('Task not found');
        }

        return $task;
    }
}
