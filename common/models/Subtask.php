<?php
namespace common\models;

use yii\db\ActiveRecord;

class Subtask extends ActiveRecord
{
    public static function tableName(){
        return 'subtask';
    }

    public function rules(){
        return [
            [['task_id','title'],'required'],
            ['is_done','boolean']
        ];
    }

    public function getTask(){
        return $this->hasOne(Task::class,['id'=>'task_id']);
    }
}
