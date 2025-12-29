<?php

namespace common\components;

use Yii;
use common\models\ActivityLog;

class Logger
{
    public static function add(
        string $action,
        string $details = null,
        int $teamId = null,
        int $boardId = null
    ) {
        $log = new ActivityLog();

        $log->user_id   = Yii::$app->user->id ?? null;
        $log->team_id   = $teamId;
        $log->board_id  = $boardId;
        $log->action    = $action;
        $log->details   = $details;
        $log->created_at = time();

        $log->save(false);
    }
}