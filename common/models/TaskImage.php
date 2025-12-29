<?php

namespace common\models;

use yii\db\ActiveRecord;

/**
 * TaskImage model
 *
 * This model represents the `task_images` table.
 * It is used to store images related to tasks.
 */
class TaskImage extends ActiveRecord
{
    /**
     * Returns the database table name.
     */
    public static function tableName()
    {
        return '{{%task_images}}';
    }

    /**
     * Validation rules for TaskImage model.
     */
    public function rules()
    {
        return [
            // Task ID and image name are required
            [['task_id', 'image'], 'required'],

            // Task ID must be integer
            [['task_id'], 'integer'],

            // Image name length limit
            [['image'], 'string', 'max' => 255],
        ];
    }

    /**
     * Relation with Task model.
     * One task image belongs to one task.
     */
    public function getTask()
    {
        return $this->hasOne(Task::class, ['id' => 'task_id']);
    }
}
