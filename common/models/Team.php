<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;

/**
 * Team model
 *
 * This model represents the `team` table.
 * It is used to manage teams and their members.
 */
class Team extends ActiveRecord
{
    /**
     * Returns the database table name.
     */
    public static function tableName()
    {
        return 'team';
    }

    /**
     * Attaches behaviors to the model.
     * - TimestampBehavior for created_at
     * - BlameableBehavior for created_by (disabled in console)
     */
    public function behaviors()
    {
        $behaviors = [
            'timestamp' => [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => false, // no updated_at
                'value' => time(),
            ],
        ];

        // Disable blameable behavior in console application
        if (!(Yii::$app instanceof \yii\console\Application)) {
            $behaviors['blameable'] = [
                'class' => BlameableBehavior::class,
                'createdByAttribute' => 'created_by',
                'updatedByAttribute' => false,
            ];
        }

        return $behaviors;
    }

    /**
     * Validation rules for Team model.
     */
    public function rules()
    {
        return [
            // Team name is required
            [['name'], 'required'],

            // Description can be long text
            [['description'], 'string'],

            // Integer fields
            [['created_by', 'created_at'], 'integer'],

            // Name length limit
            [['name'], 'string', 'max' => 255],
        ];
    }

    /**
     * Relation with TeamMembers model.
     * Returns all members of the team with user data.
     */
    public function getMembers()
    {
        return $this->hasMany(TeamMembers::class, ['team_id' => 'id'])
            ->with(['user'])
            ->orderBy(['team_members.id' => SORT_ASC]);
    }

    /**
     * Relation with User model via team members.
     */
    public function getUsers()
    {
        return $this->hasMany(User::class, ['id' => 'user_id'])
            ->via('members');
    }

    /**
     * Relation with User model.
     * Represents the creator of the team.
     */
    public function getCreator()
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }

    /**
     * Returns team members list for dropdown.
     * Format: user_id => username (with role label if manager)
     */
    public function getTeamMembersList()
    {
        $list = [];

        foreach ($this->members as $m) {
            // Use username as label
            $label = $m->user->username;

            // Append role label for manager
            if ($m->role === 'manager') {
                $label .= ' (Manager)';
            }

            $list[$m->user_id] = $label;
        }

        return $list;
    }
}
