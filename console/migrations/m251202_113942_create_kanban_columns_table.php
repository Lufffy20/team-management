<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%kanban_columns}}`.
 */
class m251202_113942_create_kanban_columns_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
{
    $this->createTable('kanban_columns', [
        'id'       => $this->primaryKey(),
        'board_id' => $this->integer()->notNull(),
        'status'   => $this->string()->notNull(),
        'label'    => $this->string()->notNull(),
        'position' => $this->integer()->notNull()->defaultValue(0),
    ]);

    // Default columns for board 1
    $this->batchInsert('kanban_columns', ['board_id', 'status', 'label', 'position'], [
        [1, 'todo', 'To-Do', 0],
        [1, 'in_progress', 'In Progress', 1],
        [1, 'done', 'Done', 2],
        [1, 'archived', 'Archived', 3],
    ]);
}

    /**
     * {@inheritdoc}
     */
    public function safeDown()
{
    $this->dropTable('kanban_columns');
}
}
