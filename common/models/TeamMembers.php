<?php
namespace common\models;

use Yii;

class TeamMembers extends \yii\db\ActiveRecord
{
    public $email; // virtual attribute for email input

    public static function tableName()
    {
        return 'team_members';
    }

    public function rules()
{
    return [
        // Email required ONLY when creating
        [['email'], 'required', 'on' => 'create'],

        // Update me email optional
        [['email'], 'safe'],

        ['email', 'validateEmailExists', 'on' => 'create'],

        [['team_id', 'user_id', 'role'], 'required'],
        [['team_id', 'user_id'], 'integer'],
        [['email', 'role'], 'string', 'max' => 255],
    ];
}


    // Custom validator to check if email exists in users table
    public function validateEmailExists($attribute, $params)
    {
        $user = User::find()->where(['email' => $this->email])->one();

        if (!$user) {
            $this->addError($attribute, 'This email is not registered in the system.');
        } else {
            $this->user_id = $user->id; // Auto bind user_id
        }
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function getTeam()
    {
        return $this->hasOne(Team::class, ['id' => 'team_id']);
    }
}
