<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * BoardMembers model
 *
 * This model represents the `board_members` table.
 * It is used to link users with boards.
 */
class BoardMembers extends ActiveRecord
{
    /**
     * Returns the database table name.
     */
    public static function tableName()
    {
        return 'board_members';
    }

    /**
     * Validation rules for BoardMembers model.
     */
    public function rules()
    {
        return [
            // Board ID and User ID are required
            [['board_id', 'user_id'], 'required'],

            // Board ID and User ID must be integers
            [['board_id', 'user_id'], 'integer'],
        ];
    }

    /**
     * Relation with Board model.
     * One board member entry belongs to one board.
     */
    public function getBoard()
    {
        return $this->hasOne(Board::class, ['id' => 'board_id']);
    }

    /**
     * Relation with User model.
     * One board member entry belongs to one user.
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }
}
