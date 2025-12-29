<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%notifications}}`.
 */
class m251223_055949_create_notifications_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%notification}}', [
            'id'         => $this->primaryKey(),
            'user_id'    => $this->integer()->notNull(),
            'title'      => $this->string(255)->notNull(),
            'message'    => $this->text()->notNull(),
            'is_read'    => $this->boolean()->defaultValue(0),
            'created_at' => $this->integer()->notNull(),
        ]);

        $this->createIndex(
            'idx-notification-user_id',
            '{{%notification}}',
            'user_id'
        );

        $this->addForeignKey(
            'fk-notification-user_id',
            '{{%notification}}',
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
        $this->dropForeignKey('fk-notification-user_id', '{{%notification}}');
        $this->dropIndex('idx-notification-user_id', '{{%notification}}');
        $this->dropTable('{{%notification}}');
    }
}
