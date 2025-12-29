<?php
namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
use yii\db\ActiveRecord;

class Team extends ActiveRecord
{
    public static function tableName()
    {
        return 'team';
    }

public function behaviors()
{
    $behaviors = [
        'timestamp' => [
            'class' => TimestampBehavior::class,
            'createdAtAttribute' => 'created_at',
            'updatedAtAttribute' => false,   // no updated_at
            'value' => time(),
        ],
    ];

    // 🔥 IMPORTANT: console me blameable disable
    if (!(\Yii::$app instanceof \yii\console\Application)) {
        $behaviors['blameable'] = [
            'class' => BlameableBehavior::class,
            'createdByAttribute' => 'created_by',
            'updatedByAttribute' => false,
        ];
    }

    return $behaviors;
}


    public function rules()
    {
        return [
            [['name'], 'required'],                // ONLY name is required
            [['description'], 'string'],
            [['created_by', 'created_at'], 'integer'],
            [['name'], 'string', 'max' => 255],
        ];
    }

    public function getMembers()
{
    return $this->hasMany(TeamMembers::class, ['team_id' => 'id'])
        ->with(['user'])
        ->orderBy(['team_members.id' => SORT_ASC]);
}

public function getUsers()
{
    return $this->hasMany(User::class, ['id' => 'user_id'])
        ->via('members');
}


    public function getCreator()
{
    return $this->hasOne(User::class, ['id' => 'created_by']);
}

public function getTeamMembersList()
{
    $list = [];

    foreach ($this->members as $m) {
        // Show username — OR you can use $m->user->email
        $label = $m->user->username;

        // Add manager label
        if ($m->role === 'manager') {
            $label .= " (Manager)";
        }

        $list[$m->user_id] = $label;
    }

    return $list;
}


}
