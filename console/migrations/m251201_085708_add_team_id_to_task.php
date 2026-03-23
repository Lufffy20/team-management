<?php

use yii\db\Migration;

class m251201_085708_add_team_id_to_task extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
{
    $this->addColumn('task', 'team_id', $this->integer()->notNull());

    $this->addForeignKey('fk_task_team', 'task', 'team_id', 'team', 'id', 'CASCADE');
}


    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m251201_085708_add_team_id_to_task cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m251201_085708_add_team_id_to_task cannot be reverted.\n";

        return false;
    }
    */
}
