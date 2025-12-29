<?php

namespace common\models;

use yii\db\ActiveRecord;

/**
 * KanbanColumn model
 *
 * This model represents the `kanban_columns` table.
 * It is used to store columns for kanban boards.
 */
class KanbanColumn extends ActiveRecord
{
    /**
     * Returns the database table name.
     */
    public static function tableName()
    {
        return 'kanban_columns';
    }
}
