<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%board}}`.
 */
class m251202_104245_create_board_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%board}}', [
        'id' => $this->primaryKey(),
        'title' => $this->string()->notNull(),
        'description' => $this->text(),
        'created_by' => $this->integer(),
        'created_at' => $this->integer(),
    ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
{
    $this->dropTable('{{%board}}');
}
}
