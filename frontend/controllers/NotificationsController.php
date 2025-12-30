<?php

namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use common\models\Notification;

/**
 * NotificationsController
 *
 * Handles user notifications:
 * - Listing notifications
 * - Marking all notifications as read
 */
class NotificationsController extends Controller
{
    /**
     * Access control.
     * Only logged-in users can access notifications.
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'], // authenticated users only
                    ],
                ],
            ],
        ];
    }

    /**
     * SHOW NOTIFICATIONS
     * --------------------------------------------------
     * Fetches all notifications for the logged-in user
     * and orders them by latest first.
     */
    public function actionIndex()
    {
        $notifications = Notification::find()
            ->where([
                'user_id' => Yii::$app->user->id, // current user only
            ])
            ->orderBy(['created_at' => SORT_DESC]) // latest on top
            ->all();

        return $this->render('index', compact('notifications'));
    }

    /**
     * MARK ALL AS READ
     * --------------------------------------------------
     * Sets `is_read = 1` for all notifications
     * belonging to the logged-in user.
     */
    public function actionMarkAllRead()
    {
        Notification::updateAll(
            ['is_read' => 1],                     // update column
            ['user_id' => Yii::$app->user->id]    // only current user
        );

        // Redirect back to notifications list
        return $this->redirect(['index']);
    }
}
