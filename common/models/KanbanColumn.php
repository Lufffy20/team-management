<?php

namespace common\models;

use yii\db\ActiveRecord;

class KanbanColumn extends ActiveRecord
{
    public static function tableName()
    {
        return 'kanban_columns';
    }
}
