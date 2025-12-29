<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%team}}`.
 */
class m251205_093744_create_team_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
{
    $this->createTable('team', [
        'id' => $this->primaryKey(),
        'name' => $this->string()->notNull(),
        'description' => $this->text(),
        'created_by' => $this->integer()->notNull(),
        'created_at' => $this->integer(),
    ]);
}

    /**
     * {@inheritdoc}
     */
    public function safeDown()
{
    $this->dropTable('team');
}
}
