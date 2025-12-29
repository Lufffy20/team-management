<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%activity_log}}`.
 */
class m251210_090918_create_activity_log_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%activity_log}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer(),
            'action' => $this->string(255)->notNull(),
            'details' => $this->text(),
            'created_at' => $this->integer()->notNull(),
        ]);

        // Foreign key to user table
        $this->addForeignKey(
            'fk-activity_log-user_id',
            '{{%activity_log}}',
            'user_id',
            '{{%user}}',
            'id',
            'SET NULL'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-activity_log-user_id', '{{%activity_log}}');
        $this->dropTable('{{%activity_log}}');
    }
}
