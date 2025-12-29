<?php

namespace common\models;

use yii\db\ActiveRecord;

class TaskImage extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%task_images}}';
    }

    public function rules()
    {
        return [
            [['task_id', 'image'], 'required'],
            [['task_id'], 'integer'],
            [['image'], 'string', 'max' => 255],
        ];
    }

    public function getTask()
    {
        return $this->hasOne(Task::class, ['id' => 'task_id']);
    }
}
