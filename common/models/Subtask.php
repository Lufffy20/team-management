<?php

namespace common\models;

use yii\db\ActiveRecord;

/**
 * Subtask model
 *
 * This model represents the `subtask` table.
 * It is used to store subtasks related to a task.
 */
class Subtask extends ActiveRecord
{
    /**
     * Returns the database table name.
     */
    public static function tableName()
    {
        return 'subtask';
    }

    /**
     * Validation rules for Subtask model.
     */
    public function rules()
    {
        return [
            // Task ID and title are required
            [['task_id', 'title'], 'required'],

            // is_done must be boolean
            ['is_done', 'boolean'],
        ];
    }

    /**
     * Relation with Task model.
     * One subtask belongs to one task.
     */
    public function getTask()
    {
        return $this->hasOne(Task::class, ['id' => 'task_id']);
    }
}
