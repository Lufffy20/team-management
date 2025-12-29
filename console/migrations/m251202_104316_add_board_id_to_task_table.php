<?php

use yii\db\Migration;

class m251202_104316_add_board_id_to_task_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
{
    $this->addColumn('{{%task}}', 'board_id', $this->integer()->defaultValue(1));
}


    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m251202_104316_add_board_id_to_task_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m251202_104316_add_board_id_to_task_table cannot be reverted.\n";

        return false;
    }
    */
}
