<?php
namespace common\models;

use yii\db\ActiveRecord;

class TaskComment extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%task_comments}}';
    }

    public function rules()
    {
        return [
            [['task_id','user_id','comment'], 'required'],
            [['task_id','user_id','created_at'], 'integer'],
            ['comment', 'string'],
        ];
    }

    public function beforeSave($insert)
    {
        if ($insert && !$this->created_at) {
            $this->created_at = time();
        }
        return parent::beforeSave($insert);
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }
}
