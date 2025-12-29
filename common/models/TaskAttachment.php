<?php

namespace common\models;

use yii\db\ActiveRecord;

/**
 * TaskAttachment model
 *
 * This model represents the `task_attachments` table.
 * It is used to store files attached to tasks.
 */
class TaskAttachment extends ActiveRecord
{
    /**
     * Returns the database table name.
     */
    public static function tableName()
    {
        return '{{%task_attachments}}';
    }

    /**
     * Validation rules for TaskAttachment model.
     */
    public function rules()
    {
        return [
            // Task ID and file name are required
            [['task_id', 'file'], 'required'],

            // Integer fields
            [['task_id', 'created_at'], 'integer'],

            // File name length limit
            [['file'], 'string', 'max' => 255],
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
     * Relation with Task model.
     * One attachment belongs to one task.
     */
    public function getTask()
    {
        return $this->hasOne(Task::class, ['id' => 'task_id']);
    }
}
