<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

class Board extends ActiveRecord
{
    public static function tableName()
    {
        return 'board';
    }

    public function rules()
    {
        return [
            [['title'], 'required'],
            [['description'], 'string'],
            [['created_by','created_at','team_id'], 'integer'],
            [['title'], 'string', 'max' => 255],
        ];
    }

    public function getTasks()
    {
        return $this->hasMany(Task::class, ['board_id' => 'id']);
    }

public function getTeam()
{
    return $this->hasOne(Team::class, ['id' => 'team_id']);
}

public function getTeamMembersList()
{
    $users = User::find()
        ->joinWith('teamMembers tm')
        ->where(['tm.team_id' => $this->team_id])
        ->select(['user.id', 'user.email'])
        ->asArray()
        ->all();

    return array_column($users, 'email', 'id'); // id => email mapping dropdown ke liye
}

public function getCreatedBy()
{
    return $this->hasOne(User::class, ['id' => 'created_by']);
}

public function getUserRole($userId)
{
    $tm = \common\models\TeamMembers::findOne([
        'team_id' => $this->team_id,
        'user_id' => $userId
    ]);

    return $tm ? $tm->role : 'guest';
}

}

?>