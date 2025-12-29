<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%board_members}}`.
 */
class m251208_042803_create_board_members_table extends Migration
{
    /**
     * {@inheritdoc}
     */
     public function safeUp()
    {
        $this->createTable('{{%board_members}}', [
            'id' => $this->primaryKey(),
            'board_id' => $this->integer()->notNull(),
            'user_id' => $this->integer()->notNull(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);

        // Foreign Keys
        $this->addForeignKey(
            'fk_board_members_board',
            '{{%board_members}}',
            'board_id',
            '{{%board}}',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_board_members_user',
            '{{%board_members}}',
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
        $this->dropForeignKey('fk_board_members_board', '{{%board_members}}');
        $this->dropForeignKey('fk_board_members_user', '{{%board_members}}');

        $this->dropTable('{{%board_members}}');
    }
}
