<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%team_members}}`.
 */
class m251205_093902_create_team_members_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
{
    $this->createTable('team_members', [
        'id' => $this->primaryKey(),
        'team_id' => $this->integer()->notNull(),
        'user_id' => $this->integer()->notNull(),
        'role' => $this->string()->defaultValue('member'),
    ]);
}

    /**
     * {@inheritdoc}
     */
    public function safeDown()
{
    $this->dropTable('team_members');
}
}
