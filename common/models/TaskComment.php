<?php

namespace common\models;

use yii\db\ActiveRecord;

/**
 * TaskComment model
 *
 * This model represents the `task_comments` table.
 * It is used to store comments added to tasks.
 */
class TaskComment extends ActiveRecord
{
    /**
     * Returns the database table name.
     */
    public static function tableName()
    {
        return '{{%task_comments}}';
    }

    /**
     * Validation rules for TaskComment model.
     */
    public function rules()
    {
        return [
            // Required fields
            [['task_id', 'user_id', 'comment'], 'required'],

            // Integer fields
            [['task_id', 'user_id', 'created_at'], 'integer'],

            // Comment text
            ['comment', 'string'],
        ];
    }

    /**
     * Sets created_at timestamp before inserting record.
     */
    public function beforeSave($insert)
    {
        if ($insert && !$this->created_at) {
            $this->created_at = time();
        }

        return parent::beforeSave($insert);
    }

    /**
     * Relation with User model.
     * One comment belongs to one user.
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }
}
