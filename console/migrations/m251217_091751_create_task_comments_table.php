<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%task_comments}}`.
 */
class m251217_091751_create_task_comments_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%task_comments}}', [
            'id' => $this->primaryKey(),
            'task_id' => $this->integer()->notNull(),
            'user_id' => $this->integer()->notNull(),
            'comment' => $this->text()->notNull(),
            'created_at' => $this->integer()->notNull(),
        ]);

        $this->addForeignKey(
            'fk_comment_task',
            '{{%task_comments}}',
            'task_id',
            '{{%task}}',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_comment_user',
            '{{%task_comments}}',
            'user_id',
            '{{%user}}',
            'id',
            'CASCADE'
        );
    }
    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%task_comments}}');
    }
}
