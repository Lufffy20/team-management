<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%task_attachments}}`.
 */
class m251217_085501_create_task_attachments_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%task_attachments}}', [
            'id' => $this->primaryKey(),
            'task_id' => $this->integer()->notNull(),
            'file' => $this->string()->notNull(),
            'created_at' => $this->integer()->notNull(),
        ]);

        // 🔑 Foreign key
        $this->addForeignKey(
            'fk_task_attachments_task_id',
            '{{%task_attachments}}',
            'task_id',
            '{{%task}}',
            'id',
            'CASCADE'
        );

        // 🔥 Index for performance
        $this->createIndex(
            'idx_task_attachments_task_id',
            '{{%task_attachments}}',
            'task_id'
        );
    }

    /**
     * {@inheritdoc}
     */
     public function safeDown()
    {
        $this->dropForeignKey(
            'fk_task_attachments_task_id',
            '{{%task_attachments}}'
        );

        $this->dropIndex(
            'idx_task_attachments_task_id',
            '{{%task_attachments}}'
        );

        $this->dropTable('{{%task_attachments}}');
    }
}
