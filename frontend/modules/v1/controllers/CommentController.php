<?php
namespace frontend\modules\v1\controllers;

use Yii;
use yii\rest\Controller;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use common\models\Task;
use common\models\TaskComment;

class CommentController extends Controller
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        // Bearer Auth
        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::class,
        ];

        // Verb Rules
        $behaviors['verbs'] = [
            'class' => VerbFilter::class,
            'actions' => [
                'create' => ['POST'],   
                'index'  => ['GET'],   
                'delete' => ['DELETE'], 
            ],
        ];

        return $behaviors;
    }

    /**
     * POST /api/tasks/{id}/comments
     */
    public function actionCreate($id)
    {
        $task = Task::findOne($id);
        if (!$task) {
            throw new NotFoundHttpException('Task not found');
        }

        $comment = new TaskComment();
        $comment->task_id = $id;
        $comment->user_id = Yii::$app->user->id;
        $comment->comment = Yii::$app->request->getBodyParam('comment');

        if (!$comment->validate()) {
            return ['status' => false, 'errors' => $comment->getErrors()];
        }

        $comment->save(false);

        return [
            'status' => true,
            'message' => 'Comment added',
            'data' => $comment
        ];
    }

    /**
     * GET /api/tasks/{id}/comments
     */
    public function actionIndex($id)
    {
        $task = Task::findOne($id);
        if (!$task) {
            throw new NotFoundHttpException('Task not found');
        }

        $comments = TaskComment::find()
            ->where(['task_id' => $id])
            ->orderBy(['id' => SORT_DESC])
            ->all();

        return [
            'status' => true,
            'data' => array_map(function ($c) {
                return [
                    'id' => $c->id,
                    'comment' => $c->comment,
                    'user' => $c->user->username ?? 'User',
                    'created_at' => $c->created_at,
                ];
            }, $comments)
        ];
    }

    /**
     * DELETE /api/comments/{id}
     */
    public function actionDelete($id)
    {
        $comment = TaskComment::findOne($id);

        if (!$comment) {
            throw new NotFoundHttpException('Comment not found');
        }

        // Only comment owner can delete
        if ($comment->user_id !== Yii::$app->user->id) {
            return ['status' => false, 'message' => 'Permission denied'];
        }

        $comment->delete();

        return [
            'status' => true,
            'message' => 'Comment deleted'
        ];
    }
}
