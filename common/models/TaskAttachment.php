<?php
namespace common\models;

use yii\db\ActiveRecord;

class TaskAttachment extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%task_attachments}}';
    }

    public function rules()
    {
        return [
            [['task_id', 'file'], 'required'],
            [['task_id', 'created_at'], 'integer'],
            [['file'], 'string', 'max' => 255],
        ];
    }

    public function beforeSave($insert)
    {
        if ($insert && !$this->created_at) {
            $this->created_at = time();
        }
        return parent::beforeSave($insert);
    }

    public function getTask()
    {
        return $this->hasOne(Task::class, ['id' => 'task_id']);
    }
}
