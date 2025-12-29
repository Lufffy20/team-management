<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

class BoardMembers extends ActiveRecord
{
    public static function tableName()
    {
        return 'board_members';
    }

    public function rules()
    {
        return [
            [['board_id','user_id'], 'required'],
            [['board_id','user_id'], 'integer'],
        ];
    }

    // Relations
    public function getBoard()
    {
        return $this->hasOne(Board::class, ['id' => 'board_id']);
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }
}
