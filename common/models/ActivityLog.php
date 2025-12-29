<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

class ActivityLog extends ActiveRecord
{
    public static function tableName()
    {
        return 'activity_log';
    }

    public function rules()
    {
        return [
            [['user_id', 'team_id', 'board_id', 'created_at'], 'integer'],
            [['action'], 'required'],
            [['details'], 'string'],
            [['action'], 'string', 'max' => 255],
        ];
    }

    public function getUser()
    {
        return $this->hasOne(\common\models\User::class, ['id' => 'user_id']);
    }

    public function getTeam()
{
    return $this->hasOne(\common\models\Team::class, ['id' => 'team_id']);
}

public function getBoard()
    {
        return $this->hasOne(\common\models\Board::class, ['id' => 'board_id']);
    }
}
