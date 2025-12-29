<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * TeamMembers model
 *
 * This model represents the `team_members` table.
 * It is used to manage users assigned to teams with roles.
 */
class TeamMembers extends ActiveRecord
{
    /**
     * Virtual attribute for email input.
     */
    public $email;

    /**
     * Returns the database table name.
     */
    public static function tableName()
    {
        return 'team_members';
    }

    /**
     * Validation rules for TeamMembers model.
     */
    public function rules()
    {
        return [
            // Email is required only on create scenario
            [['email'], 'required', 'on' => 'create'],

            // Email is optional on update
            [['email'], 'safe'],

            // Custom email existence validation on create
            ['email', 'validateEmailExists', 'on' => 'create'],

            // Required fields
            [['team_id', 'user_id', 'role'], 'required'],

            // Integer fields
            [['team_id', 'user_id'], 'integer'],

            // String fields
            [['email', 'role'], 'string', 'max' => 255],
        ];
    }

    /**
     * Validates whether email exists in users table.
     * Automatically binds user_id if found.
     */
    public function validateEmailExists($attribute, $params)
    {
        $user = User::find()->where(['email' => $this->email])->one();

        if (!$user) {
            $this->addError(
                $attribute,
                'This email is not registered in the system.'
            );
        } else {
            // Auto bind user_id
            $this->user_id = $user->id;
        }
    }

    /**
     * Relation with User model.
     * One team member belongs to one user.
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * Relation with Team model.
     * One team member belongs to one team.
     */
    public function getTeam()
    {
        return $this->hasOne(Team::class, ['id' => 'team_id']);
    }
}
