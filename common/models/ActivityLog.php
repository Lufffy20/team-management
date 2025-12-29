<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This model represents the `activity_log` table.
 * It is used to store user activities like actions on teams and boards.
 */
class ActivityLog extends ActiveRecord
{
    /**
     * Returns the database table name.
     */
    public static function tableName()
    {
        return 'activity_log';
    }

    /**
     * Validation rules for ActivityLog model.
     */
    public function rules()
    {
        return [
            // Integer fields
            [['user_id', 'team_id', 'board_id', 'created_at'], 'integer'],

            // Action is mandatory
            [['action'], 'required'],

            // Details can be long text
            [['details'], 'string'],

            // Action length limitation
            [['action'], 'string', 'max' => 255],
        ];
    }

    /**
     * Relation with User model.
     * One activity log belongs to one user.
     */
    public function getUser()
    {
        return $this->hasOne(\common\models\User::class, ['id' => 'user_id']);
    }

    /**
     * Relation with Team model.
     * One activity log can be linked to one team.
     */
    public function getTeam()
    {
        return $this->hasOne(\common\models\Team::class, ['id' => 'team_id']);
    }

    /**
     * Relation with Board model.
     * One activity log can be linked to one board.
     */
    public function getBoard()
    {
        return $this->hasOne(\common\models\Board::class, ['id' => 'board_id']);
    }
}
