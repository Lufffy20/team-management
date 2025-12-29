<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%subtask}}`.
 */
class m251204_072221_create_subtask_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%subtask}}', [
            'id' => $this->primaryKey(),
            'task_id' => $this->integer()->notNull(),
            'title' => $this->string()->notNull(),
            'is_done' => $this->boolean()->defaultValue(false),
            'created_at' => $this->dateTime()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->dateTime()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        $this->addForeignKey(
            'fk_subtask_task',
            '{{%subtask}}',
            'task_id',
            '{{%task}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk_subtask_task','{{%subtask}}');
        $this->dropTable('{{%subtask}}');
    }
}
