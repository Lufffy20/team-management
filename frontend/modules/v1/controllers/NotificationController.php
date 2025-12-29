<?php

namespace frontend\modules\v1\controllers;

use Yii;
use yii\rest\Controller;
use yii\filters\auth\HttpBearerAuth;
use yii\web\NotFoundHttpException;
use common\models\Notification;
use yii\filters\VerbFilter;


class NotificationController extends Controller
{
    public function behaviors()
{
    $behaviors = parent::behaviors();

    $behaviors['authenticator'] = [
        'class' => HttpBearerAuth::class,
    ];

    $behaviors['verbs'] = [
        'class' => VerbFilter::class,
        'actions' => [
            'index'  => ['GET'], 
            'read'   => ['PUT'],     
            'delete' => ['DELETE'],  
        ],
    ];

    return $behaviors;
}


    /**
     * GET /api/notifications
     */
    public function actionIndex()
    {
        $notifications = Notification::find()
            ->where(['user_id' => Yii::$app->user->id])
            ->orderBy(['created_at' => SORT_DESC])
            ->all();

        return [
            'status' => true,
            'data' => array_map(function ($n) {
                return [
                    'id'         => $n->id,
                    'title'      => $n->title,
                    'message'    => $n->message,
                    'is_read'    => (bool) $n->is_read,
                    'created_at' => $n->created_at,
                ];
            }, $notifications)
        ];
    }

    /**
     * PUT /api/notifications/{id}/read
     */
    public function actionRead($id)
    {
        $notification = Notification::findOne([
            'id' => $id,
            'user_id' => Yii::$app->user->id
        ]);

        if (!$notification) {
            throw new NotFoundHttpException('Notification not found');
        }

        $notification->is_read = 1;
        $notification->save(false);

        return [
            'status' => true,
            'message' => 'Notification marked as read'
        ];
    }

    /**
     * DELETE /api/notifications/{id}
     */
    public function actionDelete($id)
    {
        $notification = Notification::findOne([
            'id' => $id,
            'user_id' => Yii::$app->user->id
        ]);

        if (!$notification) {
            throw new NotFoundHttpException('Notification not found');
        }

        $notification->delete();

        return [
            'status' => true,
            'message' => 'Notification deleted'
        ];
    }
}
