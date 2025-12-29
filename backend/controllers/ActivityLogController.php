<?php

namespace backend\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use common\models\ActivityLog;

/**
 * ActivityLogController
 *
 * Handles listing of recent activity logs for authenticated users.
 */
class ActivityLogController extends Controller
{
    /**
     * Defines access control behavior.
     * Only logged-in users are allowed to access this controller.
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
     * Displays the latest activity logs.
     *
     * - Fetches activity logs with related user, team, and board
     * - Orders records by latest first
     * - Limits results to last 200 entries
     */
    public function actionIndex()
    {
        $logs = ActivityLog::find()
            ->with(['user', 'team', 'board']) // eager load relations
            ->orderBy(['id' => SORT_DESC])    // latest logs first
            ->limit(200)                      // limit records
            ->all();

        return $this->render('index', [
            'logs' => $logs,
        ]);
    }
}
