<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * Board model
 * 
 * This model represents the `board` table.
 * A board belongs to a team and contains multiple tasks.
 */
class Board extends ActiveRecord
{
    /**
     * Returns the database table name.
     */
    public static function tableName()
    {
        return 'board';
    }

    /**
     * Validation rules for Board model.
     */
    public function rules()
    {
        return [
            /* ===============================
         * TITLE (Only letters & spaces)
         * =============================== */
            [['title'], 'trim'],
            [['title'], 'required'],
            [['title'], 'string', 'min' => 3, 'max' => 100],
            [
                ['title'],
                'match',
                'pattern' => '/^[A-Za-z ]+$/',
                'message' => 'Title can contain only letters and spaces.'
            ],

        /* ===============================
         * DESCRIPTION (Limited length)
         * =============================== */
            [['description'], 'trim'],
            [['description'], 'string', 'max' => 50],


            // Integer fields
            [['created_by', 'created_at', 'team_id'], 'integer'],

            // Title length limit
            [['title'], 'string', 'max' => 255],
        ];
    }

    /**
     * Relation with Task model.
     * One board can have multiple tasks.
     */
    public function getTasks()
    {
        return $this->hasMany(Task::class, ['board_id' => 'id']);
    }

    /**
     * Relation with Team model.
     * One board belongs to one team.
     */
    public function getTeam()
    {
        return $this->hasOne(Team::class, ['id' => 'team_id']);
    }

    /**
     * Returns team members list for dropdown.
     * Format: id => email
     */
    public function getTeamMembersList()
    {
        $users = User::find()
            ->joinWith('teamMembers tm')
            ->where(['tm.team_id' => $this->team_id])
            ->select(['user.id', 'user.email'])
            ->asArray()
            ->all();

        // id => email mapping for dropdown
        return array_column($users, 'email', 'id');
    }

    /**
     * Relation with User model.
     * Represents the creator of the board.
     */
    public function getCreatedBy()
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }

    /**
     * Returns role of a user in the board's team.
     */
    public function getUserRole($userId)
    {
        $tm = \common\models\TeamMembers::findOne([
            'team_id' => $this->team_id,
            'user_id' => $userId
        ]);

        return $tm ? $tm->role : 'guest';
    }
}
